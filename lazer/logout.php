<?php

if ($_GET['logout'] == 1) {
    session_start();
    $id = $_SESSION['id'];
    try {
        $db = new PDO(
            'mysql:host=laradock_mariadb_1;port=3306;dbname=lazer',
            'root',
            '987654321As!');
        $sql = "UPDATE `users` SET `session_status` = '0' WHERE `id` = '$id'";
        if ($db->query($sql)) {
            header('Location: app.php');
        }
    } catch (PDOException $e) {
        print_r($e->getMessage());
    }

    unset($_SESSION['id']);
    setcookie("web_socket_id","web_socket_id",time()-1);
}

header("Location: login.php");
