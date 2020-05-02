<?php

namespace App\Console\Commands;

use App\Models\UserToken;
use App\Services\LogService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class DeleteExpiredTokensCommand.
 *
 * Delete all expired token.
 * Should be running once a day.
 *
 * @package App\Console\Commands
 */
class DeleteExpiredTokensCommand extends Command
{
    /** @var string */
    protected $signature = 'delete:expiredTokens';

    /** @var string */
    protected $description = 'Remove expired tokens from database.';

    /**
     * Command handle.
     */
    public function handle()
    {
        try {
            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Command [delete:expiredTokens] started.');

            DB::beginTransaction();

            $this->removeExpiredTokens();

            DB::commit();

            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Command [delete:expiredTokens] ended.');
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t));

            $this->error($t->getMessage());
        }
    }

    /**
     * Remove expired tokens.
     * We delete separate each token in case we add something to model boot.
     *
     * @throws Exception
     */
    private function removeExpiredTokens()
    {
        $userTokens = UserToken::where('expire_on', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                               ->get();

        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Found ' . $userTokens->count() . ' tokens to be removed.');

        foreach ($userTokens as $userToken) {
            $userToken->delete();
        }
    }
}
