<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CommentModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CommentModelTest extends CIUnitTestCase
{
    protected CommentModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CommentModel();
    }

    public function testValidationPassesWithValidData(): void
    {
        $data = [
            'name' => 'test.user@domain.com',
            'text' => 'This is a valid test comment.',
            'date' => '2026-10-15',
        ];

        $this->assertTrue($this->model->validate($data));
    }

    public function testValidationFailsWithInvalidEmail(): void
    {
        $data = [
            'name' => 'invalid-email-address',
            'text' => 'Some text',
            'date' => '2026-10-15',
        ];

        $this->assertFalse($this->model->validate($data));
        $errors = $this->model->errors();
        $this->assertArrayHasKey('name', $errors);
    }

    public function testValidationFailsWithEmptyText(): void
    {
        $data = [
            'name' => 'user@example.com',
            'text' => '',
            'date' => '2026-10-15',
        ];

        $this->assertFalse($this->model->validate($data));
        $errors = $this->model->errors();
        $this->assertArrayHasKey('text', $errors);
    }

    public function testValidationFailsWithInvalidDate(): void
    {
        $data = [
            'name' => 'user@example.com',
            'text' => 'Valid text',
            'date' => 'not-a-date',
        ];

        $this->assertFalse($this->model->validate($data));
        $errors = $this->model->errors();
        $this->assertArrayHasKey('date', $errors);
    }
}
