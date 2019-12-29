<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use App\Services\TaskService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class TaskController
 *
 * TODO
 *
 * @package App\Http\Controllers
 */
class TaskController extends Controller
{
    /** @var TaskService */
    private $taskService;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->taskService = new TaskService();
    }

    /**
     * Get users tasks
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUserTasks(Request $request)
    {
        try {
            $userTasks = $this->taskService->getUserTasksBuilder($request);

            $userTasks = $this->baseService->applySortParams($request, $userTasks);

            $paginationParams = $this->baseService->getPaginationParams($request);

            $pagination = $this->baseService->getPaginationData($userTasks, $paginationParams['page'], $paginationParams['limit']);

            $userTasks = $userTasks->offset($paginationParams['offset'])->limit($paginationParams['limit'])->get();

            return $this->successResponse($userTasks, $pagination);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Create a task
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createTask(Request $request)
    {
        try {

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Get a single task
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function getTask($id)
    {
        try {

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Update a task
     *
     * @param $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateTask($id, Request $request)
    {
        try {

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Delete a task
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function deleteTask($id)
    {
        try {

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
