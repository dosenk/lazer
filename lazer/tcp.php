<?php
    use Workerman\Worker;
    use Classes\Data;
    require __DIR__ . '/vendor/autoload.php';

    set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line) {
        throw new ErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
    });


    $worker = new Worker('tcp://0.0.0.0:19222');
    $worker->count = 1;
    $worker->uidConnections = array();
    $worker->onWorkerStart = function($worker)
    {
        write_log("worker->id = {$worker->id}");
    };

    $worker->onConnect = function($connection)
    {
        try {
            write_log("New connection tcp. Connection id: $connection->id");
        } catch (\Throwable $e) {
            write_log( "Error: " . $e->getMessage() . ". Line: " . $e->getLine());
        }
    };

    /**
    * @param $connection
    * @param $data
    * @return mixed
    * 1. при подключении просто щлет imei
    * 2. проверка на наличие в БД
    * 3. отправляем ответ (+ пишем imei в log) + назначаем новый $connection->uid
    * 4. если есть в бд отправляем режим работы
    */
    $worker->onMessage = function($connection, $data) {
        try {
//            print_r($data);
            global $worker;
            $db = new \Workerman\MySQL\Connection(
                'laradock_mariadb_1',
                '3306',
                'root',
                '987654321As!',
                'lazer');

            $log = "Connection id: $connection->id";

            if (!isset($connection->uid)) {

                $imei = trim($data);

                $log .= ". Login: $imei ";

                // если TCP клиент:
                $sql = "SELECT `otm_view`.`imei`, `otm_view`.`work_mode`, `otm_view`.`location_interval`
                                FROM `lazer`.`otm_view` 
                                WHERE `otm_view`.`imei` 
                                LIKE '$imei'";

                $data_sql = $db->query($sql, null, PDO::FETCH_ASSOC);

                if (!empty($data_sql)) {

                    $rv = $data_sql[0];
                    $data = new Data($rv['imei'], $rv['work_mode'], $rv['location_interval']);
                    $webUsers = $data->getActiveUsers($db, $imei); // список активных пользователей
                    $checkConnection = function($imei) use (
                                                            &$worker,
                                                            &$connection,
                                                            &$data,
                                                            &$checkConnection,
                                                            &$log,
                                                            &$webUsers
                                                            )
                    {
                        if (!array_key_exists($imei, $worker->uidConnections)) {
                            $connection->uid = $imei; // это будет imei
                            $worker->uidConnections[$imei] = $connection;
                            write_log($log. " - [LOGGED IN]");
                            sendMessageByUid($imei, $data->prepare_data($webUsers) . "\n");
                        } else {
                            write_log("$log. The socket is already open.");
                            $worker->uidConnections[$imei]->close();
                            $checkConnection($imei);
                        }
                    };
                    $checkConnection($imei);
                    return;
                } elseif (strlen($imei) <= 10) { // WEB -- СОКЕТЫ ! ! ! ! ! ! ! ! ! ! ! ! ! !
//                    $sql = "SELECT `user` FROM `users` WHERE `id` = '$imei'";
//                    $data_sql = $db->query($sql, null, PDO::FETCH_ASSOC);
                    $connection->uid = $imei; // это будет imei
                    $worker->uidConnections[$imei] = $connection;
                    write_log($log. " - [LOGGED IN]");
                    return;
                }
                write_log("$log. Object is not controlled.");
                return;

            }

            $rv = (json_decode($data));

            if (!empty($rv->webSender)) { // если пришло сообщение из веб сокета

                $imei = $rv->imei;
                $wm = $rv->workMode;
                $locInt = $rv->locInterval ?? NULL;
                $duration = $rv->duration ?? NULL;
                $duration_start_time = $rv->duration_start_time ?? NULL;
                $webSender = $rv->webSender;

                $data = new Data($imei, $wm, $locInt, $duration , $duration_start_time);

                    if ($rv->action == "getFiles") {// 1 - получить все файлы
                        $message = sendMessageByUid($data->imei,
                            $data->prepare_data([$webSender], 1) . "\n") ?
                            "Data send" : "Data dont send. Socket not connected";
                        write_log("Login: $imei. Sent to the socket to receive ALL VOICE files.");
                    } else if ($rv->action == "send_info") { // отправить режим работы, интревал и длительность записи в сокет
                        $message = sendMessageByUid($data->imei,
                            $data->prepare_data([$webSender]) . "\n") ?
                            "Data send" : "Data dont send. Socket not connected";
                    }
                    sendMessageByUid($connection->uid, $message);
                    return;
            } else { // если пришло сообщение из TCP сокета

                if (is_json($data)) {
//                print_r($data);
                    $rv = json_decode($data); // входящий JSON
                    $webClients = json_decode($rv->sender) ?? NULL;
                    $data = new Data($rv->imei);

                    $log .= ". Login: $data->imei.";



                    if (property_exists($rv, 'type')) {
                        switch ($rv->type) {
                            case 'info':
                                $webUsers = $data->getActiveUsers($db, $data->imei) ?? '';
                                $data->battery = $rv->battery ?? null;
                                $data->space = $rv->space ?? null;
                                $data->datetime = $rv->datetime ?? null;
                                $sql = "INSERT INTO `lazer`.info (lazer.info.id_otm, 
                                                                  lazer.info.battery,
                                                                  lazer.info.space, 
                                                                  lazer.info.datetime) 
                                                 VALUE ((SELECT otm_view.id 
                                                          FROM otm_view 
                                                          WHERE otm_view.imei = '$data->imei'), 
                                                        '$data->battery', 
                                                        '$data->space', 
                                                        '$data->datetime')";
                                if ($db->query($sql)) {
                                    if (!empty($webClients)) {
                                        foreach ($webUsers as $key=>$user) { // отправляет данные согласно полученных из сокета пользователей
//                                            print_r($user. "\n");
                                            sendMessageByUid($user, $data->send_data_client());
                                        }
//                                        sendMessageByUid($webUsers, $data->send_data_client());
                                    }
                                    $log .= " Recive info(battery, space). ";
                                } else {
                                    $log .= " Error writing device data.";
                                }
                                break;
                            case 'location':
                                $webUsers = $data->getActiveUsers($db, $data->imei) ?? '';
                                $data->latitude = $rv->latitude ?? null;
                                $data->longitude = $rv->longitude ?? null;
                                $data->datetime = $rv->datetime ?? null;
                                $data->deviation = $rv->deviation ?? null;
                                $data->speed = $rv->speed ?? null;
                                if (!empty($data->longitude) && !empty($data->latitude)) {
                                    $sql = "INSERT into `lazer`.`location`
                                                 (`location`.`id_otm`, `location`.`latitude`, `location`.`longitude`)
                                                 VALUES ((SELECT otm_view.id 
                                                 FROM otm_view 
                                                 WHERE otm_view.imei = '$data->imei'), 
                                                         '$data->latitude', '$data->longitude')";

                                    if ($db->query($sql, null, PDO::FETCH_ASSOC)) {
                                        if (!empty($webClients)) {
                                            foreach ($webUsers as $key=>$user) { // отправляет данные согласно полученных из сокета пользователей
                                                sendMessageByUid($user, $data->send_GeoJSON_point(
                                                    $data->deviation,
                                                    $data->speed,
                                                    $data->datetime)
                                                );
                                            }
//                                            sendMessageByUid($webUsers, $data->send_GeoJSON_point($data->deviation, $data->speed, $data->datetime));
                                        }
                                    }
                                    $log .= " Recive location";
                                } else {
                                    $log .= " Координаты не получены. Принятые данные:". json_encode($rv);
                                }
                                break;
                            case 'audio':
                                foreach ($webClients as $key=>$user) {
                                    sendMessageByUid($user, json_encode($rv));
                                }
                                $log .= " $rv->record";
                                break;
                        }
                        write_log($log);
                        return;
                    } else {
                        print_r($rv);
                        $log .= " Does not have a field type ";
                    }
                } else {
                    $log .= " Неправильный формат данных или данные не пришли. Полученые данные: $data";
                }
                write_log($log);
                return;
            }
        } catch (\Throwable $e) {
            write_log( "Error: " . $e->getMessage() . ". Line: " . $e->getLine());
        }
    };

    $worker->onClose = function($connection)
    {
    try {
        global $worker;
        if(isset($connection->uid))
        {
            write_log("Connection id: $connection->id - CLOSED. Connections: $connection->uid - [LOGGED OUT]");
            unset($worker->uidConnections[$connection->uid]);
        }
    } catch (\Throwable $e) {
        write_log( "error:" . $e->getMessage() . "line:" . $e->getLine());
    }
    };


    /**
     * @param $message
     * отправка сообщения всем сразу
     */
    function broadcast($message)
        {
            global $worker;
            foreach($worker->uidConnections as $connection)
            {
                $connection->send($message);
            }
        }


    /**
     * @param $log
     * @param null $data
     */
    function write_log ($log, $logFile = NULL)
        {
            echo  date('Y-m-d H:i:s'). ". $log \n";
    //        $logFile;
        }

    /**
     * @param $uid
     * @param $message
     * @return bool
     * отправка сообщения пользолвателю по uid
     */
    function sendMessageByUid($uid, $message) : bool
        {
            global $worker;
            if(isset($worker->uidConnections[$uid]))
            {
                $connection = $worker->uidConnections[$uid];
                $connection->send($message);
                return true;
            }
            return false;
        }

    /**
     * @param $string
     * @return bool
     * пров
     */
    function is_json($string) : bool
        {
            // decode the JSON data
            $result = json_decode($string);

            // switch and check possible JSON errors
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    $error = ''; // JSON is valid // No error has occurred
                    break;
                case JSON_ERROR_DEPTH:
                    $error = 'The maximum stack depth has been exceeded.';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Invalid or malformed JSON.';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Control character error, possibly incorrectly encoded.';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON.';
                    break;
                // PHP >= 5.3.3
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                    break;
                // PHP >= 5.5.0
                case JSON_ERROR_RECURSION:
                    $error = 'One or more recursive references in the value to be encoded.';
                    break;
                // PHP >= 5.5.0
                case JSON_ERROR_INF_OR_NAN:
                    $error = 'One or more NAN or INF values in the value to be encoded.';
                    break;
                case JSON_ERROR_UNSUPPORTED_TYPE:
                    $error = 'A value of a type that cannot be encoded was given.';
                    break;
                default:
                    $error = 'Unknown JSON error occured.';
                    break;
            }

            if ($error !== '') {
                // throw the Exception or exit // or whatever :)
                return false;
            }

            // everything is OK
            return true;
        }

    Worker::runAll();