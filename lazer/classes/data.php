<?php
namespace Classes;

class Data {
    public $imei;
    public $work_mode;
    public $location_interval;
    public $duration_record;
//    public $battery;
//    public $speed;
    public $longitude;
    public $latitude;
    public $latlon; //array([lat1, lon1, datetime1],[lat2, lon2, datetime2])

    public function __construct(
        $imei = NULL,
        $work_mode = NULL,
        $location_interval = NULL,
        $duration_record = NULL,
        $duration_start_time = NULL)
    {
        $this->imei = $imei;
        $this->work_mode = $work_mode;
        $this->location_interval = $location_interval;
        $this->duration_record = $duration_record;
        $this->duration_start_time = $duration_start_time;
    }

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


    public function prepare_data(array $webSender = NULL, $mode = NULL)
    {
        return  json_encode(['imei'=> $this->imei,
            'webSender' => $webSender,
            'wm'  => $this->work_mode,
            'mode' => [ 'location' =>
                            ['interval' => $this->location_interval],
                        'voice'    =>
                            ['duration' => $this->duration_record,
                             'start_time' => $this->duration_start_time],
                        'getFiles' => $mode
                      ]
        ]);
    }

//    public function getFiles($mode) // 1 - получить все файлы
//    {
//        return  json_encode(['imei'=> $this->imei,
////            'webSender' => $webSender,
//            'wm'  => $this->work_mode,
//            'mode' => [ 'getFiles' => $mode]
//        ]);
//    }


    public function getActiveUsers($connection, $imei):array
    {
        $sql = "SELECT DISTINCT user FROM activeLocation WHERE id_otm = (select id from otm where imei = '$imei')";
        if ($u_data = $connection->query($sql)) {
            foreach ($u_data as $key=>$value) {
                $webUsers[] = $value['user'];
            }
        } else {
            $webUsers = [];
        }
        return $webUsers;
    }


    public function send_data_client()
    {
        return  json_encode(
            [
            'imei'=> $this->imei,
            'type' => 'device_status',
            'battery'  => $this->battery,
            'space' => $this->space,
            'datetime' => $this->datetime
            ]
        );
    }
    /**
     * @return false|string
     * возвращает одну точку
     */
    public function send_GeoJSON_point($deviation = NULL, $speed = NULL, $datetime = NULL)
    {
        return json_encode(array(
            'type' => 'Feature',
            'properties' => ['info' => $this->imei,
                             'deviation' => (int)$deviation,
                             'speed' => $speed,
                             'datetime' => $datetime],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this->latitude, $this->longitude]]
        ));
    }

//    /**
//     * @return false|string
//     * возвращает несколько точек
//     */
//    public function send_GeoJSON_multipoint()
//    {
//        return json_encode(array(
//            'type' => 'Feature',
//            'properties' => ['info' => $this->imei],
//            'geometry' => [
//                'type' => 'Point',
//                'coordinates' => [$this->latlon]]
//        ));
//    }

    /**
     * @return false|string
     * возвращает линию
     */
    public function send_GeoJSON_line()
    {
        return json_encode(
            [
            'type' => 'Feature',
            'properties' => ['info' => $this->imei],
            'geometry' =>
                [
                'type' => 'LineString',
                'coordinates' => $this->latlon
                ]
            ]
        );
    }

    /**
     * @param mixed $latlon
     */
    public function setLatlon($latlon): void
    {
        $this->latlon[] = $latlon;
    }

    /**
     * @return mixed
     */
    public function getLatlon()
    {
        return $this->latlon;
    }

}

