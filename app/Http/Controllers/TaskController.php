<?php

namespace App\Http\Controllers;

use App\Models\UserTask;
use App\Services\LogService;
use App\Services\TaskService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class TaskController
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

            if ($request->has('search')) {
                $userTasks = $this->baseService->applySearch($userTasks, $request->get('search'));
            }

            $userTasks = $this->baseService->applySortParams($request, $userTasks);

            $paginationParams = $this->baseService->getPaginationParams($request);

            $pagination = $this->baseService->getPaginationData($userTasks, $paginationParams['page'], $paginationParams['limit']);

            $userTasks = $userTasks->offset($paginationParams['offset'])->limit($paginationParams['limit'])->get();

            return $this->successResponse($userTasks, $pagination);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Create a task
     *
     * @return JsonResponse
     */
    public function createTask()
    {
        try {
            //TODO
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
            $userTask = UserTask::find($id);

            return $this->successResponse($userTask);
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }

    /**
     * Update a task
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function updateTask($id)
    {
        try {
            $userTask = UserTask::find($id);

            //TODO

            return $this->successResponse($userTask);
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
            $userTask = UserTask::find($id);

            //TODO

            if ($userTask) {
                $userTask->delete();
            }

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
