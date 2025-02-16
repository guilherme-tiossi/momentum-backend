<?php

namespace App\Services;

use Auth;
use App\Models\Post;

class TimelineService
{
    public function getTimelinePosts()
    {
        $user = Auth::user();

        $followingIds = $user->following()->pluck('users.id');
        $followingIds[] = $user->id;

        $posts = Post::whereIn('user_id', $followingIds)
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $posts;
    }
}
