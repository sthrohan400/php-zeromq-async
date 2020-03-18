<?php
/** Worker Process which gets request from POLL set  and starts execution based on the parameters ( messages) from POLL readable array  */
$listen_port = "5560";
echo "Connecting to Broker on PORT:".$listen_port.".";
$subscriber = new ZMQSocket(new ZMQContext(),ZMQ::SOCKET_REP); 
$subscriber->connect("tcp://localhost:5560");
echo "Connected to Broker.";
while (true) {
    // Read envelope with address only on PUB SUB
    // $channel = $subscriber->recv();
    // receive and handle message contents from broker $readable array
    $jsoninput = $subscriber->recv();
    /** Handles JSON input and perform specific task */
    echo $jsoninput;
}




