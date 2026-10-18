<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'comments';

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
        'name',
        'text',
        'date',
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
        'name' => [
            'label'  => 'Email',
            'rules'  => 'required|valid_email|max_length[255]',
            'errors' => [
                'required'    => 'Поле "Почта" обязательно для заполнения.',
                'valid_email' => 'Пожалуйста, укажите корректный адрес электронной почты.',
                'max_length'  => 'Длина email не должна превышать 255 символов.',
            ],
        ],
        'text' => [
            'label'  => 'Текст комментария',
            'rules'  => 'required|min_length[2]|max_length[5000]',
            'errors' => [
                'required'   => 'Поле "Текст комментария" обязательно для заполнения.',
                'min_length' => 'Текст комментария должен содержать минимум 2 символа.',
                'max_length' => 'Текст комментария не должен превышать 5000 символов.',
            ],
        ],
        'date' => [
            'label'  => 'Дата создания',
            'rules'  => 'required|valid_date[Y-m-d]',
            'errors' => [
                'required'   => 'Поле "Дата создания" обязательно для заполнения.',
                'valid_date' => 'Укажите дату в формате ГГГГ-ММ-ДД.',
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
}
