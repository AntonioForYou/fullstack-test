<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Database\Seeds\UserSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AuthControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace = 'App';
    protected $seed = UserSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();
        unset($_SESSION['is_logged_in'], $_SESSION['user_id'], $_SESSION['user_email']);
    }

    public function testLoginPageAccessibleByGuest(): void
    {
        $result = $this->get('login');

        $result->assertStatus(200);
        $result->assertSee('Вход в систему');
    }

    public function testRegisterPageAccessibleByGuest(): void
    {
        $result = $this->get('register');

        $result->assertStatus(200);
        $result->assertSee('Создание аккаунта');
    }

    public function testRegisterSuccessfullyAndRedirectsToHome(): void
    {
        $result = $this->post('register', [
            'email'            => 'new.person@example.com',
            'password'         => 'Secure#Pass2026!',
            'password_confirm' => 'Secure#Pass2026!',
        ]);

        $result->assertRedirectTo('/');
        $this->assertTrue(session()->get('is_logged_in'));
        $this->assertSame('new.person@example.com', session()->get('user_email'));
    }

    public function testRegisterFailsWithInsecurePassword(): void
    {
        $result = $this->post('register', [
            'email'            => 'invalid.password@example.com',
            'password'         => 'simplepassword',
            'password_confirm' => 'simplepassword',
        ]);

        $result->assertSessionHas('errors');
        $errors = session()->getFlashdata('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertSame('Пароль должен содержать строчные и заглавные латинские буквы, цифры и спец. символы.', $errors['password']);
        $this->assertNull(session()->get('is_logged_in'));
    }

    public function testRegisterFailsWhenPasswordLacksDigits(): void
    {
        $result = $this->post('register', [
            'email'            => 'nodigits.password@example.com',
            'password'         => 'Secure#Password',
            'password_confirm' => 'Secure#Password',
        ]);

        $result->assertSessionHas('errors');
        $errors = session()->getFlashdata('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertSame('Пароль должен содержать строчные и заглавные латинские буквы, цифры и спец. символы.', $errors['password']);
        $this->assertNull(session()->get('is_logged_in'));
    }

    public function testRegisterFailsWithShortPassword(): void
    {
        $result = $this->post('register', [
            'email'            => 'short.password@example.com',
            'password'         => 'Ab#1',
            'password_confirm' => 'Ab#1',
        ]);

        $result->assertSessionHas('errors');
        $errors = session()->getFlashdata('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertSame('Пароль должен содержать не менее 8 символов.', $errors['password']);
        $this->assertNull(session()->get('is_logged_in'));
    }

    public function testLoginWithInvalidCredentialsFails(): void
    {
        $result = $this->post('login', [
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $result->assertSessionHas('error');
        $this->assertNull(session()->get('is_logged_in'));
    }

    public function testLoginSuccessfullyAndRedirectsToHome(): void
    {
        $result = $this->post('login', [
            'email'    => 'user@example.com',
            'password' => 'User#Pass2026!',
        ]);

        $result->assertRedirectTo('/');
        $this->assertTrue(session()->get('is_logged_in'));
        $this->assertSame('user@example.com', session()->get('user_email'));
    }

    public function testLogoutClearsSessionAndRedirects(): void
    {
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id']      = 1;
        $_SESSION['user_email']   = 'user@example.com';

        $result = $this->get('logout');

        $result->assertRedirectTo('/login');
        $this->assertNull(session()->get('is_logged_in'));
    }
}
