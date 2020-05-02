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
     * @param  int  $canManage
     * @param  bool  $onlyOwn
     *
     * @return UserTask|Builder
     */
    public function getUserTasksBuilder($canManage = RolePermission::MANAGE_OWN, $onlyOwn = false)
    {
        /** @var User $user */
        $user = Auth::user();

        $userTasks = UserTask::with([
            'user'         => function ($query) {
                $query->select(['id', 'name']);
            },
            'assignedUser' => function ($query) {
                $query->select(['id', 'name']);
            }
        ]);

        if ($canManage === RolePermission::MANAGE_OWN) {
            $userTasks = $userTasks->where(function ($query) use ($user, $onlyOwn) {
                $query->where('user_id', $user->id);

                if (!$onlyOwn) {
                    $query->orWhere('assigned_user_id', $user->id);
                }
            });
        }

        return $userTasks;
    }
}
