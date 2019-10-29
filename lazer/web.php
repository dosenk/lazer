<?php

    require __DIR__ . '/vendor/autoload.php';
    use \Workerman\Worker;
    use \Workerman\Connection\AsyncTcpConnection;
    use Workerman\Lib\Timer;

    $TCP_ADDRESS = 'tcp://127.0.0.1:19222';

    $context = array(
        'ssl' => array(
            'local_cert'                 => '/var/www/lazer/vendor/workerman/ssh/server.crt',
            'local_pk'                   => '/var/www/lazer/vendor/workerman/ssh/server.key',
            'verify_peer'                => false,
            // 'allow_self_signed' => true,
        )
    );

    $worker = new Worker('websocket://0.0.0.0:8003', $context);
    $worker->count = 1;
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
//            $connection_to_tcp->send('web');
            $connection_to_tcp->connect();
        };

    $worker->onWorkerStart = function($worker)
        {
            echo "worker->id={$worker->id}\n";
        };


    $worker->onMessage = function($connection, $data)
        {
            echo $data;
            global $connection_to_tcp;
//            $db = new \Workerman\MySQL\Connection(
//                'laradock_mariadb_1',
//                '3306',
//                'root',
//                '987654321As!',
//                'lazer');

            // принимаем json, если в начале json написано Db, то это запрос в БД, если что-то другое, то в сокет ТСП
//            $sql =
            $connection_to_tcp->send($data);
        };


    $worker->onClose = function($connection)
        {
            echo "Connection closed\n";
        };

    Worker::runAll();