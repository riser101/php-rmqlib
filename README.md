# RabbitMQ PHP Library
This library is for consuming/publishing to RabbitMQ queues using PHP. It is build on top of [php-amqplib](https://github.com/videlalvaro/php-amqplib) which extends the AMQP protocol for RabbitMQ.


## Installation
The recommended way to install php-rmqlib is through [Composer.](https://getcomposer.org/doc/00-intro.md)

1. Clone this repo and run `php composer.phar install` inside library root to install library dependencies.
2. Now edit QueueConfig.json in library root so that php-rmqlib can talk to your rabbitmq deploy.


    {
        "host": "ip_of_your_deployed_instance_of_rabbitmq",
        "port": 5672,
        "username": "your_rabbitmq_account_username",
        "password": "your_rabbitmq_account_password",
        "vhost": "cs",
        "heartbeat": 10
    }
2. Next, at the top of your PHP script require the rmq.php file :
    `require 'path/to/rabbitmqlib/src/rmq.php';`

All done, you're ready to use the awesomeness!

## Usage
To use this library, initialize the RabbitMQ class object.

#### Example 1
This example pushes your messages into the configured queue:

    //takes path to QueueConfig.json and log file. Creates a new library object.
  $rmq_object = new RabbitMQLib\RabbitMQ(__DIR__ . 'QueueConfig.json', __DIR__ . 'log.txt');
  //pushes your job to the queue
  $rmq_object->send($queue_name, 'your_message_goes_here', $params);

#### Example 2
This example pulls your messages into the configured queue:

  $rmq_object = new RabbitMQLib\RabbitMQ(__DIR__ . 'QueueConfig.json', __DIR__ . 'log.txt');
  //pulls a job from the queue
  $res = $rmq_object->get($queue);


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

#### License
Copyright (c) 2015 Yousuf Syed sy.yousuf9106@gmail.com, contributors. Released under the MIT license