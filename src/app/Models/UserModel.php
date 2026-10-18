<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    protected $useAutoIncrement = true;

    /**
     * @var string
     */
    protected $returnType = 'array';

    /**
     * @var bool
     */
    protected $useSoftDeletes = false;

    /**
     * @var bool
     */
    protected $protectFields = true;

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'email',
        'password',
    ];

    // Dates
    /**
     * @var bool
     */
    protected $useTimestamps = true;

    /**
     * @var string
     */
    protected $dateFormat = 'datetime';

    /**
     * @var string
     */
    protected $createdField = 'created_at';

    /**
     * @var string
     */
    protected $updatedField = 'updated_at';

    // Validation
    /**
     * @var array<string, array<string, mixed>>
     */
    protected $validationRules = [
        'email' => [
            'label'  => 'Email',
            'rules'  => 'required|valid_email|is_unique[users.email,id,{id}]',
            'errors' => [
                'required'    => 'Поле "Email" обязательно для заполнения.',
                'valid_email' => 'Пожалуйста, укажите корректный адрес электронной почты.',
                'is_unique'   => 'Пользователь с таким email уже зарегистрирован.',
            ],
        ],
        'password' => [
            'label'  => 'Пароль',
            'rules'  => 'required|min_length[8]',
            'errors' => [
                'required'   => 'Поле "Пароль" обязательно для заполнения.',
                'min_length' => 'Пароль должен содержать минимум 8 символов.',
            ],
        ],
    ];

    /**
     * @var bool
     */
    protected $skipValidation = false;

    /**
     * @var bool
     */
    protected $cleanValidationRules = true;

    /**
     * Поиск пользователя по email
     *
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        /** @var array<string, mixed>|null $user */
        $user = $this->where('email', $email)->first();

        return $user;
    }

    /**
     * Создание пользователя с хэшированием пароля
     */
    public function registerUser(string $email, string $plainPassword): int|false
    {
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $data = [
            'email'    => $email,
            'password' => $hashedPassword,
        ];

        return $this->skipValidation(true)->insert($data);
    }

    /**
     * Проверка пароля пользователя
     */
    public function verifyPassword(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }
}
