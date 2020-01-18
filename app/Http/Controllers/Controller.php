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
    private $errorMessage = [];

    /** @var bool */
    private $isForbidden = false;

    /** @var array */
    private $forbiddenMessage = [];

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
                'errorMessage' => $this->errorMessage
            ];
        } elseif ($this->isForbidden) {
            $response = [
                'isForbidden' => $this->isForbidden,
                'forbiddenMessage' => $this->forbiddenMessage
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
     * @param array $errorMessage
     * @param bool|null $refreshToken
     *
     * @return JsonResponse
     */
    protected function userErrorResponse(array $errorMessage, $refreshToken = null)
    {
        $this->isError = true;
        $this->userFault = true;
        $this->errorMessage = $errorMessage;

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
        $this->errorMessage = ['application' => TranslationCode::ERROR_APPLICATION];

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
        $this->forbiddenMessage = ['forbidden' => TranslationCode::ERROR_FORBIDDEN];

        return $this->buildResponse();
    }
}
