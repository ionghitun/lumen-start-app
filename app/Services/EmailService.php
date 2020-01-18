<?php

namespace App\Services;

use App\Jobs\SendMailJob;
use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Queue;

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

        $sendMail = new SendMail(
            $user->email,
            Lang::get('forgot.subject'),
            'emails.forgot',
            [
                'name' => $user->name,
                'forgot_code' => $user->forgot_code
            ]
        );

        Queue::push(new SendMailJob($sendMail));
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

        $sendMail = new SendMail(
            $user->email,
            Lang::get('activate.subject'),
            'emails.activation',
            [
                'name' => $user->name,
                'activation_code' => $user->activation_code
            ]
        );

        Queue::push(new SendMailJob($sendMail));
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

        $sendMail = new SendMail(
            $user->email,
            Lang::get('emailChange.subject'),
            'emails.emailChange',
            [
                'name' => $user->name,
                'activation_code' => $user->activation_code
            ]
        );

        Queue::push(new SendMailJob($sendMail));
    }
}
