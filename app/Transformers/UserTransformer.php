<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [];

    protected array $defaultIncludes = [];

    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'pfp' => $user->pfp,
            'bio' => $user->bio,
            'location' => $user->location,
            'header' => $user->header,
            'uses_default_pfp' => $user->uses_default_pfp,
            'uses_default_header' => $user->uses_default_header,
            'streak' => $user->streak,
            'followers' => ($user->followers && !$user->followers->isEmpty()) ? $user->followers->count() : null,
            'following' => ($user->following && !$user->following->isEmpty()) ? $user->following->count() : null,
            'created_at' => $user->created_at->format('d/m/Y')
        ];
    }
}
