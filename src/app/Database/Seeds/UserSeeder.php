<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public const DEMO_EMAIL = 'user@example.com';
    public const DEMO_PASSWORD = 'User#Pass2026!';

    public function run(): void
    {
        $table = $this->db->table('users');

        // Проверяем, существует ли демонстрационный пользователь, и обновляем его пароль
        $existing = $table->where('email', self::DEMO_EMAIL)->get()->getRowArray();
        if ($existing === null) {
            $table->insert([
                'email'      => self::DEMO_EMAIL,
                'password'   => password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $table->where('email', self::DEMO_EMAIL)->update([
                'password'   => password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
