<?php

    if (!empty($_POST)) {

        $login = $_POST['login'];
        $password = $_POST['password'];

        session_start();

        $db = new PDO(
            'mysql:host=laradock_mariadb_1;port=3306;dbname=lazer',
            'root',
            '987654321As!');
        $stb = $db->query("SELECT `id`, `login`, `session_status`
                                     FROM `users` 
                                     WHERE `login` = '$login' AND `pass` = '$password'");

        $row = $stb->fetch(PDO::FETCH_ASSOC);
//        print_r($stb->rowCount());
        if ($row['session_status'] == 1) {
            $_SESSION['msg'] = 'Невозможно зайти под данным пользователем. Пользователь уже в сети';
            header('Location: login.php');
        } elseif ($stb->rowCount() >= 1) {
            $id = $row['id']; //id порядковый номер в таблице users
            $_SESSION['id'] = $id;
            setcookie("web_socket_id", $id, 0);
            $sql = "UPDATE `users` SET `session_status` = '1' WHERE `id` = '$id'";
            if ($db->query($sql)) {
                header('Location: app.php');
            } else {
                $_SESSION['msg'] = "Неудалось зайти. Повторите попытку через 10 сек";
                header('Location: login.php');
            }
        } else {
            $_SESSION['msg'] = 'Неверный логин или пароль';
            header('Location: login.php');
        }


    } else {
        header('Location: app.php');
    }




