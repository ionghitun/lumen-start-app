<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Class TaskService
 *
 * @package App\Services
 */
class TaskService
{
    /**
     * Get tasks builder.
     *
     * @param int $canManage
     *
     * @return Builder
     */
    public function getUserTasksBuilder($canManage = 0)
    {
        /** @var User $user */
        $user = Auth::user();

        $userTasks = UserTask::with(['user' => function ($query) {
            $query->select(['id', 'name']);
        }, 'assignedUser' => function ($query) {
            $query->select(['id', 'name']);
        }]);

        if ($canManage === RolePermission::MANAGE_OWN) {
            $userTasks = $userTasks->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('assigned_user_id', $user->id);
            });
        }

        return $userTasks;
    }
}
