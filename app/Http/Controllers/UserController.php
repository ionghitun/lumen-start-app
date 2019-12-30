<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\User;
use App\Models\UserToken;
use App\Services\LogService;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
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
     * Register the user, send activation code on email
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = $this->userService->validateRegisterRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            $request->merge(['password' => Hash::make($request->get('password'))]);

            DB::beginTransaction();

            $this->userService->registerUser($request, $this->baseService->getLanguage($request));

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Generate and send a forgot code on email
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = $this->userService->validateForgotPasswordRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            $user = User::whereEncrypted('email', $request->get('email'))->first();

            if ($user->status === User::STATUS_UNCONFIRMED) {
                return $this->userErrorResponse(['account' => TranslationCode::ERROR_ACCOUNT_UNACTIVATED]);
            }

            if ($user->updated_at->addMinute() > Carbon::now()) {
                return $this->userErrorResponse(['forgot' => TranslationCode::ERROR_FORGOT_CODE_SEND_COOLDOWN]);
            }

            DB::beginTransaction();

            $this->userService->sendForgotPasswordCode($user, $this->baseService->getLanguage($request));

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Change password with generated code
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = $this->userService->validateChangePasswordRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            /** @var User $user */
            $user = User::whereEncrypted('email', $request->get('email'))
                ->where('forgot_code', $request->get('code'))
                ->first();

            if (!$user) {
                return $this->userErrorResponse(['forgot' => TranslationCode::ERROR_FORGOT_CODE_INVALID]);
            }

            if (Carbon::parse($user->forgot_time)->addHour() < Carbon::now()) {
                return $this->userErrorResponse(['forgot' => TranslationCode::ERROR_FORGOT_PASSED_1H]);
            }

            DB::beginTransaction();

            $this->userService->updatePassword($user, $request->get('password'));

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Activate account
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activateAccount(Request $request)
    {
        try {
            $validator = $this->userService->validateActivateAccountOrChangeEmailRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            DB::beginTransaction();

            $activated = $this->userService->activateUserAccount($request->get('email'), $request->get('code'));

            if (!$activated) {
                return $this->userErrorResponse(['code' => TranslationCode::ERROR_CODE_INVALID]);
            }

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Resend activation code
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resendActivationCode(Request $request)
    {
        try {
            $validator = $this->userService->validateResendActivationCodeRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            DB::beginTransaction();

            $error = $this->userService->resendRegisterMail($request, $this->baseService->getLanguage($request));

            DB::commit();

            if (!$error) {
                return $this->successResponse();
            } else {
                return $this->userErrorResponse($error);
            }
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

    /**
     * Get logged user
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUser(Request $request)
    {
        try {
            $user = Auth::user();

            return $this->successResponse($user);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }

    }

    /**
     * Update profile
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateUser(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $validator = $this->userService->validateUpdateUserRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            $email = $request->get('email');

            if ($user->email !== $email) {
                /** @var User $userExists */
                $userExists = User::whereEncrypted('email', $email)->first();

                if ($userExists) {
                    return $this->userErrorResponse(['email' => TranslationCode::ERROR_EMAIL_REGISTERED]);
                }
            }

            if ($request->has('newPassword') && !app('hash')->check($request->get('oldPassword'), $user->password)) {
                return $this->userErrorResponse(['oldPassword' => TranslationCode::ERROR_OLD_PASSWORD_WRONG]);
            }

            DB::beginTransaction();

            $this->userService->updateLoggedUser($user, $request);

            DB::commit();

            return $this->successResponse($user);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Change picture
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changeUserPicture(Request $request)
    {
        try {
            $validator = $this->userService->validateUpdateUserPictureRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            DB::beginTransaction();

            $this->userService->updateLoggedUserPicture($request->file('picture'));

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
