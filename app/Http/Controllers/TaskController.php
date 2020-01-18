<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserTask;
use App\Services\LogService;
use App\Services\TaskService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            /** @var User $user */
            $user = Auth::user();

            /** @var RolePermission $userRolePermission */
            $userRolePermission = $this->baseService->getUserPermissionActions($user->id, Permission::ID_TASKS);

            if ($userRolePermission->read !== RolePermission::PERMISSION_TRUE) {
                return $this->forbiddenResponse();
            }

            /** @var Builder $userTasks */
            $userTasks = $this->taskService->getUserTasksBuilder($userRolePermission->manage);

            if ($request->has('search')) {
                $userTasks = $this->baseService->applySearch($userTasks, $request->get('search'));
            }

            if ($request->has('filters') && is_array($request->get('filters'))) {
                $userTasks = $this->baseService->applyFilters($userTasks, $request->get('filters'));
            }

            $userTasks = $this->baseService->applySortParams($request, $userTasks);

            $paginationParams = $this->baseService->getPaginationParams($request);

            $pagination = $this->baseService->getPaginationData($userTasks, $paginationParams['page'], $paginationParams['limit']);

            /** @var UserTask[] $userTasks */
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createTask(Request $request)
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
            /** @var UserTask|null $userTask */
            $userTask = UserTask::find($id);

            if (!$userTask) {
                return $this->userErrorResponse(['notFound' => TranslationCode::ERROR_NOT_FOUND]);
            }

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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateTask($id, Request $request)
    {
        try {
            /** @var UserTask|null $userTask */
            $userTask = UserTask::find($id);

            if (!$userTask) {
                return $this->userErrorResponse(['notFound' => TranslationCode::ERROR_NOT_FOUND]);
            }

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
            /** @var UserTask|null $userTask */
            $userTask = UserTask::find($id);

            if (!$userTask) {
                return $this->userErrorResponse(['notFound' => TranslationCode::ERROR_NOT_FOUND]);
            }

            DB::beginTransaction();

            $userTask->delete();

            DB::commit();

            return $this->successResponse();
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            return $this->errorResponse();
        }
    }
}
