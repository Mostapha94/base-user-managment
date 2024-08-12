<?php

namespace App\Services;

use App\Http\Requests\User\CreateUserRequest;
use App\Repositories\UserRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserService
{
    use ApiResponse;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieve a list of users based on the request.
     */
    public function index(Request $request, bool $withPaginator = true)
    {
        return $this->userRepository->listUsers($request, $withPaginator);
    }

    /**
     * Create a new user.
     */
    public function store(CreateUserRequest $request)
    {
        return $this->userRepository->createUser($request);
    }

    /**
     * Update an user.
     */
    public function update($request, string $id)
    {
        return $this->userRepository->updateUser($request, $id);
    }

    /**
     * Delete a user.
     */
    public function destroy(string $id)
    {
        return $this->userRepository->deleteUser($id);
    }

    /**
     * Restore a user.
     */
    public function restore(string $id)
    {
        return $this->userRepository->restoreUser($id);
    }
}
