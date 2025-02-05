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
            'username' => $user->username,
            'pfp' => $user->pfp,
            'header' => $user->header,
            'uses_default_pfp' => $user->uses_default_pfp,
            'uses_default_header' => $user->uses_default_header,
            'email' => $user->email,
        ];
    }
}
