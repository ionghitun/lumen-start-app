<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class SendMail
 *
 * @package App\Mail
 */
class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * SendMail constructor.
     *
     * @param $to
     * @param string $subject
     * @param string $blade
     * @param array $data
     */
    public function __construct($to, string $subject, string $blade, array $data)
    {
        $this->to($to)
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject($subject)
            ->view($blade)
            ->with($data);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }
}
