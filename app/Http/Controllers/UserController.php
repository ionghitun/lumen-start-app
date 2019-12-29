<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\Language;
use App\Models\User;
use App\Models\UserToken;
use App\Services\EmailService;
use App\Services\LogService;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Class UserController
 *
 * TODO
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
        if ($request->has('rememberToken')) {
            return $this->loginWithRememberToken($request);
        }

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
    private function loginWithRememberToken(Request $request)
    {
        try {
            $rememberToken = $request->get('rememberToken');

            $user = $this->userService->loginUserWithRememberToken($rememberToken);

            if (!$user) {
                return $this->userErrorResponse(['rememberToken' => TranslationCode::ERROR_REMEMBER_TOKEN_REQUIRED]);
            }

            if ($user->status === User::STATUS_UNCONFIRMED) {
                return $this->userErrorResponse(['account' => TranslationCode::ERROR_ACCOUNT_UNACTIVATED]);
            }

            $this->userService->updateRememberTokenValability($rememberToken);

            $loginData = $this->userService->generateLoginData($user);

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

            $user = $this->userService->loginUserWithSocial($facebookUser, $this->baseService->getLanguage($request), 'facebook_id');

            $loginData = $this->userService->generateLoginData($user);

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

            $user = $this->userService->loginUserWithSocial($twitterUser, $this->baseService->getLanguage($request), 'twitter_id');

            $loginData = $this->userService->generateLoginData($user);

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

            $user = $this->userService->loginUserWithSocial($googleUser, $this->baseService->getLanguage($request), 'google_id');

            $loginData = $this->userService->generateLoginData($user);

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

            $this->userService->registerUser($request, $this->baseService->getLanguage($request));

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

            /** @var User $user */
            $user = User::whereEncrypted('email', $request->get('email'))->first();

            if ($user->status === User::STATUS_UNCONFIRMED) {
                return $this->userErrorResponse(['account' => TranslationCode::ERROR_ACCOUNT_UNACTIVATED]);
            }

            if ($user->updatedAt->addMinute() > Carbon::now()) {
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

            if (Carbon::parse($user->forgotTime)->addHour() < Carbon::now()) {
                return $this->userErrorResponse(['forgot' => TranslationCode::ERROR_FORGOT_PASSED_1H]);
            }

            $user->forgotCode = null;
            $user->forgotTime = null;
            $user->password = Hash::make($request->get('password'));

            $user->save();

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
            $validator = $this->userService->validateActivateAccountRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            /** @var User $user */
            $user = User::whereEncrypted('email', $request->get('email'))
                ->where('activation_code', $request->get('code'))
                ->first();

            if (!$user) {
                return $this->userErrorResponse(['code' => TranslationCode::ERROR_CODE_INVALID]);
            }

            $user->status = User::STATUS_CONFIRMED;
            $user->activationCode = null;

            $user->save();

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

            $error = $this->userService->resendRegisterMail($request, $this->baseService->getLanguage($request));

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
     * Confirm email address after changing
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function confirmEmail(Request $request)
    {
        try {
            $validator = $this->userService->validateConfirmEmailRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            /** @var User $user */
            $user = User::whereEncrypted('email', $request->get('email'))
                ->where('activation_code', $request->get('code'))
                ->first();

            if (!$user) {
                return $this->userErrorResponse(['code' => TranslationCode::ERROR_CODE_INVALID]);
            }

            $user->status = User::STATUS_CONFIRMED;
            $user->activationCode = null;

            $user->save();

            return $this->successResponse();
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
                UserToken::where('token', $request->get('rememberToken'))
                    ->where('user_id', $user->id)
                    ->where('type', UserToken::TYPE_REMEMBER_ME)
                    ->delete();
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
            $confirmEmail = false;

            if ($user->email !== $email) {
                /** @var User $userExists */
                $userExists = User::whereEncrypted('email', $request->get('email'))->first();

                if ($userExists) {
                    return $this->userErrorResponse(['email' => TranslationCode::ERROR_EMAIL_REGISTERED]);
                } else {
                    $user->email = $email;
                    $user->status = User::STATUS_EMAIL_UNCONFIRMED;
                    $user->activationCode = strtoupper(Str::random(6));

                    $confirmEmail = true;
                }
            }

            if ($request->has('newPassword')) {
                if (!app('hash')->check($request->get('oldPassword'), $user->password)) {
                    return $this->userErrorResponse(['oldPassword' => TranslationCode::ERROR_OLD_PASSWORD_WRONG]);
                } else {
                    $user->password = Hash::make($request->get('newPassword'));
                }
            }

            $user->name = $request->get('name');

            /** @var Language $language */
            $language = Language::where('id', $request->get('language'))->first();
            $user->language_id = $language->id;

            if ($confirmEmail) {
                $emailService = new EmailService();

                $emailService->sendEmailConfirmationCode($user, $language->code);
            }

            $user->save();

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
            /** @var User $user */
            $user = Auth::user();

            $validator = $this->userService->validateUpdateUserPictureRequest($request);

            if (!$validator->passes()) {
                return $this->userErrorResponse($validator->messages());
            }

            $picture = $request->file('picture');

            $pictureExtension = $picture->getClientOriginalExtension();
            $generatedPictureName = str_replace(' ', '_', $user->name) . '_' . time() . '.' . $pictureExtension;

            $path = 'uploads/users/';
            File::makeDirectory($path, 0777, true, true);

            $pictureData = $this->baseService->processImage($path, $picture, $generatedPictureName, true);

            if ($pictureData) {
                if (!is_null($user->picture) && $user->picture !== '') {
                    $oldPictureData = json_decode($user->picture, true);

                    foreach ($oldPictureData as $oldPicture) {
                        if ($oldPicture && file_exists($oldPicture)) {
                            unlink($oldPicture);
                        }
                    }
                }

                $user->picture = $pictureData;
            }

            $user->save();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
