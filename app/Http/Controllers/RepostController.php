<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Repost;
use App\Services\RepostService;
use App\Http\Requests\RepostRequest;
use App\Transformers\RepostTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class RepostController extends Controller
{
    private RepostService $repostService;

    public function __construct(RepostService $repostService)
    {
        $this->repostService = $repostService;
    }

    public function index()
    {
        $reposts = $this->repostService->listReposts();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($reposts, new RepostTransformer(), 'reposts')
            ->respond(200);
    }

    public function store(RepostRequest $request)
    {
        $repost = $this->repostService->createRepost($request->validated());

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($repost, new RepostTransformer(), 'reposts')
            ->respond(201);
    }

    public function depost()
    {
        $this->repostService->depost(request()->post);

        return response()->noContent();
    }

    public function destroy(Repost $repost)
    {
        if (($repost->user_id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->repostService->deleteRepost($repost);

        return response()->noContent();
    }
}
