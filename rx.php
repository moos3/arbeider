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
	$connection = new AMQPConnection(getenv('RABBITMQ_NODE') ?: '127.0.0.1', getenv('RABBITMQ_PORT') ?: '5672', getenv('WORKERMQ_USERNAME') ?: 'guest', getenv('WORKERMQ_PASSWORD') ?: 'guest');
	$channel = $connection->channel();

	$channel->exchange_declare(getenv('RABBITMQ_QUEUE') ?: 'hello', 'fanout', false, false, false);

	echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
	$callback = function($msg) {
	  echo " [x] Received ", $msg->body, "\n";
	  	runCommand($msg->body);
	};

	$channel->basic_consume(getenv('RABBITMQ_QUEUE') ?: 'hello', '', false, true, false, false, $callback);
	while(count($channel->callbacks)) {
	    $channel->wait();
	}
	$channel->close();
	$connection->close();

} catch (Exception $e) {
	echo "rabbitmq is currently down".PHP_EOL;
}


?>
