<?php
	use PhpAmqpLib\Message\AMQPMessage;
	require __DIR__ . '/../src/rmq.php';
	
	$objRMQ = new RabbitMQLIB\RabbitMQ(__DIR__ . '/config/QueueConfig.json',__DIR__ .'/logs.txt');
	// Connects to rabbitMQ
	$objRMQ->connect();	
	
	// MESSAGE HEADERS 
    $msgHeadersObj = new stdClass;
	// $msgHeadersObj->ttl = 4000;
	$msgHeadersObj->contentType = "application/json";
	$msgHeadersObj->Persistent = 2;	
	$msgHeadersObj->Priority = 5;
	//$msgHeadersObj->Xdelay = 60000;
	// $msgHeadersObj->exchange = "phpFanoutExchange";
	
	// SEND
    for($i=0;$i<100000;$i++) 
    {
	    $returnVal = $objRMQ->send("testqueue", "some-test-message".$i, $msgHeadersObj);
		echo "number of msgs sent: ".$i."\r";
	}
?>