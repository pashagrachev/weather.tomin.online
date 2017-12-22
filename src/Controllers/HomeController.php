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
        $req = $request->getParsedBody();

        $logger = new Logger('bot');
        $logger->pushProcessor(new UidProcessor);
        $file_handler = new StreamHandler("../logs/bot.log");
        $logger->pushHandler($file_handler);

        if($req['group_id'] == getenv('VK_GROUP_ID')) {
            $logger->info('Group ID ' . $req['group_id'] . 'verified');

            if($req['secret'] == getenv('VK_SECRET_KEY')) {
                $logger->info('Secret key ' . $req['secret'] . 'verified');

                switch ($req['type']) {
                    case 'confirmation':
                        $logger->info('Confirmation token sent');
                        return $response->withStatus(200)->write(getenv('VK_API_CONFIRMATION_TOKEN'));
                    case 'message_new':
                        file_put_contents('../../logs/test.log', $req);

                        $logger->info('Status ok sent');
                        return $response->withStatus(200)->write('ok');
                    default:
                        $logger->info('An unsupported event was received');
                        return $response->withStatus(400)->write('An unsupported event was received');
                }

            } else {
                $logger->error('Secret key is not valid');
                return $response->withStatus(403)->write('Secret key is not valid');
            }
        } else {
            $logger->error('Group ID is not valid');
            return $response->withStatus(403)->write('Group ID is not valid');
        }
    }
}