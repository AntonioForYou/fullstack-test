<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Автоматическая самодиагностика: если таблицы еще не созданы в БД, накатываем миграции и сиды
        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('users') || ! $db->tableExists('comments')) {
                \Config\Services::migrations()->latest();
                \Config\Database::seeder()->call('App\Database\Seeds\UserSeeder');
                \Config\Database::seeder()->call('App\Database\Seeds\CommentSeeder');
            }
        } catch (\Throwable) {
            // Игнорируем ошибки подключения при ранней инициализации
        }
    }
}
