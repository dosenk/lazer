<?php
    include_once "connect.php";
    if(!$conn)
    {
        die('server not connected');
    }
    if (!empty($_GET['id'])) {
        $webSender = $_GET['id'];
    }
//    echo $webSender;
    $query_otm = "SELECT * FROM otm_view";
    $query_loc = "SELECT * FROM location order by active desc ";
    $query_active_user = "SELECT distinct id_otm, user FROM activeLocation";
    //$query_audio= "SELECT * FROM audio";


    $r = mysqli_query($conn, $query_active_user);
    while ($row = mysqli_fetch_assoc($r)) {
        $id_otm_users[] = [(int)$row['id_otm'] => $row['user']];
    }
//    print_r($id_otm_users);
//    echo "<br>";


    $r_otm = mysqli_query($conn, $query_otm);
    $arr = ['active' => 1];

    while ($row1 = mysqli_fetch_assoc($r_otm)) {
        foreach ($id_otm_users as $key => $value ) {
            if (array_key_exists((int)$row1['id'], $value) && $webSender == $value[(int)$row1['id']]) {
                $row1 = array_merge($row1, $arr);
            }
        }
//        print_r($row1);
//        echo "<br>";
        $rows_otm[] = $row1;

    }

//    print_r($rows_otm);
//    $r_location = mysqli_query($conn, $query_loc);
//    while ($row2 = mysqli_fetch_assoc($r_location)) {
//        $rows_loc[] = $row2;
//    }
    //var_dump($rows_loc);
    // $r_audio = mysqli_query($conn, $query_audio);
    // while ($row3 = mysqli_fetch_assoc($r_audio)) {
    //     $rows_audio[] = $row3;
    // }

    print json_encode($rows_otm);
    //print json_encode($rows_loc);
    mysqli_close($conn);
