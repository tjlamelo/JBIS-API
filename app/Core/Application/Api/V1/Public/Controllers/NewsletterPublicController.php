<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Public\Requests\SubscribeNewsletterRequest;
use App\Core\Application\Api\V1\Public\Requests\UnsubscribeNewsletterRequest;
use App\Core\Domain\Communication\Actions\SubscribeNewsletterAction;
use App\Core\Domain\Communication\Actions\UnsubscribeNewsletterAction;
use App\Core\Application\Api\V1\Communication\Resources\NewsletterSubscriptionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NewsletterPublicController extends Controller
{
    public function __construct(
        private readonly SubscribeNewsletterAction $subscribe,
        private readonly UnsubscribeNewsletterAction $unsubscribe,
    ) {}

    public function subscribe(SubscribeNewsletterRequest $request): JsonResponse
    {
        $subscription = $this->subscribe->execute(
            $request->validated(),
            $request->user(),
        );

        return BaseResponse::created([
            'subscription' => new NewsletterSubscriptionResource($subscription),
            'message' => __('Inscription à la newsletter confirmée.'),
        ])->toJsonResponse();
    }

    public function unsubscribe(UnsubscribeNewsletterRequest $request): JsonResponse
    {
        $token = (string) $request->input('token', '');
        $subscription = $token !== ''
            ? $this->unsubscribe->executeByToken($token)
            : $this->unsubscribe->executeByEmail((string) $request->input('email', ''));

        if ($subscription === null) {
            return BaseResponse::notFound([
                'message' => __('Abonnement introuvable.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'subscription' => new NewsletterSubscriptionResource($subscription),
            'message' => __('Vous êtes désabonné de la newsletter JBIS.'),
        ])->toJsonResponse();
    }

    public function showByToken(Request $request): JsonResponse
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            return BaseResponse::unprocessableEntity(message: __('Token requis.'))->toJsonResponse();
        }

        $subscription = \App\Core\Domain\Communication\Models\NewsletterSubscription::query()
            ->where('unsubscribe_token', $token)
            ->first();

        if ($subscription === null) {
            return BaseResponse::notFound(['message' => __('Abonnement introuvable.')])->toJsonResponse();
        }

        return BaseResponse::ok([
            'subscription' => new NewsletterSubscriptionResource($subscription),
        ])->toJsonResponse();
    }
}
