<?php
//print_r($_POST);
session_set_cookie_params(0);
session_start();
if (!empty($_POST['full_name']) && !empty($_POST['login']) && !empty($_POST['password'])) {
    $full_name = $_POST['full_name'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password === $password_confirm) {
        $db = new PDO(
            'mysql:host=laradock_mariadb_1;port=3306;dbname=lazer',
            'root',
            '987654321As!');
        $sql = "SELECT `login` FROM `users`";
        $stb = $db->query($sql);

        while ($row = $stb->fetch()) {
            if (array_key_exists($login, array_flip($row))) {
                $_SESSION['msg'] = 'Логин уже существует.';
                 header('Location: registr.php');
                 break;
            }
        }

        $sql = "INSERT INTO `users` (`user`, `login`, `pass`, `session_status`, `last_session`)
                VALUES ('$full_name', '$login', '$password', '0', now());";
        if ($db->query($sql)) {
            $_SESSION['msg'] = 'Регистрация прошла успешно! ';
            header('Location: login.php');
        }
    } else {
        $_SESSION['msg'] = 'пароли не совподают';
        header('Location: registr.php');
    }
} else {
    $_SESSION['msg'] = 'Заполните все поля';
    header('Location: registr.php');
}
