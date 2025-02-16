<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Services\FollowService;
use App\Transformers\UserTransformer;
use League\Fractal\Serializer\JsonApiSerializer;
use Illuminate\Database\UniqueConstraintViolationException;

class FollowController extends Controller
{
    private FollowService $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    /**
     * Follow a user.
     */
    public function follow(User $userToFollow)
    {
        $follower = Auth::user();

        try {
            $this->followService->followUser($follower, $userToFollow);
        } catch (UniqueConstraintViolationException $e) {
            return response()->json(['error' => "You are already following @$userToFollow->username."], 403);
        }

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($userToFollow, new UserTransformer(), 'users')
            ->respond(200);
    }

    public function unfollow(User $userToUnfollow)
    {
        $follower = Auth::user();
        $this->followService->unfollowUser($follower, $userToUnfollow);

        return response()->noContent();
    }

    public function followers(User $user)
    {
        $followers = $this->followService->getFollowers($user);

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($followers, new UserTransformer(), 'users')
            ->respond();
    }

    public function following(User $user)
    {
        $following = $this->followService->getFollowing($user);

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($following, new UserTransformer(), 'users')
            ->respond();
    }
}
