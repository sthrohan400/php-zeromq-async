<?php
/** Demo Request Controller to handle Client POST OR GET Request asynchorously with use of broker and worker php scripts with ZEROMQ  */
class ClientRequestController
{
   private $queue;
   public function __constructor(){
        $this->queue = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ);
        $this->queue->connect("tcp://localhost:5560");
   } 
   public function handleRequest(){
       $request = $_POST;
       /** DO some data validation script here and process further */
       /** Sendiing Post request from client to Broker POLL 
        *  Asynchronous implementation doesnot wait for response thus return some message
       */
       $this->queue->send(json_encode($request));
       echo json_encode(array("status"=>200,"data"=>[]));
       exit();

   }
}
