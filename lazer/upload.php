<?php
print_r($_POST);

print_r($_FILES);
//
//function files_uploaded() {
//    // bail if there were no upload forms
//    if (empty($_FILES)) return false;
//    // check for uploaded files
//    $files = $_FILES['uploadedfile']['tmp_name'];
//    foreach( $files as $field_title => $temp_name ){
//        if( !empty( $temp_name ) && is_uploaded_file( $temp_name )){
//            // found one!
//            return true;
//        }
//    }
//    // return false if no files were found
//    return false;
//}
//
//
//if (isset($_POST['imei'])) {
//    print_r($_POST);
//    $target_path = "uploads/";
//    $imei = $_POST['imei'];
//    $id = $_FILES['uploadedfile']['name'];
//    $rest = substr($id, 20);
//    $end = substr($rest, 0, -4);
//    $datePHP = date("Y-m-d/");
//
//
//    $target_path = $target_path . $end . "/" . "Audios/" . $datePHP;
//
//    if (file_exists($target_path)) {
//        //$target_path=$target_path.$id.$datePHP;
//        /* Add the original filename to our target path.
//        Result is "uploads/filename.extension" */
//        $target_path = $target_path . basename($imei . '_' . $_FILES['uploadedfile']['name']);
//
//        if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
//            //saveAudio($target_path);
//
//            echo "The file " . basename($imei . '_' . $_FILES['uploadedfile']['name']) .
//                " has been uploaded";
//            chmod("uploads/" . basename($imei . '_' . $_FILES['uploadedfile']['name']), 0644);
//        } else {
//            echo "There was an error uploading the file, please try again!";
//            echo "filename: " . basename($_FILES['uploadedfile']['name']);
//            echo "target_path: " . $target_path;
//
//        }
//    } else {
//        $crDir = createDirectory($datePHP);
//    }
//    /*
//    function saveAudio($fileName){
//        require_once 'connect.php';
//        if(!$conn)
//        {
//            die('server not connected');
//        }
//
//        //$query="INSERT INTO audios(filename) VALUES ('$fileName')";
//
//        mysqli_query($conn,$query);
//
//        if(mysqli_affected_rows($conn)>0)
//        {
//            echo "audio file path saved in database";
//        }
//        mysqli_close($conn);
//    }
//    */
//} else {
//    echo "govno is write";
//    print_r($_POST);
//};
//
//function createDirectory($directoryName){
//    $target_path = "uploads/";
//    $id = $_FILES['uploadedfile']['name'];
//    $rest = substr($id, 20);
//    $end = substr($rest, 0, -4);
//    $target_path = $target_path.$end."/"."Audios/".$directoryName;
//    $dir = mkdir("$target_path/",0777);
//    $target_path =  $target_path.basename( $_FILES['uploadedfile']['name']);
//
//    if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
//        //saveAudio($target_path);
//
//        echo "The file ".  basename( $_FILES['uploadedfile']['name']).
//            " has been uploaded";
//        chmod ("uploads/".basename( $_FILES['uploadedfile']['name']), 0644);
//    } else{
//        echo "There was an error uploading the file, please try again!";
//        echo "filename: " .  basename( $_FILES['uploadedfile']['name']);
//        echo "target_path: " .$target_path;
//
//    }
//}
