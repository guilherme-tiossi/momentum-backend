<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Post;
use App\Services\PostService;
use App\Http\Requests\PostRequest;
use App\Services\AttachmentService;
use App\Transformers\PostTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class PostController extends Controller
{
    private PostService $postService;
    private AttachmentService $attachmentService;

    public function __construct(PostService $postService, AttachmentService $attachmentService)
    {
        $this->postService = $postService;
        $this->attachmentService = $attachmentService;
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
        $attachments = [];
        if ($request->hasFile('data.attributes.attachments')) {
            foreach ($request->file('data.attributes.attachments') as $file) {
                $attachments[] = $this->attachmentService->createAttachment($file);
            }
        }

        $post = $this->postService->createPost($request->validated(), $attachments);

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($post, new PostTransformer(), 'posts')
            ->respond(201);
    }

    public function show(Post $post)
    {
        return fractal()
            ->parseIncludes(['comments'])
            ->serializeWith(new JsonApiSerializer())
            ->item($post, new PostTransformer(), 'posts')
            ->respond();
    }

    public function update(PostRequest $request, Post $post)
    {
        $attachments = [];
        if ($request->hasFile('data.attributes.attachments')) {
            foreach ($request->file('data.attributes.attachments') as $file) {
                $attachments[] = $this->attachmentService->createAttachment($file);
            }
        }

        $post = $this->postService->updatePost($request->validated(), $post, $attachments);

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
