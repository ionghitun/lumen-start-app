<?php

namespace App\Jobs;

use App\Mail\SendMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

/**
 * Class SendMailJob
 *
 * @package App\Jobs
 */
class SendMailJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var int */
    public $tries = 3;

    /** @var SendMail|null */
    protected $sendMail = null;

    /**
     * SendMailJob constructor.
     *
     * @param SendMail $sendMail
     */
    public function __construct(SendMail $sendMail)
    {
        $this->sendMail = $sendMail;

        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Redis::throttle('key')->block(0)->allow(1)->every(1)->then(function () {
            Mail::send($this->sendMail);
        }, function () {
            $this->release(5);
        });
    }
}
