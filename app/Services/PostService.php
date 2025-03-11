<?php

namespace App\Services;

use Auth;
use App\Models\Post;
use App\Events\CreatedPost;

class PostService
{
    public function listPosts()
    {
        $userParam = request()->user;

        $query = Post::with(['user'])
            ->byUser($userParam);

        return $query->get();
    }

    public function createPost(array $data)
    {
        $post = Post::make($data['data']['attributes']);
        $post->user()->associate(Auth::id());
        $post->save();

        event(new CreatedPost($post));

        return $post;
    }

    public function updatePost(array $data, Post $post)
    {
        $post->fill($data['data']['attributes']);

        $post->save();

        return $post;
    }

    public function deletePost(Post $post)
    {
        $post->delete();
    }
}
