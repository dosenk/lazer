<?php
session_set_cookie_params(0);
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
        <form action="signin.php" method="POST">
            <label>Логин</label>
            <input type="text" name="login" max="8" placeholder="Введите свой логин">
            <label>Пароль</label>
            <input type="password" name="password" placeholder="Введите свой пароль">
            <button type="submit">Войти</button>
            <p>
                <a href="registr.php">Регистрация</a>
            </p>

            <?php
            if (!empty($_SESSION['msg'])) {
                echo '<p class="msg">' . $_SESSION['msg'] . ' </p>';
            } elseif (!empty($_COOKIE["web_socket_id"])) {
                header('Location: app.php');
            }
            unset($_SESSION['msg']);

            ?>
        </form>
    </body>
</html>

