<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Class EmailService
 *
 * TODO refactor whole class.
 *
 * @package App\Services
 */
class EmailService
{
    /**
     * Send code on email for forgot password
     *
     * @param User $user
     * @param $language
     */
    public function sendForgotPasswordCode(User $user, $language)
    {
        Mail::send('emails.' . $language . '.forgot', ['user' => $user], function ($message) use ($user) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->subject(env('APP_NAME') . ' - Forgot password code');

            $message->to($user->email);
        });
    }

    /**
     * Send code on email for account activation
     *
     * @param $user
     * @param $language
     */
    public function sendActivationCode(User $user, $language)
    {
        Mail::send('emails.' . $language . '.activation', ['user' => $user],
            function ($message) use ($user) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->subject(env('APP_NAME') . ' - Activate account');

                $message->to($user->email);
            });
    }

    /**
     * Send code on email for email change
     *
     * @param $user
     * @param $language
     */
    public function sendEmailConfirmationCode(User $user, $language)
    {
        Mail::send('emails.' . $language . '.emailConfirmation', ['user' => $user],
            function ($message) use ($user) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->subject(env('APP_NAME') . ' - Confirm email');

                $message->to($user->email);
            });
    }
}
