<?php

    require __DIR__ . '/vendor/autoload.php';
    use \Workerman\Worker;
    use \Workerman\Connection\AsyncTcpConnection;
    use Workerman\Lib\Timer;


    $TCP_ADDRESS = 'tcp://127.0.0.1:19222';




    $context = array(
        'ssl' => array(
            'local_cert'                 => '/etc/httpd/conf/ssl3/mayak.net.crt',
            'local_pk'                   => '/etc/httpd/conf/ssl3/device.key',
            'verify_peer'                => false,
            // 'allow_self_signed' => true,
        )
    );

    $worker = new Worker('websocket://0.0.0.0:8003', $context);
    $worker->count = 2;
    $worker->transport = 'ssl';
    $worker->uidConnections = array();


    // Emitted when new connection come
    $worker->onConnect = function($connection)
        {

            echo "New web connection\n";

            global $TCP_ADDRESS;
            $connection_to_tcp = new AsyncTcpConnection($TCP_ADDRESS);
            $connection->pipe($connection_to_tcp);
            $connection_to_tcp->pipe($connection);
            $connection_to_tcp->send("web");
            $connection_to_tcp->connect();



//            $connection_to_tcp = new AsyncTcpConnection($TCP_ADDRESS);
//            $connection_to_tcp->onMessage = function ($connection_to_tcp, $buffer) use ($connection)
//                {
//                    //$buffer = $buffer."adscvsadef";
//                    $connection->send("123");
//                };
//            $connection_to_tcp->onClose = function($connection_to_tcp) use ($connection)
//                {
//                    $connection->close();
//                };
//            $connection_to_tcp->connect();

        };

    $worker->onWorkerStart = function($worker)
        {
            echo "worker2->id={$worker->id}\n";

        };


    $worker->onMessage = function($connection, $data)
        {
            echo $data."\n";
            global $connection_to_tcp;

            $connection_to_tcp->send($data);

        };


    $worker->onClose = function($connection)
        {
            echo "Connection closed\n";
        };

    Worker::runAll();