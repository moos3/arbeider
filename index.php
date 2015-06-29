<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$apikey = getenv('WEBHOOK_API_KEY');

function trigger($command,$notification){
  $connection = new AMQPConnection(getenv('RABBITMQ_NODE'), getenv('RABBITMQ_PORT'), 'guest', 'guest');
  $channel = $connection->channel();
  $channel->exchange_declare(getenv('RABBITMQ_NAME'), 'fanout', false, false, false);

  $worker_apikey = getenv('WORKER_API_KEY');
  $msg = array ('apikey'=>$worker_apikey, 'git_command' => $command);

  $msg = new AMQPMessage(json_encode($msg));
  $channel->basic_publish($msg, '', getenv('RABBITMQ_NAME') ?: 'hello');
}

$key = explode('=', $_SERVER['QUERY_STRING']);
$notification = json_decode(file_get_contents('php://input'),TRUE);
$notification['apikey'] = $key[1];

if ($notification['apikey'] == $apikey){
  if ($notification['build']['project_id'] == getenv('CODESHIP_PROJECT_ID')){
    if ($notification['build']['status'] == 'success'){
      trigger('update', $notification);
    }
  } else {
    echo 'Project Authentication Failure!';
    error_log('Project Authentication Failure');
    error_log(var_export($notification).PHP_EOL);
  }
} else {
  echo 'Authentication Failed!';
  error_log('Authentication Failure');
  error_log(var_export($notification).PHP_EOL);
}


?>
