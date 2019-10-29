<?php

session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Авторизация и регистрация</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<form action="signup.php" method="POST">
    <label>ФИО</label>
        <input type="text" name="full_name" placeholder="Введите свое полное имя">
    <label>Логин</label>
        <input type="text" name="login" max="8" placeholder="Введите свой логин">
    <label>Пароль</label>
        <input type="password" name="password" placeholder="Введите свой пароль">
    <label>Подтверждение пароля</label>
        <input type="password" name="password_confirm" placeholder="Подтвердите пароль">
    <button type="submit">Войти</button>
    <p>
        У вас уже есть аккаунт <a href="login.php">авторизируйтесь</a>
    </p>

    <?php
        if ($_SESSION['msg']) {
            echo '<p class="msg">' . $_SESSION['msg'] . ' </p>';
        } elseif ($_COOKIE["web_socket_id"]) {
            header('Location: app.php');
        }
        unset($_SESSION['msg']);
    ?>

</form>
</body>
</html>
