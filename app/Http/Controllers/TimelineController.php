<?php

namespace App\Http\Controllers;

use App\Services\TimelineService;
use App\Transformers\PostTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class TimelineController extends Controller
{
    private TimelineService $timelineService;

    public function __construct(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    public function index()
    {
        $posts = $this->timelineService->getTimelinePosts();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($posts, new PostTransformer(), 'posts')
            ->respond(200);
    }
}
