<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use ApiResponses;

    /**
     * All users
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = UserResource::collection(User::all());
        return $this->success($users, 'Done', Response::HTTP_OK);
    }

    /**
     * Show specific user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = User::findOrFail(request('id'));
        return $this->success(new UserResource($user), 'Done', Response::HTTP_OK);
    }

    /**
     * Update specific user
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = User::findOrFail(request('id'));
        if ($user->id !== Auth::user()->id) {
            return $this->failed('Not authorized to change this user data', 401);
        }

        $user->update($request->all());
        return $this->success(new UserResource($user), "Done", Response::HTTP_ACCEPTED);
    }

    /**
     * Destroy specific user.
     *
     * @param User $user
     * @return JsonResponse|bool
     */
    public function destroy(): JsonResponse|bool
    {
        $user = User::findOrFail(request('id'));
        $user->delete();
        return $this->success(null, 'Deleted', Response::HTTP_NO_CONTENT);
    }
}
