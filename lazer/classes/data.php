<?php
namespace Classes;

class Data {
    public $imei;
    public $work_mode;
    public $location_interval;
    public $duration_record;
    public $longitude;
    public $latitude;
    public $latlon; //array([lat1, lon1, datetime1],[lat2, lon2, datetime2])

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


    /**
     * @return false|string
     * возвращает одну точку
     */
    public function send_GeoJSON_point()
    {
        return json_encode(array(
            'type' => 'Feature',
            'properties' => ['info' => $this->imei],
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
    public function __construct()
    {
//        if (is_array(json_decode($data, true))) {
//
//            $data = json_decode($data, true);
//            $this->imei = $this->search_key('imei', $data);
//            $this->work_mode = $this->search_key('work_mode', $data);
//            $this->location_interval = $this->search_key('location_interval', $data);
//            $this->duration_record = $this->search_key('duration', $data);
//            $this->latitude = (float)$this->search_key('latitude', $data);
//            $this->longitude = (float)$this->search_key('longitude', $data);
//
//        } else {
//            return;
//        }

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