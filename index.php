<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$apikey = getenv('WEBHOOK_API_KEY');

function trigger($command){
  $connection = new AMQPConnection(getenv('RABBITMQ_NODE'), getenv('RABBITMQ_PORT'), 'guest', 'guest');
  $channel = $connection->channel();
  $channel->queue_declare(getenv('RABBITMQ_NAME'), false, false, false, false);

  $apikey = getenv('WORKER_API_KEY');
  $msg = array ('apikey'=>$apikey, 'git_command' => $command);

  $msg = new AMQPMessage(json_encode($msg));
  $channel->basic_publish($msg, '', 'hello');
}

$notification = $_POST;

if ($notification['apikey'] == $apikey){
  if ($notification['build']['status'] == 'success'){
    trigger('update', $notification);
  }
} else {
  echo 'Authentication Failed!'
}


?>
