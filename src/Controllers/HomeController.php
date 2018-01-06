<?php
namespace App\Controllers;

use App\Services\GeocodeService;
use App\Services\HeaderGenerateService;
use App\Services\SendMessageService;
use App\Services\UploadHeaderService;
use App\Services\WeatherService;
use App\Services\GetUserService;
use App\Services\RatingUsersService;
use Slim\Http\Request;
use Slim\Http\Response;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

class HomeController
{
    public function __invoke(Request $request, Response $response) {
        $req = $request->getParsedBody();

        $mongo = new \MongoDB\Client();
        $db = $mongo->tomin_weather;

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
                        break;
                    case 'message_new':
                        if((!empty($req['object']['body'])) && (stripos($req['object']['body'], '/город ')) === 0) {
                            $city = trim(str_replace('/город ', '', $req['object']['body']));
                            $pos = GeocodeService::getCoordinates($city);
                            if(!empty($pos['latitude']) && !empty($pos['longitude']) && !empty($pos['description'])) {
                                $logger->info('Message is received as a command: ' . $req['object']['body']);

                                $weather = WeatherService::getWeather($pos['latitude'], $pos['longitude']);

                                SendMessageService::sendMessage($req['object']['user_id'], $pos['description'] . '<br>Погода: ' . $weather['description'] . ' ' . $weather['icon'] . '<br>Температура: '.$weather['temperature'] . ' &#176;C<br>По ощущениям: ' . $weather['temperature_felt'] . ' &#176;C<br>Влажность: ' . $weather['humidity'] . ' %' . '<br>Давление: ' . $weather['pressure'] . ' мм рт. ст.<br>Облачность: ' . $weather['clouds'] . ' %<br>Ветер: ' . $weather['wind_deg'] . ', ' . $weather['wind_speed'] . ' м/c<br>Обновление: ' . $weather['datetime']);

                                $collection = $db->rating;
                                $document = $collection->findOne(['user_id' => $req['object']['user_id']]);
                                if(!empty($document)) {
                                    $collection->updateOne(['user_id' => $req['object']['user_id']], ['$inc' => ['count' => +1]]);
                                } else {
                                    $collection->insertOne(['user_id' => $req['object']['user_id'], 'count' => 1]);
                                }

                            } else {
                                $logger->info('City not recognized');
                                SendMessageService::sendMessage($req['object']['user_id'], 'Увы, у меня не получилось найти такой город &#128530;');
                                SendMessageService::sendMessage($req['object']['user_id'], 'Давай попробуем так. Пришли мне карту с местоположением, а я скажу какая там погода.');
                            }
                        } else if (!empty($req['object']['geo'])) {
                            $logger->info('Message is received as a geoobject: ' . $req['object']['geo']['coordinates']);

                            $coord = explode(' ', $req['object']['geo']['coordinates']);

                            $latitude = $coord[0];
                            $longitude = $coord[1];

                            $weather = WeatherService::getWeather($latitude, $longitude);
                            $place = GeocodeService::getPlace($weather['latitude'], $weather['longitude']);

                            SendMessageService::sendMessage($req['object']['user_id'], $place . '<br>Погода: ' . $weather['description'] . ' ' . $weather['icon'] . '<br>Температура: '.$weather['temperature'] . ' &#176;C<br>По ощущениям: ' . $weather['temperature_felt'] . ' &#176;C<br>Влажность: ' . $weather['humidity'] . ' %' . '<br>Давление: ' . $weather['pressure'] . ' мм рт. ст.<br>Облачность: ' . $weather['clouds'] . ' %<br>Ветер: ' . $weather['wind_deg'] . ', ' . $weather['wind_speed'] . ' м/c<br>Обновление: ' . $weather['datetime']);

                            $collection = $db->rating;
                            $document = $collection->findOne(['user_id' => $req['object']['user_id']]);
                            if(!empty($document)) {
                                $collection->updateOne(['user_id' => $req['object']['user_id']], ['$inc' => ['count' => +1]]);
                            } else {
                                $collection->insertOne(['user_id' => $req['object']['user_id'], 'count' => 1]);
                            }
                        } else {
                            $logger->info('Message is not valid format');
                            SendMessageService::sendMessage($req['object']['user_id'], 'К сожалению, я не распознал команду &#128532;');
                            SendMessageService::sendMessage($req['object']['user_id'], 'Чтобы узнать прогноз погоды - пришли мне карту с местоположением или команду "/город" с названием города, например: "/город Санкт-Петербург".');
                        }

                        $logger->info('Status ok sent');
                        return $response->withStatus(200)->write('ok');
                        break;
                    case 'group_join':
                        $user_id = $req['object']['user_id'];
                        $logger->info('A new user id'. $user_id .' joined the group');

                        $join_user = GetUserService::getUser($user_id);
                        $top_user = RatingUsersService::getRating();
                        $header = HeaderGenerateService::generateHeader($join_user['photo'], $join_user['first_name'], $join_user['last_name'], $top_user['photo'], $top_user['first_name'], $top_user['last_name']);

                        UploadHeaderService::savePhoto($header['image']);

                        $logger->info('Update header group');
                        return $response->withStatus(200)->write('ok');
                        break;
                    default:
                        $logger->info('An unsupported event was received');
                        return $response->withStatus(400)->write('An unsupported event was received');
                        break;
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