<?php
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

class ResetCountController {
    public function __invoke(Request $request, Response $response){
        $logger = new Logger('count');
        $logger->pushProcessor(new UidProcessor);
        $file_handler = new StreamHandler("../logs/count.log");
        $logger->pushHandler($file_handler);

        if (date("H:i") === getenv('RESET_COUNT_TIME')) {
            $mongo = new \MongoDB\Client();
            $db = $mongo->tomin_weather;
            $collection = $db->rating->updateMany(
                [],
                ['$set' => ['count' => 0]],
                ['multi' => true]
            );

            $logger->info('The counters are reset');

            return $response->write('The counters are reset');
        } else {
            $logger->error('The launch time is not valid');

            return $response->withStatus(403)->write('Webhook not valid');
        }
    }
}