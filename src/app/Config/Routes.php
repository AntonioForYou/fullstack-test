<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Авторизация и регистрация (доступны только гостям)
$routes->get('login', 'AuthController::login', ['filter' => 'guest']);
$routes->post('login', 'AuthController::attemptLogin', ['filter' => 'guest']);
$routes->get('register', 'AuthController::register', ['filter' => 'guest']);
$routes->post('register', 'AuthController::attemptRegister', ['filter' => 'guest']);
$routes->get('logout', 'AuthController::logout');
$routes->post('logout', 'AuthController::logout');

// Основной сервис комментариев (требует авторизации)
$routes->get('/', 'CommentController::index', ['filter' => 'auth']);
$routes->get('comments', 'CommentController::list', ['filter' => 'auth']);
$routes->post('comments', 'CommentController::create', ['filter' => 'auth']);
$routes->delete('comments/(:num)', 'CommentController::delete/$1', ['filter' => 'auth']);
$routes->post('comments/delete/(:num)', 'CommentController::delete/$1', ['filter' => 'auth']);
