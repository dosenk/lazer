<?php
    use Workerman\Worker;
    require __DIR__ . '/vendor/autoload.php';

    $worker = new Worker('tcp://0.0.0.0:19222');
    $worker->count = 1;
    $worker->uidConnections = array();

    // Emitted when new connection come
    $worker->onConnect = function($connection)
        {
            echo "New connection tcp \n";

        };

    $worker->onWorkerStart = function($worker)
        {
            echo "worker2->id={$worker->id}\n";
        };


/**
 * @param $connection
 * @param $data
 */
$worker->onMessage = function($connection, $data)
        {
            global $worker;
            //print_r($connection->uid);
            check_imei($data);
            if(!isset($connection->uid))
                {
                    $connection->uid = trim($data);
                    $worker->uidConnections[$connection->uid] = $connection;
                    //print_r($connection);
                    //var_dump($connection);
                    echo $connection->uid."\n";

                if ($connection->uid == 'web') {
                    return;
                    } else if (in_array($row, $connection->uid)) {
                        return;
                    } else {
                        $sql = "INSERT into `lazer`.`otm` (imei, work_mode) VALUES ('$connection->uid', 1)";
                        mysqli_query($conn, $sql);
                    }
                return $connection->send('login success, your uid is ' . $connection->uid);
                }


                list($recv_uid, $message) = explode(':', $data);
                //echo $recv_uid;

            if($recv_uid == 'all')
                {
                    broadcast($message);
                }
            // 给特定uid发送
            else
                {
                    sendMessageByUid($recv_uid, $message);
                }

            //echo $data;
            //$connection->send($data." - rec in tcp socket\n");
            //$uid = 1;
            //$ret = sendMessageByUid($uid, $data);
            //$connection->send($ret ? 'ok' : 'fail');
            //echo $connection->id." - connection id\n";
            // var_dump($connection);
            //echo $connection->worker->id." - worker id\n";

        };


            // Emitted when connection closed
        $worker->onClose = function($connection)
        {
            global $worker;
            if(isset($connection->uid))
            {
                unset($worker->uidConnections[$connection->uid]);
            }
        };

        // 向所有验证的用户推送数据
        function broadcast($message)
        {
            global $worker;
            foreach($worker->uidConnections as $connection)
            {
                $connection->send($message);

               // print_r($connection);
            }
        }

        // 针对uid推送数据
        function sendMessageByUid($uid, $message)
        {
            global $worker;
            //$connection = $worker->uidConnections;
            //$keys = array_keys($connection);
            //var_dump($keys);
//            print_r($connection);
            //print_r($connection[$keys[0]]);
            if(isset($worker->uidConnections[$uid]))
            {
                //echo 1;
                $connection = $worker->uidConnections[$uid];
                $connection->send($message);
                return true;
            }
            return false;
        }

        function check_imei ($data)
        {
            $sql = "SELECT imei FROM `lazer`.`otm`";
            $result = mysqli_query($conn, $sql);
            while ($row = $result->fetch_assoc()){
                $row += $row;
            }
            if (array_search($data, $row))  ;
            print_r($row);
        }

        function db_connect()
        {
            $db_name = "lazer";
            $mysql_username = "root";
            $mysql_password = "Qoq2ZdG7gLJm8VKW";
            $server_name = "localhost";
            $conn = mysqli_connect($server_name, $mysql_username, $mysql_password, $db_name);
        }

    Worker::runAll();