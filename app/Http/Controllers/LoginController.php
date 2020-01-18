<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\User;
use App\Models\UserToken;
use App\Services\LogService;
use App\Services\UserService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class LoginController extends Controller
{
    /** @var UserService */
    private $userService;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userService = new UserService();
    }

    /**
     * Login user with email and password or remember token
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = $this->userService->validateLoginRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages()->all());
            }

            /** @var User|null $user */
            $user = $this->userService->loginUser($request->only('email', 'password'));

            if (!$user) {
                return $this->userErrorResponse(['credentials' => TranslationCode::ERROR_CREDENTIALS_INVALID]);
            }

            if ($user->status === User::STATUS_UNCONFIRMED) {
                return $this->userErrorResponse(['account' => TranslationCode::ERROR_ACCOUNT_UNACTIVATED]);
            }

            $loginData = $this->userService->generateLoginData($user, $request->has('remember'));

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Login with remember token
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginWithRememberToken(Request $request)
    {
        try {
            $validator = $this->userService->validateTokenLoginRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages()->all());
            }

            $rememberToken = $request->get('rememberToken');

            /** @var User|null $user */
            $user = $this->userService->loginUserWithRememberToken($rememberToken);

            if (!$user) {
                return $this->userErrorResponse(['rememberToken' => TranslationCode::ERROR_REMEMBER_TOKEN_INVALID]);
            }

            if ($user->status === User::STATUS_UNCONFIRMED) {
                return $this->userErrorResponse(['account' => TranslationCode::ERROR_ACCOUNT_UNACTIVATED]);
            }

            DB::beginTransaction();

            $this->userService->updateRememberTokenValability($rememberToken);

            $loginData = $this->userService->generateLoginData($user);

            DB::commit();

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Login with facebook
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginWithFacebook(Request $request)
    {
        try {
            $validator = $this->userService->validateFacebookLoginRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages()->all());
            }

            $token = $request->get('accessToken');

            try {
                /** @var SocialiteUser $facebookUser */
                $facebookUser = Socialite::driver('facebook')->userFromToken($token);
            } catch (Exception $e) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_FACEBOOK_ACCESS_TOKEN_INVALID]);
            }

            if ($facebookUser->getId() !== $request->get('facebookId')) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_FACEBOOK_ACCESS_TOKEN_MISMATCH]);
            }

            if (!$facebookUser->getEmail()) {
                return $this->userErrorResponse(['permission' => TranslationCode::ERROR_FACEBOOK_PERMISSION_EMAIL]);
            }

            DB::beginTransaction();

            /** @var User|null $user */
            $user = $this->userService->loginUserWithSocial($facebookUser, $this->baseService->getLanguage($request), 'facebook_id');

            $loginData = $this->userService->generateLoginData($user);

            DB::commit();

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Login with google
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginWithGoogle(Request $request)
    {
        try {
            $validator = $this->userService->validateGoogleLoginRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages()->all());
            }

            $token = $request->get('accessToken');

            try {
                /** @var SocialiteUser $googleUser */
                $googleUser = Socialite::driver('google')->userFromToken($token);
            } catch (Exception $e) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_GOOGLE_ACCESS_TOKEN_INVALID]);
            }

            if ($googleUser->getId() !== $request->get('google_id')) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_GOOGLE_ACCESS_TOKEN_MISMATCH]);
            }

            if (!$googleUser->getEmail()) {
                return $this->userErrorResponse(['permission' => TranslationCode::ERROR_GOOGLE_PERMISSION_EMAIL]);
            }

            DB::beginTransaction();

            /** @var User|null $user */
            $user = $this->userService->loginUserWithSocial($googleUser, $this->baseService->getLanguage($request), 'google_id');

            $loginData = $this->userService->generateLoginData($user);

            DB::commit();

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Logout user
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($request->has('rememberToken') || $request->has('everywhere')) {
                DB::beginTransaction();

                /** @var Builder $userTokens */
                $userTokens = UserToken::where('user_id', $user->id)
                    ->where('type', UserToken::TYPE_REMEMBER_ME);

                if ($request->has('rememberToken')) {
                    $userTokens = $userTokens->where('token', $request->get('rememberToken'));
                }

                $userTokens->delete();

                DB::commit();
            }

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }
}
