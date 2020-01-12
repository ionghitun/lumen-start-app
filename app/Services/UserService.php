<?php

namespace App\Services;

use App\Constants\TranslationCode;
use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use App\Models\UserToken;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator as ReturnedValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use IonGhitun\JwtToken\Exceptions\JwtException;
use IonGhitun\JwtToken\Jwt;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Class UserService
 *
 * @package App\Services
 */
class UserService
{
    /**
     * Validate request on login
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateLoginRequest(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists_encrypted:users,email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => TranslationCode::ERROR_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_EMAIL_INVALID,
            'email.exists_encrypted' => TranslationCode::ERROR_EMAIL_NOT_REGISTERED,
            'password.required' => TranslationCode::ERROR_PASSWORD_REQUIRED
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate request on login with remember token
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateTokenLoginRequest(Request $request)
    {
        $rules = [
            'rememberToken' => 'required'
        ];

        $messages = [
            'rememberToken.required' => TranslationCode::ERROR_REMEMBER_TOKEN_REQUIRED
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Get user from email and password
     *
     * @param array $credentials
     *
     * @return User|null
     */
    public function loginUser(array $credentials)
    {
        $builder = $this->getUserBuilderForLogin();

        /** @var User|null $user */
        $user = $builder->whereEncrypted('email', $credentials['email'])
            ->first();

        if (!$user) {
            return null;
        }

        $password = $user->password;

        if (app('hash')->check($credentials['password'], $password)) {
            return $user;
        }

        return null;
    }

    /**
     * Get user builder for login
     *
     * @return mixed
     */
    private function getUserBuilderForLogin()
    {
        return User::with(['role' => function ($query) {
            $query->select(['id', 'name'])
                ->with(['rolePermissions' => function ($query) {
                    $query->select(['role_id', 'permission_id', 'read', 'create', 'update', 'delete', 'manage'])
                        ->with(['permission' => function ($query) {
                            $query->select(['id', 'name']);
                        }]);
                }]);
        }]);
    }

    /**
     * Generate returned data on login
     *
     * @param User $user
     * @param bool $remember
     *
     * @return array
     *
     * @throws JwtException
     */
    public function generateLoginData(User $user, $remember = false)
    {
        $data = [
            'user' => $user,
            'token' => Jwt::generateToken([
                'id' => $user->id
            ])
        ];

        if ($remember) {
            $data['rememberToken'] = $this->generateRememberMeToken($user->id);
        }

        return $data;
    }

    /**
     * Generate remember me token
     *
     * @param $userId
     * @param $days
     *
     * @return string
     */
    public function generateRememberMeToken($userId, $days = 14)
    {
        $userToken = new UserToken();

        $userToken->user_id = $userId;
        $userToken->token = Str::random(64);
        $userToken->type = UserToken::TYPE_REMEMBER_ME;
        $userToken->expire_on = Carbon::now()->addDays($days)->format('Y-m-d H:i:s');

        $userToken->save();

        return $userToken->token;
    }

    /**
     * Login user with remembered token
     *
     * @param $token
     *
     * @return User|null
     */
    public function loginUserWithRememberToken($token)
    {
        $builder = $this->getUserBuilderForLogin();

        return $builder->whereHas('userTokens', function ($query) use ($token) {
            $query->where('token', $token)
                ->where('expire_on', '>=', Carbon::now()->format('Y-m-d H:i:s'));
        })->first();
    }

    /**
     * Update remember token valability when used on login
     *
     * @param $token
     * @param int $days
     */
    public function updateRememberTokenValability($token, $days = 14)
    {
        $userToken = UserToken::where('token', $token)
            ->where('type', UserToken::TYPE_REMEMBER_ME)
            ->first();

        if ($userToken) {
            $userToken->expire_on = Carbon::now()->addDays($days)->format('Y-m-d H:i:s');

            $userToken->save();
        }
    }

    /**
     * Validate request on facebook login
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateFacebookLoginRequest(Request $request)
    {
        $rules = [
            'facebookId' => 'required',
            'accessToken' => 'required',
        ];

        $messages = [
            'facebookId.required' => TranslationCode::ERROR_FACEBOOK_ID_REQUIRED,
            'accessToken.required' => TranslationCode::ERROR_FACEBOOK_ACCESS_TOKEN_REQUIRED
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate request on google login
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateGoogleLoginRequest(Request $request)
    {
        $rules = [
            'googleId' => 'required',
            'accessToken' => 'required',
        ];

        $messages = [
            'googleId.required' => TranslationCode::ERROR_GOOGLE_ID_REQUIRED,
            'accessToken.required' => TranslationCode::ERROR_GOOGLE_ACCESS_TOKEN_REQUIRED
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Login user with social
     *
     * @param SocialiteUser $socialUser
     * @param Language $language
     * @param string $socialId
     *
     * @return User
     */
    public function loginUserWithSocial(SocialiteUser $socialUser, Language $language, string $socialId)
    {
        $builder = $this->getUserBuilderForLogin();

        /** @var User|null $user */
        $user = $builder->where(function ($query) use ($socialUser, $socialId) {
            $query->where($socialId, $socialUser->getId())
                ->orWhereEncrypted('email', $socialUser->getEmail());
        })->first();

        if (!$user) {
            $user = new User();

            $user->language_id = $language->id;
            $user->name = $socialUser->getName();
            $user->email = $socialUser->getEmail();

            if ($socialUser->getAvatar()) {
                $baseService = new BaseService();

                $path = 'uploads/users/';
                File::makeDirectory($path, 0777, true, true);

                $generatedPictureName = time() . '.jpg';

                $pictureData = $baseService->processImage($path, $socialUser->getAvatar(), $generatedPictureName, true, true);

                if ($pictureData) {
                    $user->picture = $pictureData;
                }
            }
        }

        $user->status = User::STATUS_CONFIRMED;
        $user->$socialId = $socialUser->getId();

        $user->save();

        return $user;
    }

    /**
     * Validate request on register
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateRegisterRequest(Request $request)
    {
        $rules = [
            'name' => 'required|alpha_spaces',
            'email' => 'required|email|unique_encrypted:users,email',
            'password' => 'required|min:6'
        ];

        $messages = [
            'name.required' => TranslationCode::ERROR_REGISTER_NAME_REQUIRED,
            'name.alpha_spaces' => TranslationCode::ERROR_REGISTER_NAME_ALPHA_SPACES,
            'email.required' => TranslationCode::ERROR_REGISTER_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_REGISTER_EMAIL_INVALID,
            'email.unique_encrypted' => TranslationCode::ERROR_REGISTER_EMAIL_REGISTERED,
            'password.required' => TranslationCode::ERROR_REGISTER_PASSWORD_REQUIRED,
            'password.min' => TranslationCode::ERROR_REGISTER_PASSWORD_MIN6
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate request on update user
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateUpdateUserRequest(Request $request)
    {
        $rules = [
            'name' => 'required|alpha_spaces',
            'email' => 'required|email',
            'newPassword' => 'required_with:oldPassword|min:6',
            'language' => 'required|exists:languages,id'
        ];

        $messages = [
            'name.required' => TranslationCode::ERROR_UPDATE_NAME_REQUIRED,
            'name.alpha_spaces' => TranslationCode::ERROR_UPDATE_NAME_ALPHA_SPACES,
            'email.required' => TranslationCode::ERROR_UPDATE_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_UPDATE_EMAIL_INVALID,
            'newPassword.required_with' => TranslationCode::ERROR_UPDATE_OLD_PASSWORD_REQUIRED,
            'newPassword.min' => TranslationCode::ERROR_UPDATE_NEW_PASSWORD_MIN6,
            'language.required' => TranslationCode::ERROR_UPDATE_LANGUAGE_REQUIRED,
            'language.exists' => TranslationCode::ERROR_UPDATE_LANGUAGE_EXISTS,
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Update logged user
     *
     * @param User $user
     * @param Request $request
     * @param Language $language
     */
    public function updateLoggedUser(User &$user, Request $request, Language $language)
    {
        $email = $request->get('email');
        $confirmEmail = false;

        if ($user->email !== $email) {
            $user->email = $email;
            $user->status = User::STATUS_EMAIL_UNCONFIRMED;
            $user->activation_code = strtoupper(Str::random(6));

            $confirmEmail = true;
        }

        if ($request->has('newPassword')) {
            $user->password = Hash::make($request->get('newPassword'));
        }

        $user->name = $request->get('name');

        $user->language_id = $language->id;

        if ($confirmEmail) {
            $emailService = new EmailService();

            $emailService->sendEmailConfirmationCode($user, $language->code);
        }

        $user->save();
    }

    /**
     * Validate request on update user picture
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateUpdateUserPictureRequest(Request $request)
    {
        $rules = [
            'picture' => 'required|image',
        ];

        $messages = [
            'picture.required' => TranslationCode::ERROR_UPDATE_PICTURE_REQUIRED,
            'picture.image' => TranslationCode::ERROR_UPDATE_PICTURE_IMAGE
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Change logged user picture
     *
     * @param $picture
     */
    public function updateLoggedUserPicture($picture)
    {
        /** @var User $user */
        $user = Auth::user();

        $pictureExtension = $picture->getClientOriginalExtension();
        $generatedPictureName = str_replace(' ', '_', $user->name) . '_' . time() . '.' . $pictureExtension;

        $path = 'uploads/users/';
        File::makeDirectory($path, 0777, true, true);

        $baseService = new BaseService();

        $pictureData = $baseService->processImage($path, $picture, $generatedPictureName, true);

        if ($pictureData) {
            if ($user->picture !== '') {
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
    }

    /**
     * Validate request on forgot password
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateForgotPasswordRequest(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists_encrypted:users,email'
        ];

        $messages = [
            'email.required' => TranslationCode::ERROR_FORGOT_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_FORGOT_EMAIL_INVALID,
            'email.exists_encrypted' => TranslationCode::ERROR_FORGOT_EMAIL_NOT_REGISTERED
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Send code on email for forgot password
     *
     * @param User $user
     * @param Language $language
     */
    public function sendForgotPasswordCode(User $user, Language $language)
    {
        $user->forgot_code = strtoupper(Str::random(6));
        $user->forgot_time = Carbon::now()->format('Y-m-d H:i:s');

        /** @var EmailService $emailService */
        $emailService = new EmailService();

        $emailService->sendForgotPasswordCode($user, $language->code);

        $user->save();
    }

    /**
     * Validate request on forgot change password
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateChangePasswordRequest(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists_encrypted:users,email',
            'code' => 'required',
            'password' => 'required|min:6'
        ];

        $messages = [
            'email.required' => TranslationCode::ERROR_FORGOT_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_FORGOT_EMAIL_INVALID,
            'email.exists_encrypted' => TranslationCode::ERROR_FORGOT_EMAIL_NOT_REGISTERED,
            'code.required' => TranslationCode::ERROR_FORGOT_CODE_REQUIRED,
            'password.required' => TranslationCode::ERROR_FORGOT_PASSWORD_REQUIRED,
            'password.min' => TranslationCode::ERROR_FORGOT_PASSWORD_MIN6
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Update user password after reset
     *
     * @param $user
     * @param $password
     */
    public function updatePassword($user, $password)
    {
        $user->forgot_code = null;
        $user->forgot_time = null;
        $user->password = Hash::make($password);

        $user->save();
    }

    /**
     * Register user
     *
     * @param Request $request
     * @param Language $language
     */
    public function registerUser(Request $request, Language $language)
    {
        $user = new User();

        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->password = $request->get('password');
        $user->status = User::STATUS_UNCONFIRMED;
        $user->language_id = $language->id;
        $user->role_id = Role::ID_USER;
        $user->activation_code = strtoupper(Str::random(6));

        /** @var EmailService $emailService */
        $emailService = new EmailService();

        $emailService->sendActivationCode($user, $language->code);

        $user->save();
    }

    /**
     * Validate activate account
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateActivateAccountOrChangeEmailRequest(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'code' => 'required'
        ];

        $messages = [
            'email.required' => TranslationCode::ERROR_ACTIVATE_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_ACTIVATE_EMAIL_INVALID,
            'code.required' => TranslationCode::ERROR_ACTIVATE_CODE_REQUIRED
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Activate user account on register or on change email
     *
     * @param $email
     * @param $code
     *
     * @return bool
     */
    public function activateUserAccount($email, $code)
    {
        /** @var User|null $user */
        $user = User::whereEncrypted('email', $email)
            ->where('activation_code', $code)
            ->first();

        if (!$user) {
            return false;
        }

        $user->status = User::STATUS_CONFIRMED;
        $user->activation_code = null;

        $user->save();

        return true;
    }

    /**
     * Validate request on resend
     *
     * @param Request $request
     *
     * @return ReturnedValidator
     */
    public function validateResendActivationCodeRequest(Request $request)
    {
        $rules = [
            'email' => 'required|email'
        ];

        $messages = [
            'email.required' => TranslationCode::ERROR_ACTIVATE_EMAIL_REQUIRED,
            'email.email' => TranslationCode::ERROR_ACTIVATE_EMAIL_INVALID
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Resend registration mail
     *
     * @param Request $request
     *
     * @return array|bool
     */
    public function resendRegisterMail(Request $request)
    {
        $user = User::whereEncrypted('email', $request->get('email'))->first();

        if (!$user) {
            return ['email' => TranslationCode::ERROR_ACTIVATE_EMAIL_NOT_REGISTERED];
        }

        if ($user->status === User::STATUS_CONFIRMED) {
            return ['account' => TranslationCode::ERROR_ACTIVATE_ACCOUNT_ACTIVATED];
        }

        if ($user->updated_at->addMinute() > Carbon::now()) {
            return ['code' => TranslationCode::ERROR_ACTIVATE_CODE_SEND_COOLDOWN];
        }

        /** @var EmailService $emailService */
        $emailService = new EmailService();

        $emailService->sendActivationCode($user, $user->language->code);

        $user->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $user->save();

        return false;
    }
}
