<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CommentModel;
use CodeIgniter\HTTP\ResponseInterface;

class CommentController extends BaseController
{
    protected CommentModel $commentModel;

    public function __construct()
    {
        $this->commentModel = model(CommentModel::class);
    }

    /**
     * Отображение главной страницы с комментариями
     */
    public function index(): string
    {
        $currentUserEmail = (string) session()->get('user_email');

        return view('comments/index', [
            'currentUserEmail' => $currentUserEmail,
        ]);
    }

    /**
     * AJAX-получение списка комментариев с пагинацией и сортировкой
     */
    public function list(): ResponseInterface
    {
        $allowedPerPage = [3, 5, 10];
        $perPage = (int) $this->request->getGet('per_page');
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 3;
        }

        // Параметры сортировки
        $allowedSortBy = ['id', 'date'];
        $sortBy = (string) $this->request->getGet('sort_by');
        if (! in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'id';
        }

        $sortOrder = strtolower((string) $this->request->getGet('sort_order'));
        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        // Общее количество записей
        $totalItems = $this->commentModel->countAllResults(false);
        $totalPages = (int) max(1, (int) ceil($totalItems / $perPage));

        // Текущая страница
        $page = (int) $this->request->getGet('page');
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages && $totalItems > 0) {
            $page = $totalPages;
        }

        // Получение комментариев
        $items = $this->commentModel
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage, 'default', $page);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'items'      => $items,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages'  => $totalPages,
                    'totalItems'  => $totalItems,
                    'perPage'     => $perPage,
                ],
                'sort' => [
                    'sortBy'    => $sortBy,
                    'sortOrder' => $sortOrder,
                ],
            ],
        ]);
    }

    /**
     * AJAX-создание нового комментария
     * Email берется автоматически из сессии авторизованного пользователя
     */
    public function create(): ResponseInterface
    {
        $userEmail = (string) session()->get('user_email');
        if ($userEmail === '') {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Необходима авторизация для добавления комментария.',
            ]);
        }

        $contentType = $this->request->header('Content-Type')?->getValue() ?? '';
        if (str_contains($contentType, 'application/json')) {
            /** @var array<string, mixed> $data */
            $data = $this->request->getJSON(true) ?? [];
        } else {
            /** @var array<string, mixed> $data */
            $data = $this->request->getPost() ?? [];
        }

        $saveData = [
            'name' => $userEmail,
            'text' => isset($data['text']) ? trim((string) $data['text']) : '',
            'date' => isset($data['date']) ? trim((string) $data['date']) : '',
        ];

        if (! $this->commentModel->validate($saveData)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Пожалуйста, проверьте корректность заполнения полей.',
                'errors'  => $this->commentModel->errors(),
            ]);
        }

        $insertId = $this->commentModel->insert($saveData);
        if (! $insertId) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Не удалось сохранить комментарий. Попробуйте позже.',
            ]);
        }

        $createdComment = $this->commentModel->find($insertId);

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Комментарий успешно добавлен!',
            'data'    => $createdComment,
        ]);
    }

    /**
     * AJAX-удаление комментария
     */
    public function delete(int|string|null $id = null): ResponseInterface
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Некорректный идентификатор комментария.',
            ]);
        }

        $comment = $this->commentModel->find($id);
        if (! $comment) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Комментарий не найден или уже был удален.',
            ]);
        }

        $deleted = $this->commentModel->delete($id);
        if (! $deleted) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Не удалось удалить комментарий.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Комментарий успешно удален.',
        ]);
    }
}
