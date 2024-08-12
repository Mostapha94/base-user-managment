<?php

namespace Tests\Feature\User;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function PHPUnit\Framework\assertEquals;
use Tests\ExpectedResponse\SuccessResponse;
use Tests\ExpectedResponse\ValidationResponse;
use Tests\TestCase;

class StoreUserTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider storeUserDataProvider */
    public function testStoreUser(array $data, Closure $responseFactory, ?Closure $extraAsserts = null)
    {
        $url = route('users.store');

        $user = User::factory()->createQuietly();

        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user);

        $response = $this->json(
            'POST',
            $url,
            $data,
        );

        $extraAsserts ? $extraAsserts() : null;

        $expected = $responseFactory($this);
        $expected->assert($response);
    }

    public static function storeUserDataProvider()
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
                'password' => [
                    'The Password field is required.',
                ],
                'type' => [
                    'The Type field is required.',
                ],
            ]);
        };

        $userCanNotBeStoredWithInvaildType = function (): ValidationResponse {
            return new ValidationResponse(422, [
                'type' => [
                    'The selected Type is invalid.',
                ],
            ]);
        };

        return [
            'User stored successfully' => [
                [
                    'name' => 'Second Name',
                    'email' => 'second@gmail.com',
                    'phone_number' => '(+90) 2347824569',
                    'status' => 'ACTIVE',
                    'type' => 'admin',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                    'created_at' => '2024-04-20 15:20:00',
                ],
                $userStoredSuccessfullyResponse,
                function () {
                    //assert equals 2 because there is one created above in testStoreUser
                    assertEquals(2, User::count());
                },
            ],
            'User can not be stored with missing required data' => [
                [
                ],
                $userCanNotBeStoredWithMissingRequiredData,
                function () {
                    //assert equals 1 because there is one created above in testStoreUser
                    assertEquals(1, User::count());
                },
            ],
            'User can not be stored with invaild type' => [
                [
                    'id' => 3,
                    'name' => 'Second Name',
                    'email' => 'second@gmail.com',
                    'phone_number' => '(+90) 2347824569',
                    'type' => 'seller',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                    'status' => 'PENDING',
                ],
                $userCanNotBeStoredWithInvaildType,
                function () {
                    //assert equals 1 because there is one created above in testStoreUser
                    assertEquals(1, User::count());
                },
            ],

        ];
    }
}
