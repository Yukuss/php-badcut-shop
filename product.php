<?php
session_start();
include 'includes/db_connect.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$product_query = mysqli_query($link, "SELECT * FROM products WHERE id = $product_id");
if (!$product_query || mysqli_num_rows($product_query) === 0) die('Товар не найден');
$product = mysqli_fetch_assoc($product_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    $text = mysqli_real_escape_string($link, $_POST['comment_text']);
    $user_id = $_SESSION['user_id'];
    
    mysqli_query($link, "INSERT INTO comments 
        (product_id, user_id, parent_id, text, created_at) 
        VALUES ($product_id, $user_id, $parent_id, '$text', NOW())");
}

if (isset($_GET['delete_comment'])) {
    $comment_id = intval($_GET['delete_comment']);
    $user_id = $_SESSION['user_id'];
    
    mysqli_query($link, "DELETE FROM comments 
        WHERE id = $comment_id OR parent_id = $comment_id");
    
    $orphaned_comments = mysqli_query($link, 
        "SELECT c1.id 
         FROM comments c1 
         WHERE c1.parent_id != 0 
         AND NOT EXISTS (
             SELECT 1 FROM comments c2 
             WHERE c2.id = c1.parent_id
         )");
    
    while ($orphan = mysqli_fetch_assoc($orphaned_comments)) {
        mysqli_query($link, "DELETE FROM comments WHERE id = {$orphan['id']}");
    }
    
    header("Location: product.php?id=$product_id");
    exit();
}

$comments_result = mysqli_query($link, 
    "SELECT c.*, u.login as user_login 
     FROM comments c
     LEFT JOIN users u ON c.user_id = u.id
     WHERE c.product_id = $product_id
     ORDER BY COALESCE(c.parent_id, c.id), c.created_at");

$comments = [];
while ($comment = mysqli_fetch_assoc($comments_result)) {
    if ($comment['parent_id'] == 0) {
        $comments[$comment['id']] = $comment;
        $comments[$comment['id']]['replies'] = [];
    } else {
        $comments[$comment['parent_id']]['replies'][] = $comment;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $product['name'] ?></title>
</head>
<body>
    <?php include 'includes/header.php' ?>
    <div class="afa">
        <h1><?= $product['name'] ?></h1>
        <img class="proi" src="images/<?= $product['image_url'] ?>" width="200">
        <p>Цена: <?= $product['price'] ?> бун.</p>
        <p><?= nl2br($product['description']) ?></p>
    </div>
    <div class="comments-section">
        <h2>Комментарии</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" class="comment-form">
                <textarea name="comment_text" required placeholder="Ваш комм"></textarea>
                <button type="submit">Отправить</button>
            </form>
        <?php else: ?>
            <p>Для комментирования <a href="#" onclick="openAuthModal()">войдите</a> в аккаунт</p>
        <?php endif ?>

        <div class="comments-list">
            <?php foreach ($comments as $comment):
                $is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id'] ?>
                <div class="comment">
                    <div class="comment-header">
                        <strong><?= $comment['user_login'] ?></strong>
                        <span><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                        <?php if ($is_owner): ?>
                            <div class="comment-actions">
                                <a href="product.php?id=<?= $product_id ?>&delete_comment=<?= $comment['id'] ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Удалить этот комментарий и все ответы?')">Удалить</a>
                            </div>
                        <?php endif ?>
                    </div>
                    <p><?= $comment['text'] ?></p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button onclick="document.getElementById('reply-<?= $comment['id'] ?>').style.display='block'">Ответить</button>
                        <form method="POST" id="reply-<?= $comment['id'] ?>" style="display:none;">
                            <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                            <textarea name="comment_text" required placeholder="Ваш ответ..."></textarea>
                            <button type="submit">Отправить</button>
                        </form>
                    <?php endif ?>
                    
                    <?php foreach ($comment['replies'] as $reply): 
                        $is_reply_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id'] ?>
                        <div class="comment reply">
                            <div class="comment-header">
                                <strong><?= $reply['user_login'] ?></strong>
                                <span><?= date('d.m.Y H:i', strtotime($reply['created_at'])) ?></span>
                                <?php if ($is_reply_owner): ?>
                                    <div class="comment-actions">
                                        <a href="product.php?id=<?= $product_id ?>&delete_comment=<?= $reply['id'] ?>" 
                                           class="delete-btn" 
                                           onclick="return confirm('Удалить комментарий?')">Удалить</a>
                                    </div>
                                <?php endif ?>
                            </div>
                            <p><?= $reply['text'] ?></p>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php' ?>
</body>
</html>