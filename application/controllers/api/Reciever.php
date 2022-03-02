<?php
require_once __DIR__ . '/../../third_party/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;


$connection = new AMQPStreamConnection('puffin.rmq2.cloudamqp.com', 5672, 'glbdmqhy', 'NN1dcbt4jEkX_5sXJerzBkP-2RHsF5pP', $vhost = 'glbdmqhy');
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
};

$channel->basic_consume('hello', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

 ?>