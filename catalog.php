<?php include 'includes/header.php'; 
include_once 'includes/db_connect.php';?>

<main>
    <h1>Каталог кепок</h1>
    
    <div class="filters">
        <form method="GET">
            <input type="text" name="search" placeholder="Поиск по названию...">
            
            <select name="brand">
                <option value="">Все бренды</option>
                <?php
                $brands = mysqli_query($link, "SELECT * FROM brands");
                while($brand = mysqli_fetch_assoc($brands)) {
                    $selected = isset($_GET['brand']) && $_GET['brand'] == $brand['id'] ? 'selected' : '';
                    echo "<option value='{$brand['id']}' $selected>{$brand['name']}</option>";
                }
                ?>
            </select>
            
            <select name="category">
                <option value="">Все категории</option>
                <?php
                $categories = mysqli_query($link, "SELECT * FROM categories");
                while($category = mysqli_fetch_assoc($categories)) {

                    $selected = isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : '';
                    echo "<option value='{$category['id']}' $selected>{$category['name']}</option>";
                }
                ?>
            </select>
            
            <select name="sort">
                <option value="none">Без сортировки</option>
                <option value="price_asc">Сначала дешевые</option>
                <option value="price_desc">Сначала дорогие</option>
                <option value="name_asc">А-Я</option>
                <option value="name_desc">Я-А</option>
            </select>
            
            <button type="submit">Применить фильтры</button>
            <a href="?" class="reset-btn">Сбросить</a>
        </form>
    </div>
<!-------------------------------------------------------------------------------------------------------------------------->
    <div class="products">
        <?php
        $sql = "SELECT p.*, b.name as brand_name, c.name as category_name 
                FROM products p 
                JOIN brands b ON p.brand_id = b.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";

        if(isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql .= " AND p.name LIKE '%$search%'";
        }
        
        if(isset($_GET['brand']) && !empty($_GET['brand'])) {
            $brand = $_GET['brand'];
            $sql .= " AND p.brand_id = $brand";
        }

        if(isset($_GET['category']) && !empty($_GET['category'])) {
            $category = $_GET['category'];
            $sql .= " AND p.category_id = $category";
        }
        

        if(isset($_GET['sort'])) {
            switch($_GET['sort']) {
                case 'price_asc': $sql .= " ORDER BY p.price ASC"; break;
                case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
                case 'name_asc': $sql .= " ORDER BY p.name ASC"; break;
                case 'name_desc': $sql .= " ORDER BY p.name DESC"; break;
                default: $sql .= " ORDER BY p.id DESC";
            }
        } else {
            $sql .= " ORDER BY p.id DESC";
        }
        
        $result = mysqli_query($link, $sql);
        
        if(mysqli_num_rows($result) > 0) {
            while($product = mysqli_fetch_assoc($result)):
        ?>
            <div class="product-card">
                <img src="images/<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>">
                <h3><?= $product['name'] ?></h3>
                <p class="brand">Бренд: <?= $product['brand_name'] ?></p>
                <p class="category">Категория: <?= $product['category_name'] ?? 'Без категории' ?></p>
                <p class="price">Цена: <?= $product['price'] ?> BYN</p>
                <a href="product.php?id=<?= $product['id'] ?>" class="details-btn">Подробнее</a>
            </div>
        
        <?php 
            endwhile;
        } else {
            echo "<p>Товары не найдены.</p>";
        }
        ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>