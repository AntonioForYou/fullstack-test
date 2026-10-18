<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Database\Seeds\CommentSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class CommentControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace = 'App';
    protected $seed = CommentSeeder::class;

    /**
     * @var array<string, mixed>
     */
    protected array $authSession = [
        'is_logged_in' => true,
        'user_id'      => 1,
        'user_email'   => 'authorized.user@example.com',
    ];

    public function testGuestRedirectedToLogin(): void
    {
        $result = $this->get('/');

        $result->assertRedirectTo('/login');
    }

    public function testIndexReturnsOkForAuthorizedUser(): void
    {
        $result = $this->withSession($this->authSession)->get('/');

        $result->assertStatus(200);
        $result->assertSee('Система комментариев');
        $result->assertSee('authorized.user@example.com');
    }

    public function testListReturnsJsonWithComments(): void
    {
        $result = $this->withSession($this->authSession)->get('comments?page=1&sort_by=id&sort_order=desc');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
    }

    public function testListWithPerPageSelector(): void
    {
        // Проверка выбора 5 на страницу
        $result5 = $this->withSession($this->authSession)->get('comments?page=1&per_page=5');
        $result5->assertStatus(200);
        $result5->assertJSONFragment([
            'data' => [
                'pagination' => [
                    'perPage' => 5,
                ],
            ],
        ]);

        // Проверка выбора 10 на страницу
        $result10 = $this->withSession($this->authSession)->get('comments?page=1&per_page=10');
        $result10->assertStatus(200);
        $result10->assertJSONFragment([
            'data' => [
                'pagination' => [
                    'perPage' => 10,
                ],
            ],
        ]);

        // Некорректное значение должно сбрасываться на 3 по умолчанию
        $resultInvalid = $this->withSession($this->authSession)->get('comments?page=1&per_page=99');
        $resultInvalid->assertStatus(200);
        $resultInvalid->assertJSONFragment([
            'data' => [
                'pagination' => [
                    'perPage' => 3,
                ],
            ],
        ]);
    }

    public function testCreateRequiresTextAndDate(): void
    {
        $result = $this->withSession($this->authSession)->post('comments', [
            'text' => '',
            'date' => 'not-a-date',
        ]);

        $result->assertStatus(422);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testCreateSuccessfullyWithUserEmailFromSession(): void
    {
        $result = $this->withSession($this->authSession)->post('comments', [
            'text' => 'Valid test comment from session user',
            'date' => '2026-10-17',
        ]);

        $result->assertStatus(201);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment([
            'data' => [
                'name' => 'authorized.user@example.com',
            ],
        ]);
    }

    public function testDeleteNonExistentCommentReturns404(): void
    {
        $result = $this->withSession($this->authSession)->post('comments/delete/999999');

        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }
}
