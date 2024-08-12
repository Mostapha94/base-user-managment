<?php

namespace Tests\Feature\User;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ExpectedResponse\SuccessResponse;
use Tests\ExpectedResponse\ValidationResponse;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider updateUserDataProvider */
    public function testUpdateUser(int $id, array $data, Closure $responseFactory, Closure $preDefinedFactory)
    {
        $preDefinedFactory();

        $url = route('users.update', $id);

        $user = User::factory()->createQuietly();

        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user);

        $response = $this->json(
            'POST',
            $url,
            $data,
        );

        $expected = $responseFactory($this);
        $expected->assert($response);
    }

    public static function updateUserDataProvider()
    {
        $userStoredSuccessfullyResponse = function (): SuccessResponse {
            return new SuccessResponse();
        };

        $userCanNotBeStoredWithMissingRequiredData = function (): ValidationResponse {
            return new ValidationResponse(422, [
                'name' => [
                    'The Name field is required.',
                ],
                'email' => [
                    'The Email field is required.',
                ],
            ]);
        };

        $userCanNotBeStoredWithInvaildType = function (): ValidationResponse {
            return new ValidationResponse(422, [
                'phone_number' => [
                    'The Phone Number is invalid.',
                ],

            ]);
        };

        return [
            'User updated successfully' => [
                2,
                [
                    'name' => 'Second Name',
                    'email' => 'second@gmail.com',
                    'status' => 'ACTIVE',
                    'type' => 'admin',
                    'password' => '231247896521',
                    'password_confirmation' => '123456789',
                    'created_at' => '2024-04-20 15:20:00',
                ],
                $userStoredSuccessfullyResponse,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-04-20 15:20:00',
                    ]);
                },
            ],
            'User can not be updated with missing required data' => [
                2,
                [
                ],
                $userCanNotBeStoredWithMissingRequiredData,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'status' => 'PENDING',
                        'created_at' => '2024-04-20 15:20:00',
                    ]);
                },
            ],
            'User can not be updated with invaild phone number' => [
                2,
                [
                    'name' => 'Second Name2',
                    'email' => 'second2@gmail.com',
                    'phone_number' => '(+90) 4447824569',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                    'status' => 'PENDING',
                ],
                $userCanNotBeStoredWithInvaildType,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-04-20 15:20:00',
                    ]);
                },
            ],

        ];
    }
}
