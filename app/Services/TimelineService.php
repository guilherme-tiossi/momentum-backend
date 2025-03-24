<?php

namespace App\Services;

use Auth;
use App\Models\Like;
use App\Models\Post;
use App\Models\Repost;

class TimelineService
{
    public function getTimelinePosts()
    {
        $user = Auth::user();

        $followingIds = $user->following()->pluck('users.id');
        $followingIds[] = $user->id;

        $posts = Post::whereIn('user_id', $followingIds)
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
            ->orderBy('updated_at', 'desc')
            ->get();

        return $posts;
    }
}
