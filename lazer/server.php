<?php
    use Classes\Data;
    require __DIR__ . '/vendor/autoload.php';
    include_once "connect.php";

//   print_r($_POST);

try {
    if (!empty($_POST)) {
        $imei = $_POST['imei'] ?? '';
        $user = $_POST['webSender'] ?? '';
        $date_start = $_POST['date_start'] ?? '';
        $date_end = $_POST['date_end'] ?? '';
        $table_name = $_POST['table_name'] ?? '';
        $location_interval = $_POST['location_interval'] ?? '';
        $duration = $_POST['duration'] ?? '';

        if ($_POST['action'] == "insert") {
            switch ($table_name){
                case 'activeLocation':
                    $sql = "INSERT INTO `activeLocation` (`id_otm`, `user`) 
                            VALUES ((select id from otm where imei = '$imei'), '$user')";
                    break;
            }
            if (mysqli_query($conn, $sql)) {
                echo "Data write in DB";
            } else {
                echo "error write data";
            }
        } elseif ($_POST['action'] == "delete") {
            $sql = "DELETE FROM `activeLocation` WHERE id_otm = (select id from otm where imei = '$imei') AND user = '$user'";
            if (mysqli_query($conn, $sql)) {
                echo "Data delete from DB";
            } else {
                echo "error delete data";
            }
        } elseif ($_POST['action'] == "select") {

            switch ($table_name) {
                case 'location':
                    $sql = "SELECT lazer.location.latitude, lazer.location.longitude, lazer.location.timestamp
                    FROM location 
                    WHERE id_otm = 
                          (select id from otm where imei = '$imei') 
                      AND timestamp
                          BETWEEN '$date_start:00' 
                          AND '$date_end:59'";
//                    echo $sql;
                    $query = mysqli_query($conn, $sql);
                    if (!empty($query->num_rows)) {
                        $data = new Data($imei);
                        while ($row = mysqli_fetch_assoc($query))
                        {
                            $data->setLatlon(
                                [
                                    (double)$row['latitude'], (double)$row['longitude']
                                ]
                            );
                        }
                        print_r($data->send_GeoJSON_line());
                    }
                    break;
            }



        } elseif ($_POST['action'] == "update") {
            switch ($table_name) {
                case 'otm_loc':
                    $sql = "UPDATE `otm` SET `location_interval` = '$location_interval'
                    WHERE `imei` = '$imei'";
                    break;
                case 'otm_voice':
                    $sql = "UPDATE `otm` SET `duration` = '$duration'
                    WHERE `imei` = '$imei'";
            }
            if (mysqli_query($conn, $sql)) {
                echo "Data update in DB";
            } else {
                echo "error update data";
            }
        }
    } else {
        echo "Dont recive POST";
    }
} catch (\Throwable $e) {
    echo $e->getMessage();
}



