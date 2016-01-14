# RabbitMQ PHP Library
-
This library is for consuming RabbitMQ queues using PHP.


## Installation
Run `php composer.phar install` to install dependencies.

At the top of your PHP script require the rmq.php file :

	require 'path/to/rabbitmqlib/src/rmq.php';


## Usage
To use this library, initialize the RabbitMQ class object.

#### Example 1

	$rmq_object = new RabbitMQLib\RabbitMQ(__DIR__ . "config/config.json", __DIR__ . "log.txt");
	$rmq_object->send($queue, $message, $params);

#### Example 2

	$rmq_object = new RabbitMQLib\RabbitMQ(__DIR__ . "config/config.json", __DIR__ . "log.txt");
	$res = $rmq_object->get($queue);

	// response
	if (!$res) { 
		// got error
	} else { // continue processing with response
		// var_dump($res);
	}

#### Output:
	object(PhpAmqpLib\Message\AMQPMessage)#22 (8) {
      ["body"]=>
      string(17) "your message body"
      ["body_size"]=>
      string(2) "13"
      ["is_truncated"]=>
      bool(false)
      ["content_encoding"]=>
      NULL
      ["delivery_info"]=>
      array(5) {
        ["delivery_tag"]=>
        string(1) "1"
        ["redelivered"]=>
        bool(true)
        ["exchange"]=>
        string(0) ""
        ["routing_key"]=>
        string(8) "phpTestQ"
        ["message_count"]=>
        int(0)
      }
      ["prop_types":protected]=>
      array(14) {
      ["content_type"]=>
      string(8) "shortstr"
      ["content_encoding"]=>
      string(8) "shortstr"
      ["application_headers"]=>
      string(12) "table_object"
      ["delivery_mode"]=>
      string(5) "octet"
      ["priority"]=>
      string(5) "octet"
      ["correlation_id"]=>
      string(8) "shortstr"
      ["reply_to"]=>
      string(8) "shortstr"
      ["expiration"]=>
      string(8) "shortstr"
      ["message_id"]=>
      string(8) "shortstr"
      ["timestamp"]=>
      string(9) "timestamp"
      ["type"]=>
      string(8) "shortstr"
      ["user_id"]=>
      string(8) "shortstr"
      ["app_id"]=>
      string(8) "shortstr"
      ["cluster_id"]=>
      string(8) "shortstr"
    }
    ["properties":"PhpAmqpLib\Wire\GenericContent":private]=>
    array(2) {
      ["application_headers"]=>
      object(PhpAmqpLib\Wire\AMQPTable)#24 (1) {
        ["data":protected]=>
        array(0) {
        }
      }
      ["delivery_mode"]=>
      int(2)
    }
    ["serialized_properties":"PhpAmqpLib\Wire\GenericContent":private]=>
    NULL
    }


## Configuration File
This library requires a config in json format:

    {
        "host": "172.16.60.15",
        "port": 5672,
        "username": "bms",
        "password": "bms2015",
        "vhost": "cs",
        "heartbeat": 10
    }

## Methods

### __constructor

It will throw RuntimeExpection if the log file is not writable.

### connect

    void RabbitMQ::connect()

Connects to the queue 

* Visibility: **public**
 
### close
    void RabbitMQ::connect()

 Closes connection and channel to the queue

* Visibility: **public**

### send

    void RabbitMQ::send(string $queue, string $message, object $params)

Post new message to the queue



* Visibility: **public**


#### Arguments
* $queue **string** - The name of the queue to use
* $message **string** - The message to be posted to queue
* $params **object** - Contains message headers 



### get

    void RabbitMQ::get(string $queue)

Get message from queue 

* Visibility: **public**

#### Arguments
* $queue **string** - The name of the queue to use

### ack

    void RabbitMQ::ack(string $queue, object $params)

Removes message from the queue

* Visibility: **public**

#### Arguments
* $queue **string** - The name of the queue to use
* $params **object** - Contains the message object returned by the get method 

### subscribe

    void RabbitMQ::subscribe(string $queue, function $callback)

Subscribes to queue

* Visibility: **public**

#### Arguments
* $queue **string** - The name of the queue to use
* $callback **function** - Callback function that recieves the message object

### requeue

    void RabbitMQ::requeue(string $queue, object $params)

Requeues messages back to the queue by sending nacks.

* Visibility: **public**

#### Arguments
* $queue **string** - The name of the queue to use
* $params **object** - The $msg object returned from the queue

## Error Codes

| Error Code                            | Description                                 |
|---------------------------------------|---------------------------------------------|
| 2000									 | Could not establish a connection to rabbitMQ
| 2999                                  | Unable to close the connection to rabbitMQ  |
| 1002                                  | The message to be posted is blank           |
| 1499					                 |Failed to send message to RMQ                |
| 1450                                  | Failed to send message after reconnection   |
| 1500 								     | Queue name is blank                         |
| 1599 									 | Failed to fetch message from RMQ            |
| 1699									 | Failed to acknowledge message               |
| 1700 									 | Failed to subscribe message from RMQ        |
| 1701							         | Failed to requeue message                   |
|**Error while performing operation**   | (see error description for detail in logs) |	






