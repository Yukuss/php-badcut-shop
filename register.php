<?php
session_start();
include_once 'includes/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($login)) {
        $errors['login'] = 'Введите логин';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $login)) {
        $errors['login'] = 'Логин должен содержать 3-20 символов (лат. буквы, цифры, _)';
    }
    if (empty($email)) {
        $errors['email'] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email';
    }
    if (empty($phone)) {
        $errors['phone'] = 'Введите телефон';
    } elseif (!preg_match('/^\+375?[0-9\s\-]{9}$/', $phone)) {
        $errors['phone'] = 'Телефон должен содержать 9 цифр и +375';
    }
    if (empty($password)) {
        $errors['password'] = 'Введите пароль';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $password)) {
        $errors['password'] = 'Пароль должен содержать только лат. буквы, цифры и _ ( и не менее 4 символов)';
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (empty($errors)) {
        $stmt = mysqli_prepare($link, "SELECT id FROM users WHERE login = ? OR email = ? OR phone = ?");
        mysqli_stmt_bind_param($stmt, "sss", $login, $email, $phone);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $errors['login'] = 'Логин, email или телефон уже заняты';
        }
        else {
            $hashedPassword = md5($password);
            $stmt = mysqli_prepare($link, "INSERT INTO users (login, email, phone, pass_hash) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $login, $email, $phone, $hashedPassword);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_id'] = mysqli_insert_id($link);
                $_SESSION['login'] = $login;
                header('Location: index.php');
                exit;
            } else {
                $errors['general'] = 'Не вставилось в БД';
            }
        }
        mysqli_stmt_close($stmt);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!empty($errors)) {
    echo '<script>alert("';
    foreach ($errors as $error) {
        echo $error."\\n";
    }
    echo '");</script>';
    echo '<script>history.back();</script>';
    exit;
}

?>