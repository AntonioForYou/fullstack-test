<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система комментариев</title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= time() ?>">
</head>
<body class="bg-light">

    <!-- Навбар -->
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1 font-weight-bold">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2" style="vertical-align: -3px;">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                Система комментариев
            </span>
            <div class="d-flex align-items-center">
                <span class="text-white-50 small mr-3 d-none d-sm-inline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1" style="vertical-align: -2px;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <?= esc($currentUserEmail ?? '') ?>
                </span>
                <a href="/logout" class="btn btn-outline-light btn-sm font-weight-bold">
                    Выйти
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-10">

                <!-- Панель управления и сортировки -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    Список комментариев
                                    <span id="total-badge" class="badge badge-pill badge-primary ml-2">...</span>
                                </h5>
                            </div>
                            <div class="col-sm-6">
                                <div class="sorting-controls">
                                    <div class="form-group mb-2">
                                        <label for="sort-by" class="small font-weight-bold text-muted mb-1 d-block">Сортировать по:</label>
                                        <select id="sort-by" class="form-control form-control-sm shadow-none">
                                            <option value="id" selected>По ID</option>
                                            <option value="date">По дате добавления</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="sort-order" class="small font-weight-bold text-muted mb-1 d-block">Направление:</label>
                                        <select id="sort-order" class="form-control form-control-sm shadow-none">
                                            <option value="desc" selected>По убыванию &darr;</option>
                                            <option value="asc">По возрастанию &uarr;</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Контейнер уведомлений списка -->
                <div id="list-alert"></div>

                <!-- Контейнер для списка комментариев -->
                <div id="comments-container" class="position-relative">
                    <div id="comments-loader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка...</span>
                        </div>
                        <p class="text-muted mt-2">Загрузка комментариев...</p>
                    </div>
                    <div id="comments-list"></div>
                </div>

                <!-- Панель пагинации и выбора кол-ва комментариев (рядом с номерами страниц под комментариями) -->
                <div id="pagination-section" class="mt-4 mb-5 d-flex flex-wrap justify-content-center align-items-center">
                    <nav id="pagination-wrapper" aria-label="Навигация по комментариям" class="mr-sm-4 mb-2 mb-sm-0">
                        <ul class="pagination mb-0" id="pagination-list"></ul>
                    </nav>

                    <div class="d-flex align-items-center mb-2 mb-sm-0" id="per-page-wrapper">
                        <label for="per-page" class="small font-weight-bold text-muted mb-0 mr-2 text-nowrap">
                            Показывать по:
                        </label>
                        <select id="per-page" class="form-control form-control-sm shadow-none" style="width: auto; min-width: 75px;">
                            <option value="3" selected>3</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                </div>

                <!-- Разделитель -->
                <hr class="my-5">

                <!-- Форма добавления комментариев (располагается ПОД списком) -->
                <div class="card shadow-sm border-0" id="add-comment-card">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title font-weight-bold text-dark mb-1">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2 text-primary" style="vertical-align: -3px;">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Добавить новый комментарий
                        </h4>
                        <p class="text-muted small mb-0">Заполните поля ниже, чтобы оставить свой комментарий</p>
                    </div>

                    <div class="card-body pt-3">
                        <form id="comment-form" action="/comments" method="POST" novalidate>
                            
                            <!-- Автор комментария (почта берется из текущей сессии) -->
                            <div class="alert alert-light border small text-muted mb-3 py-2 px-3 d-flex align-items-center">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2 text-primary">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>Вы авторизованы как: <strong class="text-dark"><?= esc($currentUserEmail ?? '') ?></strong></span>
                            </div>

                            <!-- Дата создания комментария (выбирается создателем) -->
                            <div class="form-group">
                                <label for="comment-date" class="font-weight-bold text-secondary">
                                    Дата создания <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white text-muted">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                        </span>
                                    </div>
                                    <input type="date" 
                                           class="form-control shadow-none" 
                                           id="comment-date" 
                                           name="date" 
                                           required>
                                    <div class="invalid-feedback" id="date-error">
                                        Пожалуйста, укажите дату.
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Дата создания комментария в строковом формате (выбирается автором).
                                </small>
                            </div>

                            <!-- Текст комментария -->
                            <div class="form-group">
                                <label for="comment-text" class="font-weight-bold text-secondary">
                                    Текст комментария <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control shadow-none" 
                                          id="comment-text" 
                                          name="text" 
                                          rows="4" 
                                          placeholder="Напишите текст вашего комментария..." 
                                          required></textarea>
                                <div class="invalid-feedback" id="text-error">
                                    Пожалуйста, напишите текст комментария.
                                </div>
                            </div>

                            <!-- Сообщение формы -->
                            <div id="form-alert" class="d-none"></div>

                            <!-- Кнопка отправки -->
                            <div class="text-right">
                                <button type="button" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="submit-btn">
                                    <span class="spinner-border spinner-border-sm mr-2 d-none" id="submit-spinner" role="status" aria-hidden="true"></span>
                                    <span>Опубликовать комментарий</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-danger" id="deleteModalTitle">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1" style="vertical-align: -3px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        Удаление комментария
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-0 text-muted">Вы уверены, что хотите удалить комментарий <strong id="delete-comment-id-text"></strong>? Это действие нельзя отменить.</p>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger font-weight-bold" id="confirm-delete-btn">
                        <span class="spinner-border spinner-border-sm mr-1 d-none" id="delete-spinner"></span>
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery 3 and Bootstrap 4 JS -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Application JS -->
    <script src="/assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
