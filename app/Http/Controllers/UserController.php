<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\User;
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
            Log::error(LogService::getExceptionTraceAsString($e, $request));

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

            $this->userService->sendForgotPasswordCode($user, $user->language);

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

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

            /** @var User|null $user */
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
            Log::error(LogService::getExceptionTraceAsString($e, $request));

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
            Log::error(LogService::getExceptionTraceAsString($e, $request));

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

            $error = $this->userService->resendRegisterMail($request);

            DB::commit();

            if (!$error) {
                return $this->successResponse();
            } else {
                return $this->userErrorResponse($error);
            }
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Get logged user
     *
     * @return JsonResponse
     */
    public function getUser()
    {
        try {
            return $this->successResponse(Auth::user());
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
                /** @var User|null $userExists */
                $userExists = User::whereEncrypted('email', $email)->first();

                if ($userExists) {
                    return $this->userErrorResponse(['email' => TranslationCode::ERROR_EMAIL_REGISTERED]);
                }
            }

            if ($request->has('newPassword') && !app('hash')->check($request->get('oldPassword'), $user->password)) {
                return $this->userErrorResponse(['oldPassword' => TranslationCode::ERROR_OLD_PASSWORD_WRONG]);
            }

            DB::beginTransaction();

            $this->userService->updateLoggedUser($user, $request, $this->baseService->getLanguage($request));

            DB::commit();

            return $this->successResponse($user);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

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
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }
}
