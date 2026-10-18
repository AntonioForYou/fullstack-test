<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $table = $this->db->table('comments');
        $table->truncate();

        $data = [
            [
                'name'       => 'alexander.morozov@example.com',
                'text'       => 'Отличная статья, очень подробно и доступно всё описано. Спасибо автору за полезный материал!',
                'date'       => '2026-10-01',
                'created_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
            ],
            [
                'name'       => 'elena.smirnova@sample.net',
                'text'       => 'Пользуюсь подобным подходом уже полгода в своих проектах, полёт отличный. Главное — внимательно следить за структурой данных.',
                'date'       => '2026-10-05',
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'name'       => 'dmitry.kuznetsov@company.org',
                'text'       => 'Интересный взгляд на тему. Хотелось бы ещё увидеть продолжение с разбором более сложных практических сценариев.',
                'date'       => '2026-10-08',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'name'       => 'maria.ivanova@example.net',
                'text'       => 'Полностью согласна с выводами. Особенно порадовало внимание к деталям и понятные примеры в тексте.',
                'date'       => '2026-10-10',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'name'       => 'sergey.volkov@sample.org',
                'text'       => 'Подскажите, пожалуйста, а планируется ли отдельный материал по оптимизации производительности под высокими нагрузками?',
                'date'       => '2026-10-12',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'name'       => 'anna.popova@example.org',
                'text'       => 'Очень вовремя попалась эта публикация! Как раз выбирали подходящее архитектурное решение для нового рабочего проекта.',
                'date'       => '2026-10-14',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'name'       => 'maxim.fedorov@sample.com',
                'text'       => 'Прекрасный обзор! Сохранил себе в закладки, обязательно порекомендую коллегам по команде для ознакомления.',
                'date'       => '2026-10-15',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $table->insertBatch($data);
    }
}
