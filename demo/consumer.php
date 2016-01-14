<?php
	use PhpAmqpLib\Message\AMQPMessage;
	require __DIR__ . '/../src/rmq.php';
	$objRMQ = new RabbitMQLib\RabbitMQ(__DIR__ . '/config/QueueConfig.json',__DIR__ .'/logs.txt');
	
	// connect to you RabbitMQ
	$objRMQ->connect();	
	
	// Get messages from queue without ack
	for($i=0;$i<100;$i++)
	{
		$msgObject = $objRMQ->get("testqueue");
		echo "message from get: " . $msgObject->body . "\n";
	}

	// // Get messages from queue with ack
	for($i=0;$i<100;$i++)
	{
		$msgObject = $objRMQ->get("testqueue");
		echo "msg from get: " . $msgObject->body . "\n";

		//ACKNOWLEDGE
		$objRMQ->ack("testqueue", $msgObject);
		echo "ACKNOWLEDGED" . "\n";
	}

	// Requeue message 
	for($i=0;$i<100;$i++)
	{
		$msg = $objRMQ->get("testqueue");
		$objRMQ->requeue("testqueue",$msg);
		echo 'your message:"'.$msg->body.'" got requeued!'."\n";
	}
?>