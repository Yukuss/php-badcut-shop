<?php
session_start();
include_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $errors = [];
    if (empty($login)) {
        $errors['login'] = 'Введите логин';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $login)) {
        $errors['login'] = 'Логин должен содержать 3-20 символов (буквы, цифры, _)';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Введите пароль';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,}$/', $password)) {
        $errors['password'] = 'Пароль должен содержать только лат. буквы, цифры и _ ( и не менее 4 символов)';
    }

    if (empty($errors)) {
        $hashedPassword = md5($password);
        $stmt = mysqli_prepare($link, "SELECT id, login FROM users WHERE login = ? AND pass_hash = ?");
        mysqli_stmt_bind_param($stmt, "ss", $login, $hashedPassword);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $user['login'];
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Неверный логин или пароль';
        }
        mysqli_stmt_close($stmt);
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!empty($errors)) {
        echo '<script>alert("';
        foreach ($errors as $error) {
            echo $error."\\n";
        }
        echo '");</script>';
        echo '<script>history.back();</script>';
        exit;
    }
}
?>