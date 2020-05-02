<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Services\LogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ApiController
 *
 * Here should be details about this application.
 *
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    /**
     * We should return information about current API version.
     *
     * @return JsonResponse
     */
    public function version()
    {
        try {
            $apiDetails = [
                'cors' => 'enabled',
                'user' => [
                    'register'    => 'enabled',
                    'account'     => [
                        'needActivation'   => true,
                        'canResetPassword' => true
                    ],
                    'socialLogin' => 'enabled'
                ]
            ];

            return $this->successResponse($apiDetails);
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t));

            return $this->errorResponse();
        }
    }

    /**
     * Get all languages
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getLanguages(Request $request)
    {
        try {
            $languages = Language::all();

            return $this->successResponse($languages);
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t, $request));

            return $this->errorResponse();
        }
    }
}
