<?php

namespace App\Http\Controllers;

use App\Constants\TranslationCode;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\LogService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getUserTasks(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $userRolePermission = $this->baseService->getUserPermissionActions($user->id, Permission::ID_TASKS);

            if ($userRolePermission->read !== RolePermission::PERMISSION_TRUE) {
                return $this->forbiddenResponse();
            }

            $userTasks = $this->taskService->getUserTasksBuilder($userRolePermission->manage);

            if ($request->has('search')) {
                $userTasks = $this->baseService->applySearch($userTasks, $request->get('search'));
            }

            if ($request->has('filters') && is_array($request->get('filters'))) {
                $userTasks = $this->baseService->applyFilters($userTasks, $request->get('filters'));
            }

            $userTasks = $this->baseService->applySortParams($request, $userTasks);

            $paginationParams = $this->baseService->getPaginationParams($request);

            $pagination = $this->baseService->getPaginationData($userTasks, $paginationParams['page'],
                $paginationParams['limit']);

            $userTasks = $userTasks->offset($paginationParams['offset'])->limit($paginationParams['limit'])->get();

            return $this->successResponse($userTasks, $pagination);
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t, $request));

            return $this->errorResponse();
        }
    }

    /**
     * Create a task
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function createTask(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $userRolePermission = $this->baseService->getUserPermissionActions($user->id, Permission::ID_TASKS);

            if ($userRolePermission->create !== RolePermission::PERMISSION_TRUE) {
                return $this->forbiddenResponse();
            }

            //TODO

            return $this->successResponse();
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t));

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
            /** @var User $user */
            $user = Auth::user();

            $userRolePermission = $this->baseService->getUserPermissionActions($user->id, Permission::ID_TASKS);

            if ($userRolePermission->read !== RolePermission::PERMISSION_TRUE) {
                return $this->forbiddenResponse();
            }

            $userTasks = $this->taskService->getUserTasksBuilder($userRolePermission->manage);

            $userTask = $userTasks->where('id', $id)->first();

            if (!$userTask) {
                return $this->userErrorResponse(['notFound' => TranslationCode::ERROR_NOT_FOUND]);
            }

            return $this->successResponse($userTask);
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t));

            return $this->errorResponse();
        }
    }

    /**
     * Update a task
     *
     * @param $id
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function updateTask($id, Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $userRolePermission = $this->baseService->getUserPermissionActions($user->id, Permission::ID_TASKS);

            if ($userRolePermission->update !== RolePermission::PERMISSION_TRUE) {
                return $this->forbiddenResponse();
            }

            $userTasks = $this->taskService->getUserTasksBuilder($userRolePermission->manage);

            $userTask = $userTasks->where('id', $id)->first();

            if (!$userTask) {
                return $this->userErrorResponse(['notFound' => TranslationCode::ERROR_NOT_FOUND]);
            }

            //TODO

            return $this->successResponse();
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t));

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
            /** @var User $user */
            $user = Auth::user();

            $userRolePermission = $this->baseService->getUserPermissionActions($user->id, Permission::ID_TASKS);

            if ($userRolePermission->delete !== RolePermission::PERMISSION_TRUE) {
                return $this->forbiddenResponse();
            }

            $userTasks = $this->taskService->getUserTasksBuilder($userRolePermission->manage, true);

            $userTask = $userTasks->where('id', $id)->first();

            if (!$userTask) {
                return $this->userErrorResponse(['notFound' => TranslationCode::ERROR_NOT_FOUND]);
            }

            DB::beginTransaction();

            $userTask->delete();

            DB::commit();

            return $this->successResponse();
        } catch (Throwable $t) {
            Log::error(LogService::getThrowableTraceAsString($t));

            return $this->errorResponse();
        }
    }
}
