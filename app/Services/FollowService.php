<?php

namespace App\Services;

use App\Models\User;

class FollowService
{
    public function followUser(User $follower, User $userToFollow)
    {
        if ($follower->id != $userToFollow->id) {
            $follower->following()->attach($userToFollow->id);
            $follower->save();
        }
    }

    public function unfollowUser(User $follower, User $userToUnfollow)
    {
        $follower->following()->detach($userToUnfollow->id);
    }

    public function getFollowers(User $user)
    {
        return $user->followers;
    }

    public function getFollowing(User $user)
    {
        return $user->following;
    }
}
