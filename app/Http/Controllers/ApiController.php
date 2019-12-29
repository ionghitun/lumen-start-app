<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
                    'register' => 'enabled',
                    'account' => [
                        'needActivation' => true,
                        'canResetPassword' => true
                    ],
                    'socialLogin' => 'enabled'
                ]
            ];

            return $this->successResponse($apiDetails);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
