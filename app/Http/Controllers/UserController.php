<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserCollectionResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List All Users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = $this->userService->index($request);
        $users = new UserCollectionResource($data);

        return $this->success('Users', $users, 'items');
    }

    /**
     * Create New User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateUserRequest $request)
    {
        $data = $this->userService->store($request);

        return $this->success(__('app.save_success.title'), $data);
    }

    /**
     * Update personal information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $this->userService->update($request, $id);

        return $this->success(__('app.save_success.description'));
    }

    /**
     * Delete User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $this->userService->destroy($id);

        return $this->success(__('app.delete_success.description'));
    }

    /**
     * Restore User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request, string $id)
    {
        $this->userService->restore($id);

        return $this->success(__('app.restore_success.description'));
    }
}
