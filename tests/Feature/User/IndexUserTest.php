<?php

namespace Tests\Feature\User;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ExpectedResponse\DataResponse;
use Tests\TestCase;

class IndexUserTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider indexUsersDataProvider */
    public function testIndexUsers(array $data, Closure $responseFactory, Closure $preDefinedFactory)
    {
        $user = User::factory()->createQuietly();

        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user);

        $preDefinedFactory();

        $url = route('users.index');

        // Call Endpoints
        $response = $this->json(
            'GET',
            $url,
            $data,
        );

        $expected = $responseFactory($this);
        $expected->assert($response);
    }

    public static function indexUsersDataProvider()
    {
        $commonStructure = [
            'id',
            'name',
            'email',
            'phone_number',
            'last_login',
            'role',
            'created_at',
        ];

        $usersSuccessfullyResponse = function () use ($commonStructure): DataResponse {
            return new DataResponse([
                'data' => [
                    [
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'last_login' => null,
                        'created_at' => '2024-01-17T21:55:00.000000Z',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'last_login' => null,
                        'role' => [
                            'id' => 1,
                            'code' => 'SUPER_ADMIN',
                            'title' => null,
                            'description' => null,
                        ],
                        'created_at' => '2024-01-17T21:55:00.000000Z',
                    ],
                ],
                'pagination' => static::getPaginationAttributes(1, 1, 1, 10, 2, 2),
            ], [
                'data' => [
                    $commonStructure,
                    $commonStructure,
                ],
            ], 'items');
        };
        $trashedUsersSuccessfullyResponse = function () use ($commonStructure): DataResponse {
            return new DataResponse([
                'data' => [
                    [
                        'id' => 3,
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'last_login' => null,
                        'created_at' => '2024-01-17T21:55:00.000000Z',
                    ],
                ],
                'pagination' => static::getPaginationAttributes(1, 1, 1, 10, 1, 1),
            ], [
                'data' => [
                    $commonStructure,
                ],
            ], 'items');
        };
        $searchedUsersSuccessfullyResponse = function () use ($commonStructure): DataResponse {
            return new DataResponse([
                'data' => [
                    [
                        'id' => 3,
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'last_login' => null,
                        'created_at' => '2024-01-17T21:55:00.000000Z',
                    ],
                ],
                'pagination' => static::getPaginationAttributes(1, 1, 1, 10, 1, 1),
            ], [
                'data' => [
                    $commonStructure,
                ],
            ], 'items');
        };
        $paginatedUsersSuccessfullyResponse = function () use ($commonStructure): DataResponse {
            return new DataResponse([
                'data' => [
                    [
                        'id' => 3,
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'last_login' => null,
                        'created_at' => '2024-01-17T21:55:00.000000Z',
                    ],
                ],
                'pagination' => static::getPaginationAttributes(2, 2, 2, 1, 2, 2),
            ], [
                'data' => [
                    $commonStructure,
                ],
            ], 'items');
        };

        return [
            'Users Can be Listed Successfully' => [
                [],
                $usersSuccessfullyResponse,
                function () {
                    $user1 = User::factory()->create([
                        'id' => 2,
                        'type' => 'superadmin',
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',
                    ]);

                    $user1->assignRole('SUPER_ADMIN');

                    $user2 = User::factory()->create([
                        'id' => 3,
                        'type' => 'superadmin',
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',
                    ]);
                },
            ],
            'Trashed Users Can be Listed Successfully [With Trashed Key]' => [
                [
                    'trashed' => 'show',
                ],
                $trashedUsersSuccessfullyResponse,
                function () {
                    $user1 = User::factory()->create([
                        'id' => 2,
                        'type' => 'superadmin',
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',
                    ]);

                    $user2 = User::factory()->create([
                        'id' => 3,
                        'type' => 'superadmin',
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',
                        'deleted_at' => '2024-04-21 15:20:00',
                    ]);
                },
            ],
            'Searched Users Can be Listed Successfully [With Searched Key]' => [
                [
                    'search' => [
                        'value' => 'Second',
                    ],
                ],
                $searchedUsersSuccessfullyResponse,
                function () {
                    $user1 = User::factory()->create([
                        'id' => 2,
                        'type' => 'superadmin',
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',

                    ]);

                    $user2 = User::factory()->create([
                        'id' => 3,
                        'type' => 'superadmin',
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',
                    ]);
                },
            ],
            'Paginated Users Can be Listed Successfully [With Pagination Key]' => [
                [
                    'offset' => 1,
                    'page' => 2,
                ],
                $paginatedUsersSuccessfullyResponse,
                function () {
                    $user1 = User::factory()->create([
                        'id' => 2,
                        'type' => 'superadmin',
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',
                    ]);

                    $user2 = User::factory()->create([
                        'id' => 3,
                        'type' => 'superadmin',
                        'name' => 'Second Name',
                        'email' => 'second@gmail.com',
                        'phone_number' => '(+90) 1235647895623',
                        'status' => 'PENDING',
                        'created_at' => '2024-01-17 18:55:00',

                    ]);
                },
            ],
        ];
    }
}
