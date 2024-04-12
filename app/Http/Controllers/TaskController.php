<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user =  auth('sanctum')->user();
        $tasks = Task::where('user_id', $user->id)->get();
        return $this->success(TaskResource::collection($tasks), 'Tasks retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskStoreRequest $request)
    {
        $request->validated($request->all());

        $input = $request->all();
        $user =  auth('sanctum')->user();
        $input["user_id"] = $user->id;

        $task = Task::create($input);

        return $this->success(new TaskResource($task), 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::find($id);

        if (is_null($task)) {
            return $this->error('', 'Task not found.', 404);
        }

        $this->isNotAuthorized($task);

        return $this->success(new TaskResource($task), 'Task retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskUpdateRequest $request, string $id)
    {
        $request->validated($request->all());

        $input = $request->all();
        $user =  auth('sanctum')->user();
        $input["user_id"] = $user->id;

        $task = Task::find($id);

        $this->isNotAuthorized($task);

        $task->user_id = $input['user_id'];
        $task->name = $input['name'];
        $task->description = $input['description'];
        $task->priority = $input['priority'];
        $task->save();

        return $this->success(new TaskResource($task), 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);
        $this->isNotAuthorized($task);
        $task->delete();
        return $this->success("", 'Task deleted successfully.');
    }

    private function isNotAuthorized($task)
    {
        if (Auth::user()->id !== $task->user_id) {
            return $this->error('', 'Yor are not authorized to make this request.', 403);
        }
    }
}
