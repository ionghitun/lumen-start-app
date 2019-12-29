<?php

namespace App\Console\Commands;

use App\Models\UserToken;
use App\Services\LogService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class DeleteExpiredTokensCommand
 *
 * Delete all expired token.
 * Should be running once a day.
 *
 * @package App\Console\Commands
 */
class DeleteExpiredTokensCommand extends Command
{
    /** @var string */
    protected $signature = "delete:expiredTokens";

    /** @var string */
    protected $description = "Remove expired tokens from database.";

    /**
     * Command handle
     *
     * We delete separate each token in case we add something to model boot.
     */
    public function handle()
    {
        try {
            $this->info("Command [delete:expiredTokens] start: " . Carbon::now()->format('Y-m-d H:i:s'));

            DB::beginTransaction();

            /** @var Collection $userTokens */
            $userTokens = UserToken::where('expire_on', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->get();

            $this->info("Found " . $userTokens->count() . "tokens to be removed.");

            /** @var UserToken $userToken */
            foreach ($userTokens as $userToken) {
                $userToken->delete();
            }

            DB::commit();

            $this->info("Command [delete:expiredTokens] end: " . Carbon::now()->format('Y-m-d H:i:s'));
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            $this->error($e->getMessage());
        }
    }
}
