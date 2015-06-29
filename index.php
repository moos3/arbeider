<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

include('config.inc');

function trigger($command,$notification){
  global $rabbitmq_node, $rabbitmq_port, $rabbitmq_name, $apikey, $worker_apikey;
  $connection = new AMQPConnection($rabbitmq_node, $rabbitmq_port ?: 5672, 'guest', 'guest');
  $channel = $connection->channel();
  $channel->exchange_declare($rabbitmq_name, 'fanout', false, false, false);

  $msg = array ('apikey'=>$worker_apikey, 'git_command' => $command);

  $msg = new AMQPMessage(json_encode($msg));
  $channel->basic_publish($msg, $rabbitmq_name ?: 'hello');
  $channel->close();
  $connection->close();
}

$key = explode('=', $_SERVER['QUERY_STRING']);
$notification = json_decode(file_get_contents('php://input'),TRUE);
$notification['apikey'] = $key[1];

if ($notification['apikey'] == $apikey){
  if ($notification['build']['project_id'] == $codeship_project_id){
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
