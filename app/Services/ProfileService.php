<?php

namespace App\Services;

use Auth;
use App\Models\User;
use App\Models\Like;
use App\Models\Post;
use App\Models\Repost;

class ProfileService
{
    public function getUserProfilePosts(User $user)
    {
        $authId = Auth::id();

        $posts = Post::where('user_id', $user->id)
            ->with('user')
            ->addSelect([
                'liked_by_user' => Like::selectRaw('1')
                    ->where('user_id', auth()->id())
                    ->whereColumn('post_id', 'posts.id')
                    ->limit(1)
            ])
            ->addSelect([
                'reposted_by_user' => Repost::selectRaw('1')
                    ->where('user_id', auth()->id())
                    ->whereColumn('post_id', 'posts.id')
                    ->limit(1)
            ])
            ->get();

        $reposts = Repost::where('user_id', $user->id)
            ->with(['post.user', 'post' => function ($query) use ($authId) {
                $query->addSelect([
                    'liked_by_user' => Like::selectRaw('1')
                        ->where('user_id', $authId)
                        ->whereColumn('post_id', 'posts.id')
                        ->limit(1),
                    'reposted_by_user' => Repost::selectRaw('1')
                        ->where('user_id', $authId)
                        ->whereColumn('post_id', 'posts.id')
                        ->limit(1)
                ]);
            }])
            ->get()->transform(function ($repost) {
                $post = $repost->post;
                $post->reposted = true;
                return $post;
            });

        return $posts->merge($reposts)->sortByDesc('created_at')->values();
    }
}
