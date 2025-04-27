<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Like;
use App\Services\LikeService;
use App\Http\Requests\LikeRequest;
use App\Transformers\LikeTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class LikeController extends Controller
{
    private LikeService $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    public function index()
    {
        $likes = $this->likeService->listLikes();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($likes, new LikeTransformer(), 'likes')
            ->respond(200);
    }

    public function store(LikeRequest $request)
    {
        $like = $this->likeService->createLike($request->validated());

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($like, new LikeTransformer(), 'likes')
            ->respond(201);
    }

    public function unlike()
    {
        $this->likeService->unlike(request()->post);

        return response()->noContent();
    }

    public function destroy(Like $like)
    {
        if (($like->user_id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->likeService->deleteLike($like);

        return response()->noContent();
    }
}
