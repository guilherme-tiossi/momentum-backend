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

    public function createPost(array $data, array $attachments)
    {
        $post = Post::make($data['data']['attributes']);
        $post->user()->associate(Auth::id());

        $post->save();

        foreach ($attachments as $attachment) {
            $post->attachments()->attach($attachment->id);
        }

        event(new CreatedPost($post));

        return $post;
    }

    public function updatePost(array $data, Post $post, array $attachments)
    {
        $post->fill($data['data']['attributes']);

        $post->save();

        foreach ($attachments as $attachment) {
            $post->attachments()->attach($attachment->id);
        }

        return $post;
    }

    public function deletePost(Post $post)
    {
        $post->delete();
    }
}
