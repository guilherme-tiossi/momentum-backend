<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use App\Transformers\UserTransformer;
use League\Fractal\Serializer\JsonApiSerializer;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
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
        $user = $this->userService->updateUser($request->validated(), $user);

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

        $this->userService->deleteUser($user);

        return response()->noContent();
    }
}
