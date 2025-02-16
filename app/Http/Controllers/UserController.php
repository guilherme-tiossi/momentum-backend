<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use App\Transformers\UserTransformer;
use App\Http\Requests\UserUpdateRequest;
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
        // add interests
        $user->save();

        return fractal()
            ->serializeWith(new JsonApiSerializer())
            ->item($user, new UserTransformer(), 'users')
            ->respond(201);
    }

    public function show(User $user)
    {
        $user->fresh(['followers', 'following']);

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

    public function update(UserUpdateRequest $request, User $user)
    {
        $user = $this->userService->updateUser($request->validated(), $user);

        return fractal()
            ->parseIncludes(['address', 'plan'])
            ->serializeWith(new JsonApiSerializer())
            ->item($user, new UserTransformer(), 'users');
    }

    public function destroy(User $user)
    {
        if (($user->id != Auth::id())) {
            return response()->json(['error' => 'You cannot do this.'], 403);
        }

        $this->userService->deleteUser($user);

        return response()->noContent();
    }
}
