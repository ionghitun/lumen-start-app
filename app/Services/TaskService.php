<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class TaskService
 *
 * @package App\Services
 */
class TaskService
{
    /**
     * Get logged user tasks.
     *
     * @param Request $request
     *
     * @return Builder
     */
    public function getUserTasksBuilder(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $userTasks = UserTask::with(['user' => function ($query) {
            $query->select(['id', 'name']);
        }, 'assignedUser' => function ($query) {
            $query->select(['id', 'name']);
        }])->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('assigned_user_id', $user->id);
        });

        if ($request->has('search')) {
            $search = $request->get('search');

            if ($search !== '') {
                $userTasks = $userTasks->where('description', 'LIKE', '%' . $search . '%');
            }
        }

        if ($request->has('status')) {
            $status = $request->get('status');

            if (in_array($status, [UserTask::STATUS_ASSIGNED, UserTask::STATUS_COMPLETED])) {
                $userTasks = $userTasks->where('status', $status);
            }
        }

        return $userTasks;
    }
}
