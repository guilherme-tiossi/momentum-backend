<?php

namespace App\Listeners;

use Http;
use App\Events\CreatedPost;
use App\Services\Neo4jService;

class AddPostInterests
{
    public function handle(CreatedPost $event)
    {
        $post = $event->post;

        $data = [
            'text' => $post->text
        ];

        $response = Http::post('http://localhost:8001/extract-interests', $data);

        if ($response->successful()) {
            $interests = $response->json('interests');

            $neo4jService = new Neo4jService();
            $neo4jService->addInterests($post->user, $interests);
        }
    }
}
