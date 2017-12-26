<?php
namespace App\Controllers;

use App\Services\GeocodeService;
use App\Services\SendMessageService;
use App\Services\WeatherService;
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
            $logger->info('Group ID ' . $req['group_id'] . ' verified');

            if($req['secret'] == getenv('VK_SECRET_KEY')) {
                $logger->info('Secret key ' . $req['secret'] . ' verified');

                switch ($req['type']) {
                    case 'confirmation':
                        $logger->info('Confirmation token sent');
                        return $response->withStatus(200)->write(getenv('VK_API_CONFIRMATION_TOKEN'));
                    case 'message_new':
                        if (!empty($req['object']['geo'])) {
                            $logger->info('Message is received as a geoobject: ' . $req['object']['geo']['coordinates']);

                            $coord = explode(' ', $req['object']['geo']['coordinates']);
                            $longitude = $coord[0];
                            $latitude = $coord[1];

                            // Debug
                            $logger->info('Longitude: ' . $longitude);
                            $logger->info('Latitude: ' . $latitude);

                            $weather = WeatherService::getWeather($latitude, $longitude);

                            // Debug
                            $logger->info('Weather: ' . serialize($weather));

                            $place = GeocodeService::getPlace($weather['latitude'], $weather['longitude']);

                            // Debug
                            $logger->info('Place: ' . serialize($place));

                            //SendMessageService::sendMessage($req['object']['user_id'], $place . '<br>Погода: ' . $weather['description'] . ' ' . $weather['icon'] . '<br>Температура: '.$weather['temperature'] . ' &#176;C<br>Влажность: ' . $weather['humidity'] . ' %' . '<br>Давление: ' . $weather['pressure'] . ' мм рт. ст.<br>Облачность: ' . $weather['clouds'] . ' %<br>Ветер: ' . $weather['wind_deg'] . ', ' . $weather['wind_speed'] . ' м/c<br>Обновление: ' . $weather['datetime']);
                        } else {
                            $logger->info('Message is not valid format');
                            SendMessageService::sendMessage($req['object']['user_id'], 'К сожалению, я не распознал команду &#128532;');
                            SendMessageService::sendMessage($req['object']['user_id'], 'Чтобы узнать прогноз погоды - отправь мне карту с нужным местоположением.');
                        }

                        $logger->info('Status ok sent');
                        return $response->withStatus(200)->write('ok');
                        break;
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