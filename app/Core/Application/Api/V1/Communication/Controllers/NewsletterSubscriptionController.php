<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Communication\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Communication\Requests\UpdateNewsletterSubscriptionRequest;
use App\Core\Application\Api\V1\Communication\Resources\NewsletterSubscriptionResource;
use App\Core\Domain\Communication\Actions\SubscribeNewsletterAction;
use App\Core\Domain\Communication\Actions\UnsubscribeNewsletterAction;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NewsletterSubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscribeNewsletterAction $subscribe,
        private readonly UnsubscribeNewsletterAction $unsubscribe,
    ) {}

    public function me(Request $request): JsonResponse
    {
        $subscription = NewsletterSubscription::query()
            ->where('email', strtolower((string) $request->user()->email))
            ->first();

        return BaseResponse::ok([
            'subscription' => $subscription ? new NewsletterSubscriptionResource($subscription) : null,
        ])->toJsonResponse();
    }

    public function update(UpdateNewsletterSubscriptionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['email'] = $user->email;
        $data['name'] = $user->name;
        $data['source'] = 'settings';

        $subscription = $this->subscribe->execute($data, $user);

        return BaseResponse::ok([
            'subscription' => new NewsletterSubscriptionResource($subscription),
        ])->toJsonResponse();
    }

    public function destroy(Request $request): JsonResponse
    {
        $subscription = $this->unsubscribe->executeByEmail((string) $request->user()->email);

        if ($subscription === null) {
            return BaseResponse::ok(['message' => __('Aucun abonnement actif.')])->toJsonResponse();
        }

        return BaseResponse::ok([
            'subscription' => new NewsletterSubscriptionResource($subscription),
            'message' => __('Désabonnement effectué.'),
        ])->toJsonResponse();
    }
}
