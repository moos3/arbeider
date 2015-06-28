<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

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

function getCommitRevision(){
	$command = 'git rev-parse HEAD';
	exec('cd '.getenv('APP_ROOT').' && '.$command, $rev);
	return $rev;
}

try {
	$connection = new AMQPConnection(getenv('RABBITMQ_NODE'), getenv('RABBITMQ_PORT'), 'guest', 'guest');
	$channel = $connection->channel();

	$channel->queue_declare(getenv('RABBITMQ_QUEUE'), false, false, false, false);


	echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
	$callback = function($msg) {
	  echo " [x] Received ", $msg->body, "\n";
		$rev = getCommitRevision();
		if ($msg['build']['commit_id'] != $rev ){
	  	runCommand($msg->body);
		}
	};

	$channel->basic_consume(getenv('RABBITMQ_QUEUE'), '', false, true, false, false, $callback);
	while(count($channel->callbacks)) {
	    $channel->wait();
	}
	$channel->close();
	$connection->close();

} catch (Exception $e) {
	echo "rabbitmq is currently down".PHP_EOL;
}


?>
