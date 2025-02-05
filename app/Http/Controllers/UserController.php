<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Auth;
use App\Transformers\UserTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        $user = User::make($request->validated('data.attributes'));
        // adicionar interesses
        $user->save();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($user, new UserTransformer(), 'users')
            ->respond(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, Request $request)
    {
        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($user, new UserTransformer(), 'users')
            ->respond();
    }

    public function getAuth(Request $request)
    {
        return fractal()
            ->parseIncludes(['address', 'plan'])
            ->serializeWith(new JsonApiSerializer())
            ->item(Auth::user(), new UserTransformer(), 'users')
            ->respond();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $user->update($request->validated('data.attributes'));

        return fractal()
            ->parseIncludes(['address', 'plan'])
            ->serializeWith(new JsonApiSerializer())
            ->item($user, new UserTransformer(), 'users');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (($user->id != Auth::id())) {
            return response()->json(['error' => 'Você não tem autorização para realizar essa ação.'], 403);
        }

        $user->delete();

        return response()->noContent();
    }
}
