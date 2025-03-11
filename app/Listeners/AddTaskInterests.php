<?php

namespace App\Listeners;

use Http;
use App\Events\CreatedTask;
use App\Services\Neo4jService;

class AddTaskInterests
{
    public function handle(CreatedTask $event)
    {
        $task = $event->task;

        $data = [
            'text' => $task->title . '. ' . $task->description
        ];

        $response = Http::post('http://localhost:8001/extract-interests', $data);

        if ($response->successful()) {
            $interests = $response->json('interests');

            $neo4jService = new Neo4jService();
            $neo4jService->addInterests($task->user, $interests);
        }
    }
}
