<?php

namespace App\Core\Application\Api\V1\Sms\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Sms\Requests\PreviewSmsCampaignRequest;
use App\Core\Application\Api\V1\Sms\Requests\SendSmsCampaignRequest;
use App\Core\Domain\Communication\Actions\DispatchSmsCampaignAction;
use App\Core\Domain\Communication\Actions\RefreshSmsCampaignStatsAction;
use App\Core\Domain\Communication\Actions\ResolveSmsAudienceAction;
use App\Core\Domain\Communication\DTOs\SmsAudienceDto;
use App\Core\Domain\Communication\DTOs\SmsCampaignDto;
use App\Core\Domain\Communication\Exceptions\EmptySmsAudienceException;
use App\Core\Domain\Communication\Models\SmsCampaign;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SmsCampaignController extends Controller
{
    public function __construct(
        private readonly DispatchSmsCampaignAction $dispatchSmsCampaignAction,
        private readonly RefreshSmsCampaignStatsAction $refreshSmsCampaignStatsAction,
        private readonly ResolveSmsAudienceAction $resolveSmsAudienceAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $campaigns = SmsCampaign::query()
            ->withCount('dispatches')
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok($campaigns)->toJsonResponse();
    }

    public function show(SmsCampaign $campaign): JsonResponse
    {
        $campaign->load(['dispatches' => function ($query): void {
            $query->latest()->limit(200);
        }]);

        return BaseResponse::ok([
            'campaign' => $campaign,
        ])->toJsonResponse();
    }

    public function send(SendSmsCampaignRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $campaign = $this->dispatchSmsCampaignAction->execute(
                new SmsCampaignDto(
                    message: $validated['message'],
                    targeting: $validated['targeting'],
                    sendMode: $validated['send_mode'] ?? 'queue',
                    senderId: $validated['sender_id'] ?? null,
                    name: $validated['name'] ?? null,
                ),
                $request->user(),
            );
        } catch (EmptySmsAudienceException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        } catch (RuntimeException $exception) {
            return BaseResponse::internalServerError([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        return BaseResponse::created([
            'message' => __('Campagne SMS lancee avec succes.'),
            'campaign' => $campaign,
        ])->toJsonResponse();
    }

    public function preview(PreviewSmsCampaignRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $audience = $this->resolveSmsAudienceAction->execute(
            SmsAudienceDto::fromArray($validated['targeting'])
        );

        return BaseResponse::ok([
            'preview' => [
                'message' => $validated['message'],
                'sender_id' => $validated['sender_id'] ?? config('services.queensms.sender_id'),
                'estimated_recipients' => $audience->count(),
            ],
        ])->toJsonResponse();
    }

    public function refreshStats(SmsCampaign $campaign): JsonResponse
    {
        $campaign = $this->refreshSmsCampaignStatsAction->execute($campaign);

        return BaseResponse::ok([
            'campaign' => $campaign,
        ])->toJsonResponse();
    }

    public function credit(Request $request): JsonResponse
    {
        $provider = (string) config('sms.provider', 'queen_sms');
        $providerClass = config("sms.providers.{$provider}");

        $service = app()->make($providerClass);

        if (! is_object($service) || ! method_exists($service, 'getCredit')) {
            return BaseResponse::unprocessableEntity([
                'message' => 'Provider SMS ne supporte pas la recuperation de credit.',
            ])->toJsonResponse();
        }

        try {
            $credit = (int) $service->getCredit();
        } catch (\Throwable $exception) {
            return BaseResponse::internalServerError([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'credit' => $credit,
        ])->toJsonResponse();
    }
}
