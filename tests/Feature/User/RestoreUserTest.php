<?php

namespace Tests\Feature\User;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function PHPUnit\Framework\assertEquals;
use Tests\ExpectedResponse\ResourceNotFoundResponse;
use Tests\ExpectedResponse\SuccessResponse;
use Tests\TestCase;

class RestoreUserTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider restoreUserDataProvider */
    public function testRestoreUser(int $id, Closure $responseFactory, Closure $preDefinedFactory, Closure $extraAsserts)
    {
        $preDefinedFactory();

        $url = route('users.restore', $id);

        $user = User::factory()->createQuietly();

        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user);

        $response = $this->json('POST', $url);

        $extraAsserts();

        $expected = $responseFactory($this);

        $expected->assert($response);
    }

    public static function restoreUserDataProvider()
    {
        $userRestoredSuccessfullyResponse = function (): SuccessResponse {
            return new SuccessResponse();
        };

        $userCanNotBeRestoredWithInvalidIDGivenResponse = function (): ResourceNotFoundResponse {
            return new ResourceNotFoundResponse();
        };

        return [
            'User restored successfully' => [
                2,
                $userRestoredSuccessfullyResponse,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-04-20 15:20:00',
                        'deleted_at' => '2024-04-21 15:20:00',
                    ]);
                },
                function () {
                    //assert equals 2 because there is one created above in testRestoreUser
                    assertEquals(2, User::count());
                },
            ],
            'User can not be deleted with invalid ID given' => [
                5,
                $userCanNotBeRestoredWithInvalidIDGivenResponse,
                function () {
                    User::factory()->create([
                        'id' => 2,
                        'name' => 'First Name',
                        'email' => 'first@gmail.com',
                        'phone_number' => '(+90) 1235647895611',
                        'status' => 'PENDING',
                        'created_at' => '2024-04-20 15:20:00',
                        'deleted_at' => '2024-04-21 15:20:00',
                    ]);
                },
                function () {
                    //assert equals 1 because there is one created above in testRestoreUser
                    assertEquals(1, User::count());
                },
            ],
        ];
    }
}
