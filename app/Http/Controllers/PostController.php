<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Post;
use App\Services\PostService;
use App\Http\Requests\PostRequest;
use App\Transformers\PostTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class PostController extends Controller
{
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index()
    {
        $posts = $this->postService->listPosts();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($posts, new PostTransformer(), 'posts')
            ->respond(200);
    }

    public function store(PostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($post, new PostTransformer(), 'posts')
            ->respond(201);
    }

    public function show(post $post)
    {
        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($post, new PostTransformer(), 'posts')
            ->respond();
    }

    public function update(PostRequest $request, Post $post)
    {
        $post = $this->postService->updatePost($request->validated(), $post);

        return fractal()
            ->parseIncludes(['address', 'plan'])
            ->serializeWith(new JsonApiSerializer())
            ->item($post, new PostTransformer(), 'posts');
    }

    public function destroy(Post $post)
    {
        if (($post->user_id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->postService->deletePost($post);

        return response()->noContent();
    }
}
