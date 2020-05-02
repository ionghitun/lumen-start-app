<?php

namespace App\Exceptions;

use App\Constants\TranslationCode;
use App\Services\LogService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Class Handler
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  Throwable  $throwable
     *
     * @throws Exception
     */
    public function report(Throwable $throwable)
    {
        parent::report($throwable);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * When in production we return same json structure even when error occurred.
     *
     * @param  Request  $request
     * @param  Throwable  $throwable
     *
     * @return JsonResponse|Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $throwable)
    {
        if (env('APP_DEBUG')) {
            return parent::render($request, $throwable);
        }

        Log::error(LogService::getThrowableTraceAsString($throwable, $request));

        $response = [
            'isError'       => true,
            'userFault'     => false,
            'errorMessages' => ['application' => TranslationCode::ERROR_APPLICATION]
        ];

        return response()->json($response, Response::HTTP_OK);
    }
}
