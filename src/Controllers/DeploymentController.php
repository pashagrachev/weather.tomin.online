<?php
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

class DeploymentController {
    public function deployment(Request $request, Response $response){
        $logger = new Logger('deployment');
        $logger->pushProcessor(new UidProcessor);
        $file_handler = new StreamHandler("../logs/deployment.log");
        $logger->pushHandler($file_handler);

        $header = getallheaders();
        $hmac = hash_hmac('sha1', file_get_contents('php://input'), getenv('PAYLOAD_SECRET'));

        if (isset($header['X-Hub-Signature']) && $header['X-Hub-Signature'] === 'sha1='.$hmac) {
            $logger->info('Webhook ' . $header['X-Hub-Signature'] . 'verified');

            if(!empty($request->getParsedBody()['payload'])) {
                $logger->info(serialize($request->getParsedBody()['payload']));

                $update = false;
                $payload = $request->getParsedBody()['payload'];
                if(empty($payload['commits'])) {
                    $update = true;
                } else {
                    $branch = array_pop(explode("/", $payload['ref']));
                    if($branch === 'master') {
                        $update = true;
                    }
                }

                if($update) {
                    exec('cd ' . getenv('REPO_DIR') . ' && ' . getenv('GIT_BIN_PATH')  . ' fetch');
                    exec('cd ' . getenv('REPO_DIR') . ' && GIT_WORK_TREE=' . getenv('MASTER_DIR') . ' ' . getenv('GIT_BIN_PATH')  . ' checkout -f master');
                    $commit_hash = shell_exec('cd ' . getenv('REPO_DIR') . ' && ' . getenv('GIT_BIN_PATH')  . ' rev-parse --short HEAD');

                    $logger->info('Deployed branch: ' .  @$branch . ' Commit: ' . $commit_hash);
                }

                return $response->withStatus(200)->withJson(array('response' => 'Deployment completed'));
            } else {
                $logger->error('Payload not found');

                return $response->withStatus(400)->withJson(array('error' => 'Payload not found'));
            }
        } else {
            $logger->error('Webhook not valid');

            return $response->withStatus(403)->withJson(array('error' => 'Webhook not valid'));
        }
    }
}