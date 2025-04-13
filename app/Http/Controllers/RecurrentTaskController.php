<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\RecurrentTask;
use App\Services\RecurrentTaskService;
use App\Http\Requests\RecurrentTaskRequest;
use App\Transformers\RecurrentTaskTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class RecurrentTaskController extends Controller
{
    private RecurrentTaskService $recurrentTaskService;

    public function __construct(RecurrentTaskService $recurrentTaskService)
    {
        $this->recurrentTaskService = $recurrentTaskService;
    }

    public function index()
    {
        $tasks = $this->recurrentTaskService->listRecurrentTasks();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($tasks, new RecurrentTaskTransformer(), 'recurrent_tasks')
            ->respond(200);
    }

    public function store(RecurrentTaskRequest $request)
    {
        $task = $this->recurrentTaskService->createRecurrentTask($request->validated());

        return fractal()
            ->parseIncludes(['parent'])
            ->serializeWith(new JsonApiSerializer())
            ->item($task, new RecurrentTaskTransformer(), 'recurrent_tasks')
            ->respond(201);
    }

    public function show(RecurrentTask $recurrentTask)
    {
        return fractal()
            ->parseIncludes(['parent'])
            ->serializeWith(new JsonApiSerializer())
            ->item($recurrentTask, new RecurrentTaskTransformer(), 'recurrent_tasks')
            ->respond(200);
    }

    public function update(RecurrentTaskRequest $request, RecurrentTask $recurrentTask)
    {
        $recurrentTask = $this->recurrentTaskService->updateRecurrentTask($request->validated(), $recurrentTask);

        return fractal()
            ->parseIncludes(['parent'])
            ->serializeWith(new JsonApiSerializer())
            ->item($recurrentTask, new RecurrentTaskTransformer(), 'recurrent_tasks')
            ->respond(200);
    }

    public function destroy(RecurrentTask $recurrentTask)
    {
        if (($recurrentTask->user_id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->recurrentTaskService->deleteRecurrentTask($recurrentTask);

        return response()->noContent();
    }
}
