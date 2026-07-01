<?php
session_start();
include 'includes/db_connect.php';
$query = "SELECT id, title, url, parent_id FROM menu ORDER BY parent_id, id";
$result = mysqli_query($link, $query);

$menuItems = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['parent_id'] === NULL) {
            $menuItems[$row['id']] = $row;
            $menuItems[$row['id']]['children'] = [];
        } else {
            $menuItems[$row['parent_id']]['children'][] = $row;
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Bad Cut</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap');
    </style>
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
<header>
    <div class="logo"><a class="image" href="index.php"><img src="images/logo.png" alt="logo"></a></div>
<nav>
    <ul class="navbar">
        <?php
        foreach ($menuItems as $item) {
            echo '<li class="' . (!empty($item['children']) ? 'dropdown' : '') . '">';
            echo '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
            
            if (!empty($item['children'])) {
                echo '<ul class="dropdown-content">';
                foreach ($item['children'] as $child) {
                    echo '<li><a href="' . $child['url'] . '">' . $child['title'] . '</a></li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        }
        ?>
    </ul>
    <?php
    if (isset($_SESSION['user_id'])) {
        echo '<span>Вы вошли как ' . $_SESSION['login'] . '.</span><a href="logout.php">Выйти?</a>';
    } else {
        echo '<button onclick="openAuthModal()">Войти</button>';
    }
    ?>
</nav>
</header>

    <?php include 'auth_modal.php'; ?>

