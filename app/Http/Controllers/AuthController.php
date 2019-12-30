<?php

namespace App\Http\Controllers;


use App\Constants\TranslationCode;
use App\Models\User;
use App\Models\UserToken;
use App\Services\LogService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
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
                return $this->userErrorResponse($validator->messages());
            }

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
            Log::error(LogService::getExceptionTraceAsString($e));

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
                return $this->userErrorResponse($validator->messages());
            }

            $rememberToken = $request->get('rememberToken');

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
            Log::error(LogService::getExceptionTraceAsString($e));

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
                return $this->userErrorResponse($validator->messages());
            }

            $token = $request->get('accessToken');

            /** @var SocialiteUser $facebookUser */
            $facebookUser = Socialite::driver('facebook')->userFromToken($token);

            if ($facebookUser->getId() !== $request->get('facebookId')) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_TOKEN_MISMATCH]);
            }

            if (!$facebookUser->getEmail()) {
                return $this->userErrorResponse(['permission' => TranslationCode::ERROR_PERMISSION_EMAIL]);
            }

            DB::beginTransaction();

            $user = $this->userService->loginUserWithSocial($facebookUser, $this->baseService->getLanguage($request), 'facebook_id');

            $loginData = $this->userService->generateLoginData($user);

            DB::commit();

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Login with twitter
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginWithTwitter(Request $request)
    {
        try {
            $validator = $this->userService->validateTwitterLoginRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            $token = $request->get('accessToken');

            /** @var SocialiteUser $twitterUser */
            $twitterUser = Socialite::driver('twitter')->userFromToken($token);

            if ($twitterUser->getId() !== $request->get('twitter_id')) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_TOKEN_MISMATCH]);
            }

            if (!$twitterUser->getEmail()) {
                return $this->userErrorResponse(['permission' => TranslationCode::ERROR_PERMISSION_EMAIL]);
            }

            DB::beginTransaction();

            $user = $this->userService->loginUserWithSocial($twitterUser, $this->baseService->getLanguage($request), 'twitter_id');

            $loginData = $this->userService->generateLoginData($user);

            DB::commit();

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

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
                return $this->userErrorResponse($validator->messages());
            }

            $token = $request->get('accessToken');

            /** @var SocialiteUser $googleUser */
            $googleUser = Socialite::driver('twitter')->userFromToken($token);

            if ($googleUser->getId() !== $request->get('google_id')) {
                return $this->userErrorResponse(['token' => TranslationCode::ERROR_TOKEN_MISMATCH]);
            }

            if (!$googleUser->getEmail()) {
                return $this->userErrorResponse(['permission' => TranslationCode::ERROR_PERMISSION_EMAIL]);
            }

            DB::beginTransaction();

            $user = $this->userService->loginUserWithSocial($googleUser, $this->baseService->getLanguage($request), 'google_id');

            $loginData = $this->userService->generateLoginData($user);

            DB::commit();

            return $this->successResponse($loginData);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

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

            if ($request->has('rememberToken')) {
                DB::beginTransaction();

                UserToken::where('token', $request->get('rememberToken'))
                    ->where('user_id', $user->id)
                    ->where('type', UserToken::TYPE_REMEMBER_ME)
                    ->delete();

                DB::commit();
            }

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
