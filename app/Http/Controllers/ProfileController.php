<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Services\ProfileService;
use App\Transformers\ProfilePostTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class ProfileController extends Controller
{
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function getUserProfilePosts(User $user)
    {
        $posts = $this->profileService->getUserProfilePosts($user);

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->collection($posts, new ProfilePostTransformer(), 'posts');
    }
}
