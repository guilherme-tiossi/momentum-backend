<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\CommentLike;
use App\Services\CommentLikeService;
use App\Http\Requests\CommentLikeRequest;
use App\Transformers\CommentLikeTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class CommentLikeController extends Controller
{
    private CommentLikeService $commentLikeService;

    public function __construct(CommentLikeService $commentLikeService)
    {
        $this->commentLikeService = $commentLikeService;
    }

    public function index()
    {
        $likes = $this->commentLikeService->listCommentLikes();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($likes, new CommentLikeTransformer(), 'likes')
            ->respond(200);
    }

    public function store(CommentLikeRequest $request)
    {
        $like = $this->commentLikeService->createLike($request->validated());

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($like, new CommentLikeTransformer(), 'likes')
            ->respond(201);
    }

    public function unlike()
    {
        $this->commentLikeService->unlike(request()->comment);

        return response()->noContent();
    }

    public function destroy(CommentLike $comment_like)
    {
        if ($comment_like->user_id != Auth::id()) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->commentLikeService->deleteLike($comment_like);

        return response()->noContent();
    }
}
