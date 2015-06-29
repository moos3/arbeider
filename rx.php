<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

global $apikey;
$apikey = getenv('WORKER_API_KEY');

function runCommand($msg){
	global $apikey;
	$msg = json_decode($msg,true);
	if ($msg['apikey'] == $apikey) {
		if ($msg['git_command'] = 'update'){
			shell_exec('/update.sh');
		}
	}
}

try {
  $connection = new AMQPConnection(getenv('RABBITMQ_NODE'), getenv('RABBITMQ_PORT'), 'guest', 'guest');
  $channel = $connection->channel();

  $channel->exchange_declare(getenv('RABBITMQ_NAME') ?: 'hello', 'fanout', false, false, false);
  list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
  $channel->queue_bind($queue_name, getenv('RABBITMQ_NAME') ?: 'hello');

  echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
  $callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";
          runCommand($msg->body);
  };

  $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
  while(count($channel->callbacks)) {
      $channel->wait();
  }
  $channel->close();
  $connection->close();

} catch (Exception $e) {
        echo "rabbitmq is currently down".PHP_EOL;
}


?>
