<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Models\Repost;

class RepostService
{
    public function listReposts()
    {
        $user = Auth::user();

        return $user->reposts;
    }

    public function createRepost(array $data)
    {
        $repost = Repost::make(['timestamp' => Carbon::now()]);
        $repost->user()->associate(Auth::id());
        $repost->post()->associate($data['data']['relationships']['post']['data']['id']);
        $repost->save();

        return $repost;
    }

    public function deleteRepost(Repost $repost)
    {
        $repost->delete();
    }
}
