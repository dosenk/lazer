<?php
    use Workerman\Worker;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
    require __DIR__ . '/vendor/autoload.php';

    class Data {
        public $imei;
        public $work_mode;
        public $location_interval;
        public $duration_record;
        public $longitude;
        public $latitude;

        /**
         * @param $searchKey
         * @param array $arr
         * @return string
         * ищет в массиве ключ м возвращает его значение
         */
        protected function search_key($searchKey, $arr): string
        {
            // Если в массиве есть элемент с ключем $searchKey, то ложим в результат
            if (isset($arr[$searchKey])) {
                return $result = $arr[$searchKey];
            }
        // Обходим все элементы массива в цикле
            foreach ($arr as $key => $param) {
                // Если эллемент массива есть массив, то вызываем рекурсивно эту функцию
                if (is_array($param)) {
                    search_key($searchKey, $param);
                }
                return "";
            }
        }

        public function prepare_data()
        {
            return  json_encode(['imei'=> $this->imei,
                                 'wm'  => $this->work_mode,
                                 'mode' => [ 'location' =>
                                              ['interval' => $this->location_interval,
                                              'latitude' => $this->latitude,
                                              'longitude' => $this->longitude],
                                             'voice'    =>
                                              ['duration' => $this->duration_record]
                                           ]
                                ]);
        }

        public function send_loc()
        {
            return json_encode(array(
                'type' => 'Feature',
                'properties' => ['info' => $this->imei],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$this->longitude, $this->latitude]]
            ));
        }


        public function __construct($data)
        {
            if (is_array(json_decode($data, true))) {

                $data = json_decode($data, true);
                $this->imei = $this->search_key('imei', $data);
                $this->work_mode = $this->search_key('work_mode', $data);
                $this->location_interval = $this->search_key('location_interval', $data);
                $this->duration_record = $this->search_key('duration', $data);
                $this->latitude = $this->search_key('latitude', $data);
                $this->longitude = $this->search_key('longitude', $data);

            } else {
                return;
            }

        }

    }

    $worker = new Worker('tcp://0.0.0.0:19222');
    $worker->count = 1;
    $worker->uidConnections = array();

    $worker->onWorkerStart = function($worker)
    {
        echo "worker->id={$worker->id}\n";
    };

    $worker->onConnect = function($connection)
        {
            echo "New connection tcp \n";
            echo $connection->id. " - connection id \n";
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
    $worker->onMessage = function($connection, $data)
            {
                global $worker;
                $db = new \Workerman\MySQL\Connection(
                    'laradock_mariadb_1',
                    '3306',
                    'root',
                    '987654321As!',
                    'lazer');

                if(!isset($connection->uid) ) {

                    $imei = trim($data);
                    if ($imei == 'web') {
                        $connection->uid = $imei; // это будет imei
                        $worker->uidConnections[$imei] = $connection;
                        echo $connection->uid . " - connection uid (login) \n";
                        sendMessageByUid($imei, "test data");
                        print_r(array_key_first($worker->uidConnections));
                        return;
                    }

                    $sql = "SELECT `otm_view`.`imei`, `otm_view`.`work_mode`, `otm_view`.`location_interval`, `otm_view`.`duration`
                            FROM `lazer`.`otm_view` 
                            WHERE `otm_view`.`imei` 
                            LIKE '$imei'";

                    $data_sql = $db->query($sql, null, PDO::FETCH_ASSOC);

                    if (!empty($data_sql)) {
                        $data = new Data(json_encode($data_sql[0]));
                        //print_r($data);
                    } else {
                        write_log("Object is not controlled. Imei: ", $imei);
                       return;
                    }

                    if ($data->imei == $imei){
                        $connection->uid = $imei; // это будет imei
                        $worker->uidConnections[$imei] = $connection;
                        echo $connection->uid . " - connection uid (login) \n";
                        //sleep(10);
                        sendMessageByUid($imei, $data->prepare_data(). "\n");
                    }

                    return;

                } elseif ($connection->uid == 'web') {
//                    print_r($data);
                    $imei = trim($data);
                    $sql = "SELECT `otm_view`.`imei`, `otm_view`.`work_mode`, `otm_view`.`location_interval`, `otm_view`.`duration`
                            FROM `lazer`.`otm_view` 
                            WHERE `otm_view`.`imei` 
                            LIKE '$imei'";
                    $data_sql = $db->query($sql, null, PDO::FETCH_ASSOC);
                    $data = new Data(json_encode($data_sql[0]));
                    $message = sendMessageByUid($imei, $data->prepare_data(). "\n") ? "Data send" : "Data dont send. Socket not connected";
//                    print_r($message);
                    sendMessageByUid('web', $message);
                    return;

                } else {
                    print_r($data);
                    echo "\n";
                    if (is_json($data)) {
                        $data = new Data($data);

                        if (!empty($data->longitude) && !empty($data->latitude)) {
                            $sql = "INSERT into `lazer`.`location`
                            (`location`.`id_otm`, `location`.`latitude`, `location`.`longitude`) 
                            VALUES ((SELECT otm_view.id FROM otm_view WHERE otm_view.imei = '$data->imei'), '$data->latitude', '$data->longitude')";

                            if ($db->query($sql, null, PDO::FETCH_ASSOC)) {
                                echo "данные записаны в БД \n";
                                $sql = "SELECT DISTINCT user FROM activeLocation WHERE id_otm = (SELECT id FROM otm WHERE imei = '$data->imei')";
                                $g_data = $db->query($sql);
                                //print_r($g_data);
                                foreach ($g_data as $value) {
                                    sendMessageByUid($value['user'], $data->send_loc());
                                    //echo $value['user']. ' - ' . $data->send_loc();
                                }



                            }
                        } else {
                                write_log("Координаты не получены. Принятые данные: ", json_encode($data));
                        }
                     }else {
                        write_log("Неправильный формат данных или данные не пришли. Data: ", $data);
                    }
                    return;
                };
            };


    $worker->onClose = function($connection)
    {
        global $worker;
        if(isset($connection->uid))
        {
            echo "connections ".$connection->uid." closed\n\r";
            unset($worker->uidConnections[$connection->uid]);
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
     * @param $imei
     * пишет логи
     */
    function write_log ($log, $imei)
        {
            echo  $log . $imei."\n";
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