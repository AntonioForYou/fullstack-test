<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
    }

    /**
     * Страница авторизации (email и пароль)
     */
    public function login(): string|ResponseInterface
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/login');
    }

    /**
     * Обработка входа пользователя
     */
    public function attemptLogin(): ResponseInterface
    {
        $rules = [
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email',
                'errors' => [
                    'required'    => 'Поле "Email" обязательно для заполнения.',
                    'valid_email' => 'Пожалуйста, укажите корректный email.',
                ],
            ],
            'password' => [
                'label'  => 'Пароль',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Поле "Пароль" обязательно для заполнения.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $user = $this->userModel->findByEmail($email);

        if ($user === null || ! $this->userModel->verifyPassword($password, (string) $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Неверный адрес электронной почты или пароль.');
        }

        // Авторизуем пользователя в сессии
        session()->set([
            'user_id'      => (int) $user['id'],
            'user_email'   => (string) $user['email'],
            'is_logged_in' => true,
        ]);

        return redirect()->to('/');
    }

    /**
     * Страница регистрации
     */
    public function register(): string|ResponseInterface
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/register');
    }

    /**
     * Обработка регистрации пользователя
     */
    public function attemptRegister(): ResponseInterface
    {
        $rules = [
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required'    => 'Поле "Email" обязательно для заполнения.',
                    'valid_email' => 'Пожалуйста, укажите корректный адрес электронной почты.',
                    'is_unique'   => 'Пользователь с таким email уже зарегистрирован.',
                ],
            ],
            'password' => [
                'label'  => 'Пароль',
                'rules'  => [
                    'required',
                    'min_length[8]',
                    'regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9])/]',
                ],
                'errors' => [
                    'required'    => 'Поле "Пароль" обязательно для заполнения.',
                    'min_length'  => 'Пароль должен содержать не менее 8 символов.',
                    'regex_match' => 'Пароль должен содержать строчные и заглавные латинские буквы, цифры и спец. символы.',
                ],
            ],
            'password_confirm' => [
                'label'  => 'Подтверждение пароля',
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Пожалуйста, подтвердите пароль.',
                    'matches'  => 'Введенные пароли не совпадают.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $userId = $this->userModel->registerUser($email, $password);

        if ($userId === false) {
            return redirect()->back()->withInput()->with('error', 'Не удалось создать аккаунт. Попробуйте позже.');
        }

        // После успешной регистрации сразу авторизуем и перебрасываем на страницу с комментариями
        session()->set([
            'user_id'      => $userId,
            'user_email'   => $email,
            'is_logged_in' => true,
        ]);

        return redirect()->to('/');
    }

    /**
     * Выход из системы
     */
    public function logout(): ResponseInterface
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
