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

    /** @var null */
    private $result = null;

    /** @var null */
    private $pagination = null;

    /** @var null */
    private $errorMessage = null;

    /** @var bool */
    private $isError = false;

    /** @var bool */
    private $userFault = true;

    /** @var bool */
    private $refreshToken = true;

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
     * @param null $data
     * @param null $pagination
     * @param null $refreshToken
     *
     * @return JsonResponse
     */
    protected function successResponse($data = null, $pagination = null, $refreshToken = null)
    {
        $this->result = $data;
        $this->pagination = $pagination;
        $this->userFault = false;

        if (!is_null($refreshToken)) {
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
        $response = [
            'isError' => $this->isError,
            'userFault' => $this->userFault,
            'errorMessage' => $this->errorMessage,
            'result' => $this->result,
            'pagination' => $this->pagination
        ];

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
     * @param $errorMessage
     * @param null $refreshToken
     *
     * @return JsonResponse
     */
    protected function userErrorResponse($errorMessage, $refreshToken = null)
    {
        $this->isError = true;
        $this->errorMessage = $errorMessage;

        if (!is_null($refreshToken)) {
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
        $response = [
            'isError' => true,
            'userFault' => false,
            'errorMessage' => ['application' => TranslationCode::ERROR_APPLICATION],
            'result' => null,
            'pagination' => null
        ];

        return response()->json($response, Response::HTTP_OK);
    }
}
