<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Task;
use App\Services\TaskService;
use App\Http\Requests\TaskRequest;
use App\Transformers\TaskTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        $tasks = $this->taskService->listTasks();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($tasks, new TaskTransformer(), 'tasks')
            ->respond(200);
    }

    public function store(TaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());

        return fractal()
            ->parseIncludes(['parent'])
            ->serializeWith(new JsonApiSerializer())
            ->item($task, new TaskTransformer(), 'tasks')
            ->respond(201);
    }

    public function show(Task $task)
    {
        return fractal()
            ->parseIncludes(['parent'])
            ->serializeWith(new JsonApiSerializer())
            ->item($task, new TaskTransformer(), 'tasks')
            ->respond(200);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $task = $this->taskService->updateTask($request->validated(), $task);

        return fractal()
            ->parseIncludes(['parent'])
            ->serializeWith(new JsonApiSerializer())
            ->item($task, new TaskTransformer(), 'tasks')
            ->respond(200);
    }

    public function destroy(Task $task)
    {
        if (($task->user_id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->taskService->deleteTask($task);

        return response()->noContent();
    }

    public function getTaskReport()
    {
        $results = $this->taskService->getTaskReport();

        return $results;
    }
}
