/**
 * Система комментариев - логика на jQuery 3
 */
$(document).ready(function () {
    'use strict';

    // Состояние приложения
    let state = {
        currentPage: 1,
        sortBy: 'id',
        sortOrder: 'desc',
        perPage: 3,
        totalPages: 1,
        totalItems: 0,
        commentToDeleteId: null
    };

    // Инициализация даты по умолчанию (сегодня)
    function setTodayDate() {
        const today = new Date().toISOString().split('T')[0];
        $('#comment-date').val(today);
    }
    setTodayDate();

    // ------------------------------------------------------------------------
    // Валидация полей формы (проверяется только при нажатии кнопки отправки)
    // ------------------------------------------------------------------------
    function validateTextInput() {
        const $input = $('#comment-text');
        const $error = $('#text-error');
        const value = $.trim($input.val());

        if (value === '') {
            $input.removeClass('is-valid').addClass('is-invalid');
            $error.text('Поле "Текст комментария" обязательно для заполнения.');
            return false;
        }

        if (value.length < 2) {
            $input.removeClass('is-valid').addClass('is-invalid');
            $error.text('Комментарий должен содержать не менее 2 символов.');
            return false;
        }

        $input.removeClass('is-invalid');
        return true;
    }

    function validateDateInput() {
        const $input = $('#comment-date');
        const $error = $('#date-error');
        const value = $.trim($input.val());

        if (value === '') {
            $input.removeClass('is-valid').addClass('is-invalid');
            $error.text('Пожалуйста, выберите дату создания комментария.');
            return false;
        }

        $input.removeClass('is-invalid');
        return true;
    }

    // ------------------------------------------------------------------------
    // Экранирование HTML для предотвращения XSS
    // ------------------------------------------------------------------------
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Получение первой буквы email для аватара
    function getAvatarLetter(email) {
        if (!email) return '?';
        return email.charAt(0).toUpperCase();
    }

    // Форматирование даты для вывода (ДД.ММ.ГГГГ)
    function formatDateDisplay(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}.${parts[1]}.${parts[0]}`;
        }
        return dateStr;
    }

    // ------------------------------------------------------------------------
    // Рендеринг карточки комментария
    // ------------------------------------------------------------------------
    function renderCommentCard(comment) {
        const id = escapeHtml(comment.id);
        const name = escapeHtml(comment.name);
        const text = escapeHtml(comment.text);
        const dateRaw = escapeHtml(comment.date);
        const dateFormatted = formatDateDisplay(dateRaw);
        const letter = getAvatarLetter(comment.name);

        return `
            <div class="card shadow-sm border-0 mb-3 comment-card" id="comment-card-${id}">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="comment-avatar mr-3">${letter}</div>
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark">${name}</h6>
                            <span class="text-muted comment-meta">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1" style="vertical-align: -1px;">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                ${dateFormatted}
                                <span class="badge badge-light border text-muted ml-2">#${id}</span>
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn" data-id="${id}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1" style="vertical-align: -2px;">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Удалить
                    </button>
                </div>
                <div class="card-body pt-1 pb-3">
                    <p class="comment-text mb-0">${text}</p>
                </div>
            </div>
        `;
    }

    // ------------------------------------------------------------------------
    // Рендеринг пагинации (рядом с выбором количества комментариев)
    // ------------------------------------------------------------------------
    function renderPagination(pagination) {
        const $section = $('#pagination-section');
        const $wrapper = $('#pagination-wrapper');
        const $list = $('#pagination-list');
        $list.empty();

        const current = pagination.currentPage;
        const total = pagination.totalPages;
        const totalItems = pagination.totalItems;

        // Синхронизируем значение в селекте количества записей
        if (pagination.perPage) {
            $('#per-page').val(pagination.perPage);
        }

        if (totalItems === 0) {
            $section.addClass('d-none');
            return;
        }

        $section.removeClass('d-none');

        if (total <= 1) {
            // Если всего одна страница - отображаем номер 1 рядом с выбором количества
            $list.html(`
                <li class="page-item active disabled">
                    <span class="page-link">1</span>
                </li>
            `);
            $wrapper.removeClass('d-none');
            return;
        }

        $wrapper.removeClass('d-none');

        // Кнопка "Назад"
        const prevDisabled = current <= 1 ? 'disabled' : '';
        $list.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${current - 1}" aria-label="Предыдущая">&laquo;</a>
            </li>
        `);

        // Номера страниц с выбором конкретной
        for (let i = 1; i <= total; i++) {
            const active = i === current ? 'active' : '';
            $list.append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Кнопка "Вперед"
        const nextDisabled = current >= total ? 'disabled' : '';
        $list.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${current + 1}" aria-label="Следующая">&raquo;</a>
            </li>
        `);
    }

    // ------------------------------------------------------------------------
    // Загрузка комментариев через AJAX
    // ------------------------------------------------------------------------
    function loadComments(page = 1, scrollToComments = false) {
        state.currentPage = page;
        state.sortBy = $('#sort-by').val() || 'id';
        state.sortOrder = $('#sort-order').val() || 'desc';
        state.perPage = parseInt($('#per-page').val(), 10) || 3;

        $('#comments-loader').removeClass('d-none');
        $('#comments-list').css('opacity', 0.5);

        $.ajax({
            url: '/comments',
            method: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                page: state.currentPage,
                sort_by: state.sortBy,
                sort_order: state.sortOrder,
                per_page: state.perPage
            },
            success: function (response) {
                if (response.status === 'success') {
                    const data = response.data;
                    const items = data.items || [];
                    const pagination = data.pagination;

                    state.currentPage = pagination.currentPage;
                    state.totalPages = pagination.totalPages;
                    state.totalItems = pagination.totalItems;
                    state.perPage = pagination.perPage;

                    $('#total-badge').text(pagination.totalItems);

                    const $list = $('#comments-list');

                    let html = '';
                    if (items.length === 0) {
                        html = `
                            <div class="card shadow-sm border-0 py-5 text-center text-muted">
                                <div class="card-body">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 text-secondary">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <h5>Пока нет комментариев</h5>
                                    <p class="mb-0">Будьте первым, кто оставит комментарий через форму ниже!</p>
                                </div>
                            </div>
                        `;
                    } else {
                        items.forEach(function (comment) {
                            html += renderCommentCard(comment);
                        });
                    }

                    $list.html(html);

                    renderPagination(pagination);

                    if (scrollToComments) {
                        $('html, body').animate({
                            scrollTop: $('#comments-container').offset().top - 90
                        }, 300);
                    }
                }
            },
            error: function () {
                $('#list-alert').html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Ошибка!</strong> Не удалось загрузить список комментариев. Попробуйте обновить страницу.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `);
            },
            complete: function () {
                $('#comments-loader').addClass('d-none');
                $('#comments-list').css('opacity', 1);
            }
        });
    }

    // Первоначальная загрузка списка
    loadComments(1);

    // ------------------------------------------------------------------------
    // Обработка переключения пагинации
    // ------------------------------------------------------------------------
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const $parent = $(this).parent();
        if ($parent.hasClass('disabled') || $parent.hasClass('active')) {
            return;
        }
        const targetPage = parseInt($(this).data('page'), 10);
        if (targetPage > 0 && targetPage <= state.totalPages) {
            loadComments(targetPage, true);
        }
    });

    // ------------------------------------------------------------------------
    // Обработка изменения сортировки (по id / по дате, asc / desc)
    // ------------------------------------------------------------------------
    $('#sort-by, #sort-order').on('change', function () {
        loadComments(1, false);
    });

    // ------------------------------------------------------------------------
    // Обработка изменения количества комментариев на странице (3, 5, 10)
    // ------------------------------------------------------------------------
    $(document).on('change', '#per-page', function () {
        state.perPage = parseInt($(this).val(), 10) || 3;
        loadComments(1, false);
    });

    // ------------------------------------------------------------------------
    // Добавление комментария через AJAX
    // ------------------------------------------------------------------------
    function submitCommentForm() {
        // Сброс предыдущих сообщений и классов валидации
        $('#form-alert').stop(true, true).addClass('d-none').empty();
        $('#comment-text, #comment-date').removeClass('is-invalid is-valid');

        const isDateValid = validateDateInput();
        const isTextValid = validateTextInput();

        if (!isDateValid || !isTextValid) {
            $('#form-alert')
                .removeClass('d-none alert-success alert-danger')
                .addClass('alert alert-warning')
                .html('<strong>Внимание!</strong> Пожалуйста, заполните обязательные поля.');

            if (!isDateValid) {
                $('#comment-date').focus();
            } else {
                $('#comment-text').focus();
            }
            return;
        }

        const formData = {
            text: $.trim($('#comment-text').val()),
            date: $.trim($('#comment-date').val())
        };

        const $btn = $('#submit-btn');
        const $spinner = $('#submit-spinner');
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');

        $.ajax({
            url: '/comments',
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                if (response.status === 'success') {
                    // Уведомление об успешном добавлении
                    $('#form-alert')
                        .removeClass('d-none alert-danger alert-warning')
                        .addClass('alert alert-success')
                        .html(`<strong>Успешно!</strong> ${escapeHtml(response.message)}`);

                    // Очистка формы
                    $('#comment-text').val('').removeClass('is-valid is-invalid');
                    $('#comment-date').removeClass('is-valid is-invalid');
                    setTodayDate();

                    // Перезагружаем комментарии без скролл-прыжков
                    // Если сортировка по ID убывающая - открываем 1 страницу, чтобы сразу увидеть новый комментарий
                    const targetPage = (state.sortBy === 'id' && state.sortOrder === 'desc') ? 1 : state.currentPage;
                    loadComments(targetPage, false);

                    // Скрыть уведомление через 5 секунд
                    setTimeout(function () {
                        $('#form-alert').fadeOut(400, function () {
                            $(this).addClass('d-none').show().empty();
                        });
                    }, 5000);
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const response = xhr.responseJSON;
                if (xhr.status === 422 && response && response.errors) {
                    const errorMessages = [];
                    if (response.errors.text) {
                        $('#comment-text').removeClass('is-valid').addClass('is-invalid');
                        $('#text-error').text(response.errors.text);
                        errorMessages.push(escapeHtml(response.errors.text));
                    }
                    if (response.errors.date) {
                        $('#comment-date').removeClass('is-valid').addClass('is-invalid');
                        $('#date-error').text(response.errors.date);
                        errorMessages.push(escapeHtml(response.errors.date));
                    }
                    for (const [key, msg] of Object.entries(response.errors)) {
                        if (key !== 'text' && key !== 'date') {
                            errorMessages.push(escapeHtml(msg));
                        }
                    }
                    const text = errorMessages.length > 0
                        ? errorMessages.join('<br>')
                        : (response.message || 'Пожалуйста, проверьте корректность заполнения полей.');

                    $('#form-alert')
                        .removeClass('d-none alert-success alert-warning')
                        .addClass('alert alert-danger')
                        .html(`<strong>Ошибка валидации:</strong><br>${text}`);
                } else {
                    const message = (response && response.message)
                        ? response.message
                        : 'Произошла ошибка при отправке комментария. Попробуйте еще раз.';
                    $('#form-alert')
                        .removeClass('d-none alert-success alert-warning')
                        .addClass('alert alert-danger')
                        .html(`<strong>Ошибка!</strong> ${escapeHtml(message)}`);
                }
            },
            complete: function () {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
            }
        });
    }

    // Отправка формы по submit
    $('#comment-form').on('submit', function (e) {
        e.preventDefault();
        submitCommentForm();
    });

    // Обработка клика по кнопке отправки
    $(document).on('click', '#submit-btn', function (e) {
        e.preventDefault();
        submitCommentForm();
    });

    // ------------------------------------------------------------------------
    // Удаление комментария через AJAX
    // ------------------------------------------------------------------------
    $(document).on('click', '.delete-btn', function () {
        const commentId = $(this).data('id');
        state.commentToDeleteId = commentId;
        $('#delete-comment-id-text').text('#' + commentId);
        $('#deleteConfirmModal').modal('show');
    });

    $('#confirm-delete-btn').on('click', function () {
        const commentId = state.commentToDeleteId;
        if (!commentId) return;

        const $btn = $(this);
        const $spinner = $('#delete-spinner');
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');

        $.ajax({
            url: `/comments/delete/${commentId}`,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#deleteConfirmModal').modal('hide');

                if (response.status === 'success') {
                    // Анимация исчезновения карточки
                    const $card = $(`#comment-card-${commentId}`);
                    $card.addClass('fade-out');

                    setTimeout(function () {
                        // Оповещение об удалении
                        $('#list-alert').html(`
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Успешно!</strong> Комментарий #${commentId} был удален.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        `);

                        setTimeout(function () {
                            $('#list-alert .alert').alert('close');
                        }, 3000);

                        // Проверяем, был ли это последний элемент на странице
                        const remainingOnPage = $('#comments-list .comment-card').length - 1;
                        let targetPage = state.currentPage;
                        if (remainingOnPage <= 0 && state.currentPage > 1) {
                            targetPage = state.currentPage - 1;
                        }

                        loadComments(targetPage, false);
                    }, 300);
                }
            },
            error: function (xhr) {
                $('#deleteConfirmModal').modal('hide');
                if (xhr.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                const response = xhr.responseJSON;
                const message = (response && response.message) ? response.message : 'Не удалось удалить комментарий.';
                $('#list-alert').html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Ошибка!</strong> ${escapeHtml(message)}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `);
            },
            complete: function () {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                state.commentToDeleteId = null;
            }
        });
    });

});
