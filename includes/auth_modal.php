<?php
session_start();
include 'includes/db_connect.php';?>

<div id="authModal" class="modal" style="display: none;">
<div class="modal-content">
<span class="close" onclick="closeModal()">X</span>
<div id="loginForm">
    <h2>Вход</h2>
    <form action="login.php" method="POST">
        <div class="form-group">
         Логин:<br>
         <input type="text" name="login" required>
        </div>
        <div class="form-group">
        Пароль:<br>
        <input type="password" name="password" required>
        </div>
        <button type="submit">Войти</button>
    </form>
    <p class="toggle-form">Нет аккаунта? <a href="#" onclick="showForm('registerForm')">Зарегистрируйтесь</a></p>
</div>




<div id="registerForm" style="display:none;">
    <h2>Регистрация</h2>
    <form action="register.php" method="POST">
        <div class="form-group">
             Логин:<br>
           <input type="text" name="login" required>
        </div>
        <div class="form-group">
             Почта:<br>
           <input type="email" name="email" placeholder="primer@mail.ru" required>
        </div>
        <div class="form-group">
             Телефон:<br>
           <input type="phone" name="phone" placeholder="+375111111111" required>
        </div>
        <div class="form-group">
             Пароль:<br>
           <input type="password" name="password" required>
        </div>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p class="toggle-form">Есть аккаунт? <a href="#" onclick="showForm('loginForm')">Войти</a></p>
</div>
</div>
</div>



<script>
function showForm(formId) {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById(formId).style.display = 'block';
}

function openAuthModal() {
    document.getElementById('authModal').style.display = 'block';
    showForm('loginForm');
}

function closeModal() {
    document.getElementById('authModal').style.display = 'none';
}
</script>