<?php
/*** Asynchronous Request Response  Implementation Using ZeroMQ Sockets
 * @Installation : ZeroMQ should be added as PHP extension on Server.
 * @Condition for Above Pattern Used
 * Multiple request comes to broker which queues ( not persistence queue for persistence queue use rabbit mq or others) the request from client ( Application or website)
 * Broker Connects both client and worker
 * @Client : Client provides request to process
 * @worker: Worker will be run to execute request provided by client 
 * Broker handles request put it to queue and worker gets the client request from the queue and process it.
 * Benefit for this approach would be we can add multiple workers and make our request execution faster without any major update at client request end.
 * @RUN  YOu need to run this script on Command promt or shell like: pathtophp.exe broker.php
  */
  echo "Initializing Broker..";
  $context = new ZMQContext();
    /** Socket Define to connect with Client Request with PORT 5559 ( Internal Port) */
  $frontend = new ZMQSocket($context, ZMQ::SOCKET_ROUTER);
    /** Socket Define to Connect with Worker ( Scripts handling request Processing) with PORT 5560 */
  $backend = new ZMQSocket($context, ZMQ::SOCKET_DEALER);
  $frontend->bind("tcp://*:5559");
  echo "Client  PORT Bind Success..".PHP_EOL;
  $backend->bind("tcp://*:5560");
  echo "Worker Port Bind Success..".PHP_EOL;
  //  Initialize poll set
  /** Creates new Record Set or QUEUE for Incoming and Outgoing Mesage 
   * @ZMQ::POLL_IN  Since we have provided ZMQ:POLL_IN Paramter to ZMQPOll object it only records Incoming message now.
   * @ZMQ::POLL_OUT 
   */

  $poll = new ZMQPoll();
  // $poll->add($frontend, ZMQ::POLL_IN | ZMQ::POLL_OUT);
  $poll->add($frontend, ZMQ::POLL_IN); // Adds data from client request to poll set or queue set or record set
  $poll->add($backend, ZMQ::POLL_IN);// Adds data from worker proecss to add to queue set 

  $readable = $writeable = array();

  /**
   * Now we loop infinitely to listen for incoming message
   * and pass it to worker processes
   * 
   */
  while (true) {
    /** Reading data from POLL  */  
    try {
        /*@var readable Readable means data present on socket receive buffer
        * @var writable means space available in socket send buffer
        */
        $events = $poll->poll($readable, $writeable);  
        $errors =  $poll->getLastErrors();
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                echo "Error polling object " . $error . "\n";
            }
        }
    }
    catch(ZMQPOllException $e){
        echo "POLL Failed:".$e->getMessage()."\n";
    }

    foreach ($readable as $socket) {
        if ($socket === $frontend) {
            //  Process all parts of the message
            while (true) {
                $message = $socket->recv();
                /**
                 * @FLAG ZMQ::SOCKOPT_RCVMORE Set to receive multipart messages
                 */
                $more = $socket->getSockOpt(ZMQ::SOCKOPT_RCVMORE);
                /** Sending POLL requests to Worker Process with body message  */
                $backend->send($message, $more ? ZMQ::MODE_SNDMORE : null);
                if (!$more) {
                    break; //  Last message part
                }
            }
        } elseif ($socket === $backend) {
            $message = $socket->recv();
			$response = json_decode($message,true);
        }
    }
}
