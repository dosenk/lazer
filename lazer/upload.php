<?php

require __DIR__ . '/vendor/autoload.php';
include_once "connect.php";
//print_r($_POST);
//
//print_r($_FILES);
set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line) {
    throw new ErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
});

function files_uploaded() {
    // bail if there were no upload forms
    if (empty($_FILES)) return false;
    // check for uploaded files
    $files = $_FILES['uploadedfile']['tmp_name'];
    foreach( $files as $field_title => $temp_name ){
        if( !empty( $temp_name ) && is_uploaded_file( $temp_name )){
            // found one!
            return true;
        }
    }
    // return false if no files were found
    return false;
}
function write_log($log) {
    $file = 'workerman.log';
    file_put_contents($file, date('Y-m-d H:i:s').' - '. $log . "\n", FILE_APPEND);
}
//print_r($_POST);
//echo "<br>";
//print_r($_FILES);
try {
    if (!empty($_FILES)) {


        $target_path = "uploads/";
        $imei = trim($_POST['imei']);

        $file_name = substr($_FILES['uploadedfile']['tmp_name'], 8).'_'.$_FILES['uploadedfile']['name'];

//        $f_type = ['audio', 'contact', 'image', 'text']; in_array($_FILES['uploadedfile']['type'], $f_type) ?
        $file_type = $_FILES['uploadedfile']['type'] ?? 'unknown_type';
        $file_tmp_name = $_FILES['uploadedfile']['tmp_name'];

        $sql = "SELECT id FROM otm WHERE imei = '$imei'";
//        write_log($sql);
        $query = mysqli_query($conn, $sql);
        $id_otm = mysqli_fetch_assoc($query);
        $id = $id_otm['id'];

        $sql2 = "INSERT into `data` (`id_otm`, `data`, `type`) VALUES ('$id', '$file_name', '$file_type')";
//        write_log($sql2);

        if ( mysqli_query($conn, $sql2)) {
            write_log("Receive IMEI: $imei. Data is write in DB");
            $target_path = "uploads/".$file_name;

            if (move_uploaded_file($file_tmp_name, $target_path)) {
                write_log("file saved");
                echo "1";
            } else {
                write_log("dont move file");
            }
        } else {
            write_log("Receive IMEI: $imei. NOT write in DB");
        }
        write_log(json_encode($_POST). ' - ' . json_encode($_FILES) );
    } else {
        write_log("POST does not received");
    }
} catch (\Throwable $e) {
    write_log( "Error: " . $e->getMessage() . ". Line: " . $e->getLine());
}

