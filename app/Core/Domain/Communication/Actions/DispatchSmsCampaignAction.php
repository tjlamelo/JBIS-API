<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Sms\Jobs\SendCampaignSmsJob;
use App\Core\Domain\Communication\Contracts\SmsProvider;
use App\Core\Domain\Communication\DTOs\SmsAudienceDto;
use App\Core\Domain\Communication\DTOs\SmsCampaignDto;
use App\Core\Domain\Communication\Events\SmsCampaignDispatched;
use App\Core\Domain\Communication\Exceptions\EmptySmsAudienceException;
use App\Core\Domain\Communication\Models\SmsCampaign;
use App\Core\Domain\Communication\Models\SmsDispatch;
use App\Core\Domain\Communication\States\SmsCampaignStatus;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class DispatchSmsCampaignAction
{
    public function __construct(
        private readonly ResolveSmsAudienceAction $resolveSmsAudienceAction,
        private readonly SmsProvider $smsProvider,
    ) {}

    public function execute(SmsCampaignDto $data, ?User $actor = null): SmsCampaign
    {
        $recipients = $this->resolveSmsAudienceAction->execute(
            SmsAudienceDto::fromArray($data->targeting)
        );
        if ($recipients->isEmpty()) {
            throw new EmptySmsAudienceException();
        }

        return DB::transaction(function () use ($data, $actor, $recipients): SmsCampaign {
            $campaign = SmsCampaign::query()->create([
                'created_by' => $actor?->id,
                'name' => $data->name,
                'message' => $data->message,
                'targeting' => $data->targeting,
                'send_mode' => $data->sendMode,
                'sender_id' => $data->senderId,
                'recipients_count' => $recipients->count(),
                'status' => SmsCampaignStatus::Processing->value,
            ]);

            foreach ($recipients as $recipient) {
                $dispatch = SmsDispatch::query()->create([
                    'sms_campaign_id' => $campaign->id,
                    'user_id' => $recipient['user_id'],
                    'phone_number' => $recipient['phone_number'],
                    'status' => 'pending',
                ]);

                if ($data->sendMode === 'sync') {
                    $this->sendNow($dispatch, $data);
                } else {
                    SendCampaignSmsJob::dispatch(
                        $dispatch->id,
                        $data->message,
                        $data->senderId,
                    );
                }
            }

            if ($data->sendMode === 'sync') {
                Event::dispatch(new SmsCampaignDispatched($campaign->id));
            } else {
                $campaign->transitionTo(SmsCampaignStatus::Queued);
            }

            return $campaign->fresh(['dispatches']) ?? $campaign;
        });
    }

    private function sendNow(SmsDispatch $dispatch, SmsCampaignDto $data): void
    {
        try {
            $result = $this->smsProvider->send($dispatch->phone_number, $data->message, $data->senderId);

            $dispatch->update([
                'status' => $result->status,
                'provider_message_id' => $result->providerMessageId,
                'error_message' => $result->errorMessage,
                'sent_at' => $result->status === 'sent' ? now() : null,
            ]);
        } catch (\Throwable $exception) {
            $dispatch->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
