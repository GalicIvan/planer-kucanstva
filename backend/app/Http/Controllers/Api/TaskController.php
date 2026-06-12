<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * GET /api/tasks
     * Supports filtering by status and assigned user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json([]);
        }

        $query = Task::query()
            ->where('household_id', $household->id)
            ->with(['assignee:id,name', 'creator:id,name']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($assignedTo = $request->query('assigned_to_user_id')) {
            $query->where('assigned_to_user_id', $assignedTo);
        }

        return response()->json($query->orderBy('due_date')->get());
    }

    /**
     * POST /api/tasks
     */
    public function store(TaskRequest $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json(['message' => 'You must belong to a household before adding tasks.'], 422);
        }

        $task = Task::create([
            'household_id' => $household->id,
            'assigned_to_user_id' => $request->assigned_to_user_id,
            'created_by_user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'pending',
            'due_date' => $request->due_date,
        ]);

        return response()->json($task->load(['assignee:id,name', 'creator:id,name']), 201);
    }

    /**
     * PUT/PATCH /api/tasks/{task}
     */
    public function update(TaskRequest $request, Task $task)
    {
        $task->fill($request->only(['title', 'description', 'assigned_to_user_id', 'status', 'due_date']));
        $task->save();

        return response()->json($task->load(['assignee:id,name', 'creator:id,name']));
    }

    /**
     * PATCH /api/tasks/{task}/status
     * Quick endpoint used by the "mark as done" checkbox.
     */
    public function updateStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,done'],
        ]);

        $task->update(['status' => $data['status']]);

        return response()->json($task->load(['assignee:id,name', 'creator:id,name']));
    }

    /**
     * DELETE /api/tasks/{task}
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }
}
