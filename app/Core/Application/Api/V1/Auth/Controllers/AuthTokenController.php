<?php

namespace App\Core\Application\Api\V1\Auth\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Auth\Actions\CompleteTwoFactorLoginAction;
use App\Core\Application\Api\V1\Auth\Actions\HandleGoogleCallbackAction;
use App\Core\Application\Api\V1\Auth\Actions\PrepareTwoFactorLoginChallengeAction;
use App\Core\Application\Api\V1\Auth\Requests\CompleteTwoFactorLoginRequest;
use App\Core\Application\Api\V1\Auth\Requests\ConfirmTwoFactorRequest;
use App\Core\Application\Api\V1\Auth\Requests\ForgotPasswordRequest;
use App\Core\Application\Api\V1\Auth\Requests\LoginRequest;
use App\Core\Application\Api\V1\Auth\Requests\RegisterRequest;
use App\Core\Application\Api\V1\Auth\Requests\ResetPasswordRequest;
use App\Core\Application\Api\V1\Identity\Support\AuthUserPayloadMapper;
use App\Core\Application\Api\V1\Identity\Support\ProfileResponseMapper;
use App\Core\Domain\Identity\Actions\ForgotPasswordAction;
use App\Core\Domain\Identity\Actions\LoginUserAction;
use App\Core\Domain\Identity\Actions\RegisterUserAction;
use App\Core\Domain\Identity\Actions\ResendEmailVerificationAction;
use App\Core\Domain\Identity\Actions\ResetPasswordAction;
use App\Core\Domain\Identity\Actions\VerifyEmailAction;
use App\Core\Domain\Identity\DTOs\DeviceContextDto;
use App\Core\Domain\Identity\DTOs\LoginCredentialsDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class AuthTokenController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUserAction,
        private readonly LoginUserAction $loginUserAction,
        private readonly ForgotPasswordAction $forgotPasswordAction,
        private readonly ResetPasswordAction $resetPasswordAction,
        private readonly ResendEmailVerificationAction $resendEmailVerificationAction,
        private readonly VerifyEmailAction $verifyEmailAction,
        private readonly PrepareTwoFactorLoginChallengeAction $prepareTwoFactorLoginChallengeAction,
        private readonly CompleteTwoFactorLoginAction $completeTwoFactorLoginAction,
        private readonly HandleGoogleCallbackAction $handleGoogleCallbackAction,
        private readonly ProfileResponseMapper $profileResponseMapper,
        private readonly AuthUserPayloadMapper $authUserPayloadMapper,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->registerUserAction->execute(
            $request->validated(),
            $this->deviceContextFromRequest($request),
        );

        return BaseResponse::created($payload)->toJsonResponse();
    }

    /**
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = LoginCredentialsDto::fromArray($request->validated());
        $payload = $this->loginUserAction->execute(
            $credentials,
            $this->deviceContextFromRequest($request),
            true,
        );

        if ($payload->user->hasEnabledTwoFactorAuthentication()) {
            return BaseResponse::ok(
                $this->prepareTwoFactorLoginChallengeAction->execute($payload, $credentials)
            )->toJsonResponse();
        }

        return BaseResponse::ok($payload->toArray())->toJsonResponse();
    }

    public function completeTwoFactorLogin(
        CompleteTwoFactorLoginRequest $request,
        TwoFactorAuthenticationProvider $provider
    ): JsonResponse {
        $result = $this->completeTwoFactorLoginAction->execute($request->validated(), $provider);

        if ($result['status'] === 'error') {
            return BaseResponse::unprocessableEntity($result['data'])->toJsonResponse();
        }

        return BaseResponse::ok($result['data'])->toJsonResponse();
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return BaseResponse::ok([
            'user' => $user ? $this->authUserPayloadMapper->toArray($user) : null,
        ])->toJsonResponse();
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return BaseResponse::ok([
            'message' => __('Deconnexion effectuee avec succes.'),
        ])->toJsonResponse();
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return BaseResponse::ok([
            'message' => __('Toutes les sessions ont ete revoquees.'),
        ])->toJsonResponse();
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return BaseResponse::ok([
            'message' => $this->forgotPasswordAction->execute((string) $request->validated('email')),
        ])->toJsonResponse();
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return BaseResponse::ok([
            'message' => $this->resetPasswordAction->execute($request->validated()),
        ])->toJsonResponse();
    }

    public function resendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => $this->resendEmailVerificationAction->execute($user),
        ])->toJsonResponse();
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse|JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return BaseResponse::forbidden([
                'message' => __('Lien de verification invalide ou expire.'),
            ])->toJsonResponse();
        }

        if (! $this->verifyEmailAction->execute($id, $hash)) {
            return BaseResponse::forbidden([
                'message' => __('Utilisateur introuvable ou hash invalide.'),
            ])->toJsonResponse();
        }

        $frontUrl = (string) config('app.frontend_url', 'http://localhost:3000');

        return redirect()->away($frontUrl.'/verify-email?verified=1');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        /** @var GoogleProvider $googleProvider */
        $googleProvider = Socialite::driver('google');

        return $googleProvider
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $frontUrl = (string) config('app.frontend_url', 'http://localhost:3000');
        $result = $this->handleGoogleCallbackAction->execute($frontUrl);

        if (! $result['success']) {
            return redirect()->away($frontUrl.'/login?google_error='.$result['value']);
        }

        return redirect()->away($result['value']);
    }

    public function enableTwoFactor(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        $enable($user);

        return BaseResponse::ok([
            'message' => __('Double authentification initialisee.'),
        ])->toJsonResponse();
    }

    public function confirmTwoFactor(ConfirmTwoFactorRequest $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        $confirm($user, (string) $request->validated('code'));

        return BaseResponse::ok([
            'message' => __('Double authentification confirmee.'),
        ])->toJsonResponse();
    }

    public function disableTwoFactor(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        $disable($user);

        return BaseResponse::ok([
            'message' => __('Double authentification desactivee.'),
        ])->toJsonResponse();
    }

    public function twoFactorQrCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'svg' => $user->twoFactorQrCodeSvg(),
        ])->toJsonResponse();
    }

    public function twoFactorRecoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'recovery_codes' => $user->recoveryCodes(),
        ])->toJsonResponse();
    }

    public function regenerateTwoFactorRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return BaseResponse::unauthorized([
                'message' => __('Utilisateur non authentifie.'),
            ])->toJsonResponse();
        }

        $generate($user);

        return BaseResponse::ok([
            'recovery_codes' => $user->fresh()->recoveryCodes(),
        ])->toJsonResponse();
    }

    private function deviceContextFromRequest(Request $request): DeviceContextDto
    {
        return new DeviceContextDto(
            ip: (string) $request->ip(),
            userAgent: (string) $request->userAgent(),
            deviceName: (string) ($request->input('device_name') ?? 'api'),
            secChUa: (string) $request->header('sec-ch-ua'),
            secChUaPlatform: (string) $request->header('sec-ch-ua-platform'),
            acceptLanguage: (string) $request->header('accept-language'),
        );
    }
}
