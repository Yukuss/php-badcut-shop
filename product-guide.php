<?php
// Запускаем сессию для работы с пользовательскими данными (логин, user_id и т.д.)
session_start();
// Подключаем файл для соединения с базой данных
include 'includes/db_connect.php';

// Получаем ID товара из адресной строки (параметр id) и преобразуем его в число для безопасности
// Если параметр не передан, используем 0 (несуществующий ID)
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Выполняем запрос к базе данных чтобы получить информацию о товаре по его ID
$product_query = mysqli_query($link, "SELECT * FROM products WHERE id = $product_id");

// Проверяем: если запрос не выполнился или товар не найден (0 строк в результате)
if (!$product_query || mysqli_num_rows($product_query) === 0) {
    // Останавливаем выполнение скрипта и выводим сообщение об ошибке
    die('Товар не найден');
}

// Получаем данные о товаре в виде ассоциативного массива
$product = mysqli_fetch_assoc($product_query);

// ОБРАБОТКА ДОБАВЛЕНИЯ НОВОГО КОММЕНТАРИЯ
// Проверяем: если форма была отправлена методом POST и есть текст комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    // Получаем ID родительского комментария (если это ответ на другой комментарий)
    // Если не передан, используем 0 (основной комментарий)
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    // Безопасно обрабатываем текст комментария: экранируем специальные символы для SQL
    $text = mysqli_real_escape_string($link, $_POST['comment_text']);
    
    // Получаем ID пользователя из сессии (пользователь должен быть авторизован)
    $user_id = $_SESSION['user_id'];
    
    // Формируем и выполняем SQL-запрос для добавления нового комментария в базу данных
    // NOW() - текущая дата и время добавления комментария
    mysqli_query($link, "INSERT INTO comments 
        (product_id, user_id, parent_id, text, created_at) 
        VALUES ($product_id, $user_id, $parent_id, '$text', NOW())");
}

// ОБРАБОТКА УДАЛЕНИЯ КОММЕНТАРИЯ
// Проверяем: если в адресной строке есть параметр delete_comment (кнопка "Удалить")
if (isset($_GET['delete_comment'])) {
    // Получаем ID комментария для удаления и преобразуем в число
    $comment_id = intval($_GET['delete_comment']);
    
    // Получаем ID текущего пользователя из сессии
    $user_id = $_SESSION['user_id'];
    
    // Выполняем запрос на удаление комментария
    // Удаляем: либо сам комментарий (id = $comment_id), либо все ответы на него (parent_id = $comment_id)
    // И только если комментарий принадлежит текущему пользователю (user_id = $user_id)
    mysqli_query($link, "DELETE FROM comments 
        WHERE (id = $comment_id OR parent_id = $comment_id) AND user_id = $user_id");
    
    // Перенаправляем пользователя обратно на страницу товара чтобы обновить список комментариев
    header("Location: product.php?id=$product_id");
    
    // Завершаем выполнение скрипта после перенаправления
    exit();
}

// ПОЛУЧЕНИЕ И СТРУКТУРИРОВАНИЕ КОММЕНТАРИЕВ
// Формируем сложный SQL-запрос для получения всех комментариев к товару
$comments_result = mysqli_query($link, 
    "SELECT c.*, u.login as user_login 
     FROM comments c
     LEFT JOIN users u ON c.user_id = u.id  -- Присоединяем таблицу пользователей чтобы получить их логины
     WHERE c.product_id = $product_id       -- Только комментарии к текущему товару
     ORDER BY COALESCE(c.parent_id, c.id), c.created_at");  -- Сортируем: сначала родительские комментарии, затем ответы по времени

// Создаем пустой массив для структурированных комментариев
$comments = [];

// Обрабатываем каждый комментарий из результата запроса
while ($comment = mysqli_fetch_assoc($comments_result)) {
    // Если у комментария нет родителя (parent_id = 0) - это основной комментарий
    if ($comment['parent_id'] == 0) {
        // Добавляем основной комментарий в массив, используя его ID как ключ
        $comments[$comment['id']] = $comment;
        
        // Создаем пустой массив для ответов на этот комментарий
        $comments[$comment['id']]['replies'] = [];
    } else {
        // Если это ответ (есть parent_id) - добавляем его в массив ответов родительского комментария
        $comments[$comment['parent_id']]['replies'][] = $comment;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $product['name'] ?> - Детальная информация</title>
</head>
<body>
    <!-- Подключаем шапку сайта -->
    <?php include 'includes/header.php' ?>
    
    <!-- БЛОК ИНФОРМАЦИИ О ТОВАРЕ -->
    <div class="product-info">
        <h1><?= $product['name'] ?></h1>
        <!-- Отображаем изображение товара -->
        <img class="product-image" src="images/<?= $product['image_url'] ?>" width="200">
        <p>Цена: <?= $product['price'] ?> руб.</p>
        <!-- nl2br - преобразует переносы строк в теги <br> для красивого отображения -->
        <p><?= nl2br($product['description']) ?></p>
    </div>
    
    <!-- СЕКЦИЯ КОММЕНТАРИЕВ -->
    <div class="comments-section">
        <h2>Комментарии покупателей</h2>
        
        <!-- ФОРМА ДОБАВЛЕНИЯ КОММЕНТАРИЯ -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Если пользователь авторизован - показываем форму -->
            <form method="POST" class="comment-form">
                <textarea name="comment_text" required placeholder="Напишите ваш отзыв о товаре..."></textarea>
                <button type="submit">Опубликовать отзыв</button>
            </form>
        <?php else: ?>
            <!-- Если пользователь не авторизован - показываем ссылку для входа -->
            <p>Для добавления комментариев <a href="#" onclick="openAuthModal()">войдите в аккаунт</a></p>
        <?php endif ?>

<!-------------------------------------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------------------------------------->

<!-- СПИСОК КОММЕНТАРИЕВ -->
<div class="comments-list">
    <?php foreach ($comments as $comment): 
        // Проверяем: является ли текущий пользователь владельцем этого комментария
        $is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id'] ?>
        
        <!-- ОТДЕЛЬНЫЙ КОММЕНТАРИЙ -->
        <div class="comment">
            <!-- ШАПКА КОММЕНТАРИЯ - информация об авторе и времени -->
            <div class="comment-header">
                <!-- Имя пользователя который оставил комментарий -->
                <strong><?= $comment['user_login'] ?></strong>
                <!-- Дата и время комментария в формате ДД.ММ.ГГГГ ЧЧ:ММ -->
                <span><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                
                <!-- КНОПКИ ДЕЙСТВИЙ (только для владельца комментария) -->
                <?php if ($is_owner): ?>
                    <div class="comment-actions">
                    <!-- Ссылка для удаления комментария с подтверждением через JavaScript -->
                    <a href="product.php?id=<?= $product_id ?>&delete_comment=<?= $comment['id'] ?>" 
                       class="delete-btn" 
                       onclick="return confirm('Вы уверены что хотите удалить этот комментарий и все ответы на него?')">
                       Удалить
                    </a>
                    </div>
                <?php endif ?>
            </div>
            
            <!-- ТЕКСТ КОММЕНТАРИЯ -->
            <p><?= htmlspecialchars($comment['text']) ?></p>
            
            <!-- КНОПКА ОТВЕТА (только для авторизованных пользователей) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- При клике показываем форму ответа -->
                <button onclick="document.getElementById('reply-<?= $comment['id'] ?>').style.display='block'">
                    Ответить
                </button>
                
                <!-- ФОРМА ОТВЕТА НА КОММЕНТАРИЙ (изначально скрыта) -->
                <form method="POST" id="reply-<?= $comment['id'] ?>" style="display:none;">
                    <!-- Скрытое поле с ID родительского комментария -->
                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                    <textarea name="comment_text" required placeholder="Напишите ваш ответ..."></textarea>
                    <button type="submit">Отправить ответ</button>
                </form>
            <?php endif ?>
                    
<!-------------------------------------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------------------------------------->

            <!-- БЛОК ОТВЕТОВ НА КОММЕНТАРИЙ -->
            <?php if (!empty($comment['replies'])): ?>
                <div class="replies-container">
                <?php foreach ($comment['replies'] as $reply): 
                    // Проверяем: является ли текущий пользователь владельцем ответа
                    $is_reply_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id'] ?>
                    
                    <!-- ОТДЕЛЬНЫЙ ОТВЕТ -->
                    <div class="comment reply">
                        <div class="comment-header">
                            <strong><?= $reply['user_login'] ?></strong>
                            <span><?= date('d.m.Y H:i', strtotime($reply['created_at'])) ?></span>
                            
                            <!-- КНОПКИ ДЕЙСТВИЙ ДЛЯ ОТВЕТА -->
                            <?php if ($is_reply_owner): ?>
                                <div class="comment-actions">
                                    <a href="product.php?id=<?= $product_id ?>&delete_comment=<?= $reply['id'] ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Удалить этот ответ?')">
                                       Удалить
                                    </a>
                                </div>
                            <?php endif ?>
                        </div>
                        <p><?= htmlspecialchars($reply['text']) ?></p>
                    </div>
                <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>
    <?php endforeach ?>
</div>
    </div>
    
    <!-- Подключаем подвал сайта -->
    <?php include 'includes/footer.php' ?>
</body>
</html>