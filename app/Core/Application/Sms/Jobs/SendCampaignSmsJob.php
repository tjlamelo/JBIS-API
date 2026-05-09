<?php

namespace App\Core\Application\Sms\Jobs;

use App\Core\Domain\Communication\Contracts\SmsProvider;
use App\Core\Domain\Communication\Events\SmsCampaignDispatched;
use App\Core\Domain\Communication\Models\SmsDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class SendCampaignSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $dispatchId,
        private readonly string $message,
        private readonly ?string $senderId,
    ) {}

    public function handle(SmsProvider $smsProvider): void
    {
        $dispatch = SmsDispatch::query()->find($this->dispatchId);
        if (! $dispatch || $dispatch->status === 'sent') {
            return;
        }

        try {
            $result = $smsProvider->send($dispatch->phone_number, $this->message, $this->senderId);

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

        $campaign = $dispatch->campaign;
        if ($campaign) {
            Event::dispatch(new SmsCampaignDispatched($campaign->id));
        }
    }
}
