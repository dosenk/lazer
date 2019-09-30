<?php
    use Classes\Data;
    require __DIR__ . '/vendor/autoload.php';
    include_once "connect.php";

   //print_r($_POST);


    if (!empty($_POST)) {
        $imei = $_POST['imei'] ?? '';
        $user = $_POST['sender'] ?? '';
        $date_start = $_POST['date_start'] ?? '';
        $date_end = $_POST['date_end'] ?? '';
        if ($_POST['action'] == "insert") {
            $sql = "INSERT INTO `activeLocation` (`id_otm`, `user`) VALUES ((select id from otm where imei = '$imei'), '$user')";
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
            $sql = "SELECT lazer.location.latitude, lazer.location.longitude, lazer.location.timestamp
                    FROM location 
                    WHERE id_otm = 
                          (select id from otm where imei = '$imei') 
                      AND timestamp
                          BETWEEN '$date_start 00:00:00' 
                          AND '$date_end 23:59:59'";
                $query = mysqli_query($conn, $sql);
//                echo $sql;
                try {
                    $data = new Data();
                    $data->$imei = $imei;
                    while ($row = mysqli_fetch_assoc($query))
                    {
                        $data->setLatlon(
                            [
                                (double)$row['latitude'], (double)$row['longitude']
                            ]
                        );
                    }
                    print_r($data->send_GeoJSON_line());
                } catch (\Throwable $e) {
                    echo $e->getMessage();
                }



        }
    } else {
        echo "Dont recive POST";
    }



