<?php
namespace App\Controllers;

class Controller
{
    public function __invoke() {
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