<?php

include_once "connect.php";


function write_log($log) {
    $file = 'workerman.log';
    file_put_contents($file, date('Y-m-d H:i:s').' - '. $log . "\n", FILE_APPEND);
}

$date = new DateTime();
$export_end_date = $_POST['end_date'] ?? $date->sub(DateInterval::createFromDateString('1 day'))->format('Y-m-d 23:59:59'); //'2019-10-25 23:59:59';
$export_start_date = $_POST['start_date'] ?? $date->format('Y-m-d'); //'2019-04-25';
$sql = "SELECT id, object, imei FROM lazer.otm WHERE work_mode BETWEEN '1' AND '2'"; //'$export_start_date' >= start_date AND '$export_end_date' <= end_date";
//echo $export_end_date.'-'.$export_start_date."<br>";
$result = $conn->query($sql);
//var_dump($result->fetch_assoc());
while ($row1 = $result->fetch_assoc()) {
//    var_dump($row1);
    $first_day = new DateTime($export_start_date);
    $last_day =  new DateTime($export_end_date);
    $interval = $last_day->diff($first_day);
    $day = $interval->format('%a');
//    echo "IMEI - ".$row1['imei'].". $day - num day <br>";
    for ($i = 0; $i <= $day; $i++)
    {
        $date = $first_day->format('Y-m-d');
//        echo $date.'<br>';
        $path = "/var/www/lazer/AUTO_EXP/".$row1['object']."/".$row1['imei']."/$date";
//        echo $path;
        $sql = "SELECT data, type, timestamp FROM lazer.data WHERE id_otm = ".$row1['id']." AND timestamp LIKE '$date%' ";
//        echo $sql. "<br>";
        $data = $conn->query($sql);
//            var_dump($data->num_rows);
        if (!empty($data->num_rows)){
            while ($row = $data->fetch_assoc()) {
//                var_dump($row);
                $dataType = $row['type'] ?? 'undefined type DATA';
//                echo $dataType;
                $file = '/var/www/lazer/uploads/'. $row['data'];
                if (file_exists($file))
                {
                    if (!file_exists($path . '/' . $dataType)) mkdir($path . '/' . $dataType, 0777, true);
                    copy($file, $path. '/' . $dataType.'/'.$row['data']);
                }

            }

            $log = "Экспорт по объектру - ".$row1['object']." прошел успешно! За дату: " .$date;
//            write_log($log);
            echo date('Y-m-d H:i:s').' - '. $log . "\n";
        }
        $first_day->add(new DateInterval('P1D'));
    }
}

