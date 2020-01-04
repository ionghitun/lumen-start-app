<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

/**
 * Class EmailService
 *
 * @package App\Services
 */
class EmailService
{
    /**
     * Send code on email for forgot password
     *
     * @param User $user
     * @param $languageCode
     */
    public function sendForgotPasswordCode(User $user, $languageCode)
    {
        Lang::setLocale($languageCode);

        Mail::send('emails.forgot', ['user' => $user], function ($message) use ($user) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->subject(Lang::get('forgot.subject'));

            $message->to($user->email);
        });
    }

    /**
     * Send code on email for account activation
     *
     * @param $user
     * @param $languageCode
     */
    public function sendActivationCode(User $user, $languageCode)
    {
        Lang::setLocale($languageCode);

        Mail::send('emails.activation', ['user' => $user],
            function ($message) use ($user) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->subject(Lang::get('activate.subject'));

                $message->to($user->email);
            });
    }

    /**
     * Send code on email for email change
     *
     * @param $user
     * @param $languageCode
     */
    public function sendEmailConfirmationCode(User $user, $languageCode)
    {
        Lang::setLocale($languageCode);

        Mail::send('emails.emailChange', ['user' => $user],
            function ($message) use ($user) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->subject(Lang::get('emailChange.subject'));

                $message->to($user->email);
            });
    }
}
