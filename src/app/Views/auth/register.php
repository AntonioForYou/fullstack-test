<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Система комментариев</title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="col-md-6 col-lg-5">

                <div class="text-center mb-4">
                    <div class="d-inline-flex p-3 rounded-circle bg-success text-white shadow-sm mb-3">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                    </div>
                    <h3 class="font-weight-bold text-dark">Создание аккаунта</h3>
                    <p class="text-muted small">Заполните поля для регистрации в сервисе</p>
                </div>

                <div class="card shadow-sm border-0 rounded-lg">
                    <div class="card-body p-4">

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= esc(session()->getFlashdata('error')) ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                        <form action="/register" method="post" novalidate>
                            <?= csrf_field() ?>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="font-weight-bold text-secondary small">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white text-muted">@</span>
                                    </div>
                                    <input type="email" 
                                           class="form-control shadow-none <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           id="email" 
                                           name="email" 
                                           value="<?= esc(old('email') ?? '') ?>" 
                                           placeholder="user@example.com" 
                                           required 
                                           autofocus 
                                           autocomplete="email">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?= esc($errors['email']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Пароль -->
                            <div class="form-group">
                                <label for="password" class="font-weight-bold text-secondary small">Пароль <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white text-muted">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <input type="password" 
                                           class="form-control shadow-none <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Придумайте надежный пароль" 
                                           required 
                                           autocomplete="new-password">
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?= esc($errors['password']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted">
                                    Не менее 8 символов, должен содержать строчные и заглавные латинские буквы, цифры и спец. символы.
                                </small>
                            </div>

                            <!-- Подтверждение пароля -->
                            <div class="form-group">
                                <label for="password_confirm" class="font-weight-bold text-secondary small">Повторите пароль <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white text-muted">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <input type="password" 
                                           class="form-control shadow-none <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                                           id="password_confirm" 
                                           name="password_confirm" 
                                           placeholder="Повторите пароль еще раз" 
                                           required 
                                           autocomplete="new-password">
                                    <?php if (isset($errors['password_confirm'])): ?>
                                        <div class="invalid-feedback">
                                            <?= esc($errors['password_confirm']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 mt-4 shadow-sm">
                                Зарегистрироваться
                            </button>
                        </form>

                    </div>
                    <div class="card-footer bg-white border-top-0 py-3 text-center">
                        <span class="text-muted small">Уже зарегистрированы?</span>
                        <a href="/login" class="font-weight-bold small ml-1">Войти в аккаунт</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- jQuery 3 and Bootstrap 4 JS -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
