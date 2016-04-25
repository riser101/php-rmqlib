<?php namespace RabbitMQLib;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../utils/CommonLib/index.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use CommonLib\Log;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPRuntimeException;

class RabbitMQ {
    
    private $logger;
    private $channel;
    private $connection;
    private $HOST;
    private $PORT;
    private $USER;
    private $PASS;
    private $VHOST;
    private $HEARTBEAT;
    private $conn=0;
    
    /**
     * The class constructor
     *
     * @param json $config_file  Path to config file
     * @param txt $log_file  Path to log file
     * @return void
     */
    public function __construct($config_file, $log_file) 
	{
		$queue_data = json_decode(file_get_contents($config_file), true);
        $this->logger = new Log($log_file);
        if (empty($config_file)) 
        {
           $this->logger->log_error(10001, "Config data is empty", "", __METHOD__);
           exit();
        }
        $this->set_config($queue_data);   
    }
    
    /**
     * The class destructor    
     */
    public function __destruct() 
    {
		$this->logger = NULL;
	}

    public function set_config($queue_data)
    {
         $this->HOST = $queue_data["host"];
         isset($queue_data['port']) ? $this->PORT = $queue_data['port'] : $this->PORT = 5672;    
         isset($queue_data['username']) ? $this->USER = $queue_data['username'] : $this->USER = "guest";    
         isset($queue_data['password']) ? $this->PASS = $queue_data['password'] : $this->PASS = "guest";    
         isset($queue_data['heartbeat']) ? $this->HEARTBEAT = $queue_data['heartbeat'] : $this->HEARTBEAT = 10;    
         $this->VHOST = $queue_data["vhost"];
    }
	
	/**
 	 * Creates a connection and establishes a channel
 	 * 
     * @return boolean 
 	 */
	public function connect() 
	{
        $blnResult = FALSE;    
		try 
		{
            $this->connection = new AMQPStreamConnection($this->HOST, $this->PORT, $this->USER, $this->PASS, $this->VHOST, ['read_write_timeout' => 2 * $this->HEARTBEAT,'heartbeat'=> $this->HEARTBEAT]);
	    	if ($this->connection === NULL) 
	    	{
	    		$this->logger->log_error("", "connection is null", "", __METHOD__);
	    	 	return $blnResult;
	    	}
	    	$this->channel = $this->connection->channel();
            $this->conn=0;
			$blnResult = TRUE;	
        } catch (Exception $e) {
            $error_code = 2000;             
            $error_detail = "Could not Connect to the Queue Server Check if the Queue server is running or check if the connection string is correct.";
            $this->logger->log_error($error_code,"Cannot connect" , $error_detail, __METHOD__);
        }
        return $blnResult;
	}
	
	/**
 	 * Closes connection and channel
 	 * 
     * @return boolean 
 	 */
	public function close()
	{
	     try
	     {
	         $this->channel->close();
			 $this->connection->close();
			 return TRUE;
	     } catch (Exception $e) {
             $error_code = 2999;
             $error_detail = "Could not Close the Connection to the Queue Server: ". $e->getMessage();
             $this->logger->log_error($error_code, "Unable to close connection", $error_detail, __METHOD__); 
	     }
         return FALSE;
    }
    /**
 	 * Sends message to queue
 	 *
     * @param string $queue The name of the queue
     * @param string $message The message to be publish
     * @param Object $params Contains headers for rmq message       -   
     * @return boolean
 	 */
	public function send($queue, $message, $params)
    {
 		 if ($message === "") 
 		 {
 		     $error_code = 1002;
             $error_detail = "The message to be posted to queue cannot be left blank:\n";
             $this->logger->log_error($error_code, "Message is blank", $error_detail, __METHOD__);      	
             return FALSE;
 		 }
   		 if ($this->channel === NULL) 
   		 { 
	         $this->connect();
	     }
		 if ($this->connection->isConnected() === TRUE) 
		 { 
		     try
             {
                 property_exists($params, "contentType") ? $contentType = $params->contentType : $contentType = "application/JSON"; 
                 property_exists($params, "ttl") ? $Expiration = $params->ttl : $Expiration = 604800000; 
                 property_exists($params, "Persistent") ? $Persistent = $params->Persistent : $Persistent = 2; 
                 property_exists($params, "Priority") ? $Priority = $params->Priority : $Priority = 1;    
                 property_exists($params, "delay") ? $valXdelay = $params->delay : $valXdelay = 0;    
                 property_exists($params, "exchange") ? $exchangeName = $params->exchange and $queue = "" : $exchangeName = "";    
                 
                 $messageId = uniqid();
                 $hdrs = new AMQPTable(array("x-delay" => $valXdelay));
                 $message = new AMQPMessage( $message, array( 'content_type' =>$contentType,
		          							 	   		      'delivery_mode' => $Persistent,
		          							 	              'expiration' => $Expiration,
		          							 	              'priority' => $Priority,
                                                              'message_id' => $messageId ));
                 $message->set('application_headers', $hdrs);
                 $this->channel->basic_publish($message, $exchangeName, $queue);            
                 return TRUE;
             } catch (AMQPTimeoutException $e) {
                    $this->reconnect($message, $exchangeName, $queue);
             } catch (AMQPRuntimeException $e) {
                    $this->reconnect($message, $exchangeName, $queue);
             } catch (Exception $e) {
                  $error_code = 1499;
                  $error_detail = "The message could not be sent to the Queue:\n". $e->getMessage();
                  $this->logger->log_error($error_code, "Unable to send", $error_detail, __METHOD__);        
                  return FALSE;
             }// catch block ends here
         } else {
             $error_detail = "Not connected to RabbitMQ:\n";
             $this->logger->log_error("", "RabbitMQ channel is no longer connected", $error_detail, __METHOD__);     	
           }
	}

    public function reconnect($message, $exchangeName, $queue)
    {
        $retries=3;
        if (++$this->conn <= $retries) 
        {
            $txt = sprintf("Lost connection with RabbitMQ. Attempt[%d] Trying to reconnect.."."\n",$this->conn);
            echo $txt;
            $this->connect();
            try
            {
                $this->channel->basic_publish($message, $exchangeName, $queue);            
            } catch(Exception $e) {
                $error_code = 1450;
                $error_detail = "The message could not be sent to the Queue:\n". $e->getMessage();
                $this->logger->log_error($error_code, "Unable to send", $error_detail, __METHOD__); 
            }
        } else {
            echo "Lost connection with RabbitMQ. Giving up!";
            $this->close();
        }
    }// reconnect
    /**
 	 * Get single message from queue
 	 *
     * @param String $queue 
     * @return object $msgObj object or boolean false
  	 */
	public function get($queue)
	{
         if ($this->channel == NULL) { 
	         $this->connect();
         }
	     if ($queue === "") {
             $error_code = 1500;
             $error_detail = "QueueName cannot be left blank";
             $this->logger->log_error($error_code, "Queue Name is Blank", $error_detail, __METHOD__);           	
             return FALSE;
         }
         if ($this->connection->isConnected() === TRUE) 
         {
             try{
                 
                 $msgObj = $this->channel->basic_get($queue); 
                 if ($msgObj === NULL) 
                 {
                     $error_code = 4000;
                     $error_detail = "The Queue you are trying to access is empty at this time";
                     $this->logger->log_error($error_code, "Queue is empty", $error_detail, __METHOD__);           	
                     return FALSE;    		
                 }
                 return $msgObj;
             } catch (Exception $e) {
                 $error_code = 1599;
                 $error_detail = "The message could not be retrived from the Queue:\n". $e->getMessage();
                 $this->logger->log_error($error_code, "Cannot retrieve message", $error_detail, __METHOD__);     	
                 return FALSE;
             }
         $error_detail = "Not connected to RabbitMQ:\n";
         $this->logger->log_error("", "RabbitMQ channel is no longer connected", $error_detail, __METHOD__);     	
         return FALSE;
         }
	}

	/**
 	 * Acknowledge message on queue
 	 *
     * @param $queue This should be empty
     * @param $params The message object to acknowledge
     * @return boolean 
 	 */
	
	public function ack($queue ,$params)
	{
         $msgObj = $params;
         if( ! $msgObj) 
         {
         	echo "you need you pass a proper msg object";
         }
         if ($this->channel === NULL) 
         { 
	         $this->connect();
	     }
		 if ($this->connection->isConnected() === TRUE) 
         {
             try
             {
                 $this->channel->basic_ack($msgObj->delivery_info['delivery_tag']);            
                 return TRUE;
             } catch(Exception $e) {
                 $error_code = 1699;
                 $error_detail = "Could not acknowledge the message:\n". $e->getMessage();
                 $this->logger->log_error($error_code, "unable to acknowledge", $error_detail, __METHOD__);     	
                 return FALSE;
             }
         }
         $error_detail = "Not connected to RabbitMQ:\n";
         $this->logger->log_error("", "RabbitMQ channel is no longer connected", $error_detail, __METHOD__);     	
         return FALSE;
	}

	/**
 	 * Subscribes to queue
 	 *
     * @param string $queue The queue name
     * @param $callback function - recives the msg object 
     * @return message object or false 
 	 */
	public function subscribe($queue, $callback)
	{
	     $blnResult = FALSE;	
	     $blnVerifyConnection = FALSE;
         if ($this->channel === NULL) 
	     { 
	         $blnVerifyConnection = $this->connect(); 
	     }
         if ($this->connection->isConnected() === TRUE) 
         {
	          try
	          {
	             //sets prefetch count to one
                 $this->channel->basic_qos(null, 1, null);
                 $this->channel->basic_consume($queue, '', FALSE, TRUE, FALSE, FALSE, $callback);
                 while(count($this->channel->callbacks)) {
 			         $this->channel->wait();
                 }
 			  } catch (Exception $e) {
	             $error_code = 1700;
                 $error_detail = "Could not subscribe message from the queue\n". $e->getMessage();
                 $this->logger->log_error($error_code, "Unable to subscribe", $error_detail, __METHOD__);         
                 return FALSE;
	          }
		}
         $error_detail = "Not connected to RabbitMQ:\n";
         $this->logger->log_error("", "RabbitMQ channel is no longer connected", $error_detail, __METHOD__);        
         return FALSE;
	}

    /**
     * Requeues the message back to the queue 
     *
     * @param string $queue The queue name
     * @param object The message object returned from method get
     * @return boolean 
     */
    public function requeue($queue,$params)
    {
         $msg = $params;
         if ($this->connection->isConnected() === TRUE) 
         {   
            try
            {
               $this->channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);
               return true;
            } catch (Exception $e) {
               $error_code = 1701;
               $error_detail = "Could not requeue the message:\n". $e->getMessage();
               $this->logger->log_error($error_code, "unable to requeue", $error_detail, __METHOD__);         
               return FALSE;
            }// catch
        }// if connection condition
    }// requeue
}
?>


