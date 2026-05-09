<?php

namespace App\Core\Application\Mail\Jobs;

use App\Core\Application\Mail\Mailable\GenericCampaignMail;
use App\Core\Domain\Communication\Events\MailCampaignDispatched;
use App\Core\Domain\Communication\DTOs\MailTemplateContentDto;
use App\Core\Domain\Communication\Services\MailPersonalizationRenderer;
use App\Core\Domain\Communication\Models\MailDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class SendCampaignMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $dispatchId,
        private readonly string $subject,
        private readonly ?string $body,
        private readonly ?array $content,
        private readonly ?string $fromName,
        private readonly ?string $replyTo,
    ) {}

    public function handle(MailPersonalizationRenderer $renderer): void
    {
        $dispatch = MailDispatch::query()
            ->with(['user.profile.agency', 'user.roles'])
            ->find($this->dispatchId);
        if (! $dispatch || $dispatch->status === 'sent') {
            return;
        }

        try {
            $rendered = $renderer->renderForUser(
                $this->subject,
                $this->body,
                MailTemplateContentDto::fromNullableArray($this->content),
                $dispatch->user,
            );

            Mail::to($dispatch->email)->send(
                new GenericCampaignMail(
                    $rendered->subject,
                    $rendered->body,
                    $rendered->contentArray(),
                    $this->fromName,
                    $this->replyTo,
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

        $campaign = $dispatch->campaign;
        if ($campaign) {
            Event::dispatch(new MailCampaignDispatched($campaign->id));
        }
    }
}
