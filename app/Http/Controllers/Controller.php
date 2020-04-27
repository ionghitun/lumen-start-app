<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use IonGhitun\JwtToken\Jwt;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class Controller
 *
 * All controllers should extend this controller
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    /** @var BaseService */
    protected $baseService;

    /** @var bool */
    private $isError = false;

    /** @var array */
    private $errorMessages = [];

    /** @var bool */
    private $isForbidden = false;

    /** @var array */
    private $forbiddenMessages = [];

    /** @var bool */
    private $userFault = false;

    /** @var null */
    private $result = null;

    /** @var array */
    private $pagination = [];

    /** @var bool */
    private $refreshToken = false;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->baseService = new BaseService();
    }

    /**
     * Success response
     *
     * @param string|array|null $data
     * @param array|null $pagination
     * @param bool|null $refreshToken
     *
     * @return JsonResponse
     */
    protected function successResponse($data = null, $pagination = null, $refreshToken = null)
    {
        if ($data !== null) {
            $this->result = $data;
        }

        if ($pagination !== null) {
            $this->pagination = $pagination;
        }

        if ($refreshToken !== null) {
            $this->refreshToken = $refreshToken;
        }

        return $this->buildResponse();
    }

    /**
     * Build the response.
     *
     * @return JsonResponse
     */
    private function buildResponse()
    {
        if ($this->isError) {
            $response = [
                'isError' => $this->isError,
                'userFault' => $this->userFault,
                'errorMessages' => $this->errorMessages
            ];
        } elseif ($this->isForbidden) {
            $response = [
                'isForbidden' => $this->isForbidden,
                'forbiddenMessages' => $this->forbiddenMessages
            ];
        } else {
            $response = [
                'isError' => $this->isError
            ];

            if ($this->result !== null) {
                $response['result'] = $this->result;
            }

            if (count($this->pagination) > 0) {
                $response['pagination'] = $this->pagination;
            }
        }

        if ($this->refreshToken && Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            $response['refreshedToken'] = Jwt::generateToken([
                'id' => $user->id
            ]);
        }

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Return user fault response.
     *
     * @param array $errorMessages
     * @param bool|null $refreshToken
     *
     * @return JsonResponse
     */
    protected function userErrorResponse(array $errorMessages, $refreshToken = null)
    {
        $this->isError = true;
        $this->userFault = true;
        $this->errorMessages = $errorMessages;

        if ($refreshToken !== null) {
            $this->refreshToken = $refreshToken;
        }

        return $this->buildResponse();
    }

    /**
     * Return application error response.
     *
     * @return JsonResponse
     */
    protected function errorResponse()
    {
        $this->isError = true;
        $this->errorMessages = ['application' => TranslationCode::ERROR_APPLICATION];

        return $this->buildResponse();
    }

    /**
     * Return access forbidden response.
     *
     * @return JsonResponse
     */
    protected function forbiddenResponse()
    {
        $this->isForbidden = true;
        $this->forbiddenMessages = ['forbidden' => TranslationCode::ERROR_FORBIDDEN];

        return $this->buildResponse();
    }
}
