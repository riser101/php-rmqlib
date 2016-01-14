<?php
	use PhpAmqpLib\Message\AMQPMessage;
	require __DIR__ . '/../src/rmq.php';
	$objRMQ = new RabbitMQLib\RabbitMQ(__DIR__ . '/config/QueueConfig.json',__DIR__ .'/logs.txt');
	
	// connects to rabbitMQ
	$objRMQ->connect();	
	
	// callback function that recieves the $msg object
	$callback = function($msg){
  		echo "message from callback: ".$msg->body."\n";
  	};
	
	// subscribers to queue
	$objRMQ->subscribe("testqueue", $callback);
?>