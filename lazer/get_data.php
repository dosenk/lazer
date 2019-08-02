<?php
    include_once "connect.php";
    if(!$conn)
    {
        die('server not connected');
    }
    $query_otm = "SELECT * FROM otm_view";
    $query_loc = "SELECT * FROM location order by active desc ";
    //$query_audio= "SELECT * FROM audio";

    $r_otm = mysqli_query($conn, $query_otm);
    while ($row1 = mysqli_fetch_assoc($r_otm)) {
        $rows_otm[] = $row1;
    }

    $r_location = mysqli_query($conn, $query_loc);
    while ($row2 = mysqli_fetch_assoc($r_location)) {
        $rows_loc[] = $row2;
    }
    //var_dump($rows_loc);
    // $r_audio = mysqli_query($conn, $query_audio);
    // while ($row3 = mysqli_fetch_assoc($r_audio)) {
    //     $rows_audio[] = $row3;
    // }
    print json_encode($rows_otm);
    //print json_encode($rows_loc);
    mysqli_close($conn);
