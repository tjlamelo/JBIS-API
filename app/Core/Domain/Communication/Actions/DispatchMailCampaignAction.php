<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Jobs\SendCampaignMailJob;
use App\Core\Application\Mail\Mailable\GenericCampaignMail;
use App\Core\Domain\Communication\DTOs\MailAudienceDto;
use App\Core\Domain\Communication\DTOs\MailCampaignDto;
use App\Core\Domain\Communication\DTOs\MailTemplateContentDto;
use App\Core\Domain\Communication\Events\MailCampaignDispatched;
use App\Core\Domain\Communication\Exceptions\EmptyAudienceException;
use App\Core\Domain\Communication\Models\MailCampaign;
use App\Core\Domain\Communication\Models\MailDispatch;
use App\Core\Domain\Communication\Services\MailPersonalizationRenderer;
use App\Core\Domain\Communication\States\MailCampaignStatus;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class DispatchMailCampaignAction
{
    public function __construct(
        private readonly ResolveMailAudienceAction $resolveMailAudienceAction,
        private readonly MailPersonalizationRenderer $personalizationRenderer,
    ) {}

    public function execute(MailCampaignDto $data, ?User $actor = null): MailCampaign
    {
        $recipients = $this->resolveMailAudienceAction->execute(
            MailAudienceDto::fromArray($data->targeting)
        );
        if ($recipients->isEmpty()) {
            throw new EmptyAudienceException();
        }

        return DB::transaction(function () use ($data, $actor, $recipients): MailCampaign {
            $campaign = MailCampaign::query()->create([
                'created_by' => $actor?->id,
                'name' => $data->name,
                'subject' => $data->subject,
                'body' => $data->body ?? '',
                'content' => $data->content,
                'targeting' => $data->targeting,
                'send_mode' => $data->sendMode,
                'from_name' => $data->fromName,
                'reply_to' => $data->replyTo,
                'recipients_count' => $recipients->count(),
                'status' => MailCampaignStatus::Processing->value,
            ]);

            foreach ($recipients as $user) {
                $dispatch = MailDispatch::query()->create([
                    'mail_campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'email' => (string) $user->email,
                    'status' => 'pending',
                ]);

                if ($data->sendMode === 'sync') {
                    $this->sendNow($dispatch, $data);
                } else {
                    SendCampaignMailJob::dispatch(
                        $dispatch->id,
                        $data->subject,
                        $data->body,
                        $data->content,
                        $data->fromName,
                        $data->replyTo,
                    );
                }
            }

            if ($data->sendMode === 'sync') {
                Event::dispatch(new MailCampaignDispatched($campaign->id));
            } else {
                $campaign->transitionTo(MailCampaignStatus::Queued);
            }

            return $campaign->fresh(['dispatches']) ?? $campaign;
        });
    }

    private function sendNow(MailDispatch $dispatch, MailCampaignDto $data): void
    {
        try {
            $dispatch->loadMissing(['user.profile.agency', 'user.roles']);
            $rendered = $this->personalizationRenderer->renderForUser(
                $data->subject,
                $data->body,
                MailTemplateContentDto::fromNullableArray($data->content),
                $dispatch->user,
            );

            Mail::to($dispatch->email)->send(
                new GenericCampaignMail(
                    $rendered->subject,
                    $rendered->body,
                    $rendered->contentArray(),
                    $data->fromName,
                    $data->replyTo,
                )
            );

            $dispatch->update([
                'status' => 'sent',
                'error_message' => null,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $dispatch->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
