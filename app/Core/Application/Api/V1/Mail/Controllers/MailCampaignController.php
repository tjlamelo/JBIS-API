<?php

namespace App\Core\Application\Api\V1\Mail\Controllers;

use App\Core\Application\Api\V1\Mail\Requests\PreviewMailCampaignRequest;
use App\Core\Application\Api\V1\Mail\Requests\SendMailCampaignRequest;
use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Communication\Actions\DispatchMailCampaignAction;
use App\Core\Domain\Communication\Actions\RefreshMailCampaignStatsAction;
use App\Core\Domain\Communication\DTOs\MailCampaignDto;
use App\Core\Domain\Communication\Exceptions\EmptyAudienceException;
use App\Core\Domain\Communication\Models\MailCampaign;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MailCampaignController extends Controller
{
    public function __construct(
        private readonly DispatchMailCampaignAction $dispatchMailCampaignAction,
        private readonly RefreshMailCampaignStatsAction $refreshMailCampaignStatsAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $campaigns = MailCampaign::query()
            ->withCount('dispatches')
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok($campaigns)->toJsonResponse();
    }

    public function show(MailCampaign $campaign): JsonResponse
    {
        $campaign->load(['dispatches' => function ($query): void {
            $query->latest()->limit(200);
        }]);

        return BaseResponse::ok([
            'campaign' => $campaign,
        ])->toJsonResponse();
    }

    public function send(SendMailCampaignRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['body']) && empty($validated['content'])) {
            return BaseResponse::unprocessableEntity([
                'message' => __('Le corps du mail ou un contenu template est requis.'),
            ])->toJsonResponse();
        }

        try {
            $campaign = $this->dispatchMailCampaignAction->execute(
                new MailCampaignDto(
                    subject: $validated['subject'],
                    body: $validated['body'] ?? null,
                    content: $validated['content'] ?? null,
                    targeting: $validated['targeting'],
                    sendMode: $validated['send_mode'] ?? 'queue',
                    name: $validated['name'] ?? null,
                    fromName: $validated['from_name'] ?? null,
                    replyTo: $validated['reply_to'] ?? null,
                ),
                $request->user(),
            );
        } catch (EmptyAudienceException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        } catch (RuntimeException $exception) {
            return BaseResponse::internalServerError([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        return BaseResponse::created([
            'message' => __('Campagne email lancee avec succes.'),
            'placeholders_help' => [
                'Utilise n importe quel chemin avec la syntaxe {{path.to.value}}',
                'Exemples: {{user.name}}, {{profile.matricule}}, {{agency.name}}, {{roles.0}}, {{custom.event_name}}',
                'Fallback: {{profile.first_name|Cher utilisateur}}',
            ],
            'campaign' => $campaign,
        ])->toJsonResponse();
    }

    public function preview(PreviewMailCampaignRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['body']) && empty($validated['content'])) {
            return BaseResponse::unprocessableEntity([
                'message' => __('Le corps du mail ou un contenu template est requis.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'preview' => [
                'subject' => $validated['subject'],
                'body' => $validated['body'] ?? null,
                'content' => $validated['content'] ?? null,
            ],
            'placeholders_help' => [
                'Utilise n importe quel chemin avec la syntaxe {{path.to.value}}',
                'Exemples: {{user.name}}, {{profile.matricule}}, {{agency.name}}, {{roles.0}}, {{custom.event_name}}',
                'Fallback: {{profile.first_name|Cher utilisateur}}',
            ],
        ])->toJsonResponse();
    }

    public function refreshStats(MailCampaign $campaign): JsonResponse
    {
        $campaign = $this->refreshMailCampaignStatsAction->execute($campaign);

        return BaseResponse::ok([
            'campaign' => $campaign,
        ])->toJsonResponse();
    }
}
