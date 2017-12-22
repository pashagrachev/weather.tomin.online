<?php
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

class HomeController
{
    public function __invoke(Request $request, Response $response) {
        $event = json_decode(file_get_contents('php://input'), true);

        switch ($event['type']) {
            case 'confirmation':
                echo getenv('VK_API_CONFIRMATION_TOKEN');
                break;
            case 'message_new':
                echo('ok');
                break;
            default:
                echo('Unsupported event');
                break;
        }
    }
}