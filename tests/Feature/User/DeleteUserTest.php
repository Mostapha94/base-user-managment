<?php

namespace Tests\Feature\User;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function PHPUnit\Framework\assertEquals;
use Tests\ExpectedResponse\ResourceNotFoundResponse;
use Tests\ExpectedResponse\SuccessResponse;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider deleteUserDataProvider */
    public function testDeleteUser(int $id, Closure $responseFactory, Closure $preDefinedFactory, Closure $extraAsserts)
    {
        $preDefinedFactory();

        $url = route('users.destroy', $id);

        $user = User::factory()->createQuietly();

        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user);

        $response = $this->json('DELETE', $url);

        $extraAsserts();

        $expected = $responseFactory($this);

        $expected->assert($response);
    }

    public static function deleteUserDataProvider()
    {
        $userStoredSuccessfullyResponse = function (): SuccessResponse {
            return new SuccessResponse();
        };

        $userCanNotBeDeletedWithInvalidIDGivenResponse = function (): ResourceNotFoundResponse {
            return new ResourceNotFoundResponse();
        };

        return [
            'User deleted successfully' => [
                2,
                $userStoredSuccessfullyResponse,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                    ]);
                },
                function () {
                    //assert equals 1 because there is one created above in testDeleteUser
                    assertEquals(1, User::count());
                },
            ],
            'User can not be deleted with invalid ID given' => [
                5,
                $userCanNotBeDeletedWithInvalidIDGivenResponse,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                    ]);
                },
                function () {
                    //assert equals 2 because there is one created above in testDeleteUser
                    assertEquals(2, User::count());
                },
            ],
        ];
    }
}
