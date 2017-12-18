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

            return $response->withStatus(200)->withJson('OK');
        } else {
            $logger->error('Payload not found');

            return $response->withStatus(400)->withJson('Payload not found');
        }
    }
}