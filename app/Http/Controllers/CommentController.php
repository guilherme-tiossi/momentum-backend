<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Post;
use App\Models\Comment;
use App\Services\CommentService;
use App\Http\Requests\CommentRequest;
use App\Transformers\CommentTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class CommentController extends Controller
{
    private CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function index()
    {
        $post = Post::findOrFail(request()->post);
        $comments = $this->commentService->listComments($post);

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($comments, new CommentTransformer(), 'comments')
            ->respond(200);
    }

    public function store(CommentRequest $request)
    {
        $comment = $this->commentService->createComment($request->validated());

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($comment, new CommentTransformer(), 'comments')
            ->respond(201);
    }

    public function destroy(Comment $comment)
    {
        if (($comment->user_id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->commentService->deleteComment($comment);

        return response()->noContent();
    }
}
