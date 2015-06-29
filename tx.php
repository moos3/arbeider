<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->exchange_declare(getenv('RABBITMQ_NAME') ?: 'hello', 'fanout', false, false, false);

$apikey = getenv('WORKER_API_KEY');
$msg = array ('apikey'=>$apikey, 'git_command' => 'update');

$msg = new AMQPMessage(json_encode($msg));
$channel->basic_publish($msg, 'hello');
echo " [x] Sent ".var_export($msg)."\n";
$channel->close();
$connection->close();
?>
