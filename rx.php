<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('hello', false, false, false, false);

global $apikey;
$apikey = getenv('WOKER_API_KEY');

function runCommand($msg){
	global $apikey;
	$msg = json_decode($msg,true);
	if ($msg['apikey'] == $apikey) {
		if ($msg['git_command'] = 'update'){
			shell_exec('/update.sh');
		}
	}
}

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
  runCommand($msg->body);
};
$channel->basic_consume('hello', '', false, true, false, false, $callback);
while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
