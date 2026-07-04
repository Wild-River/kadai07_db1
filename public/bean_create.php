<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $supplier = $_POST['supplier'];
    $lot_no = $_POST['lot_no'];
    $price = $_POST['price'];
    $kg_per_bag = $_POST['kg_per_bag'];

    // $sql を prepare して、5つの値を bindValue で紐付けて実行
    $sql = 'INSERT INTO beans (name, supplier, lot_no, price, kg_per_bag) VALUES (:name, :supplier, :lot_no, :price, :kg_per_bag)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':supplier', $supplier, PDO::PARAM_STR);
    $stmt->bindValue(':lot_no', $lot_no, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_INT); // price は整数なのでINTでOK
    $stmt->bindValue(':kg_per_bag', $kg_per_bag, PDO::PARAM_STR); // 小数はSTR
    $status = $stmt->execute(); //実行

    // 成功 → $_SESSION に記録して index.php へリダイレクト
    if (!$status) {
        sql_error($stmt);
    }
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生豆登録 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <h1 class="page-title">生豆登録</h1>
        <div class="card">
            <?php if (!empty($error)) : ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./bean_create.php">
                <div class="form-group">
                    <label for="name" class="form-label">
                        商品名
                        <input type="text" id="name" name="name" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="supplier" class="form-label">
                        仕入先
                        <input type="text" id="supplier" name="supplier" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="lot_no" class="form-label">
                        Lot No.
                        <input type="text" id="lot_no" name="lot_no" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">
                        販売定価
                        <input type="number" id="price" name="price" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="kg_per_bag" class="form-label">
                        kg/袋
                        <input type="number" step="1" id="kg_per_bag" name="kg_per_bag" min="0" max="9999" class="form-input" required>
                    </label>
                </div>

                <button type="submit" class="submit-btn">
                    登録する
                </button>
            </form>
        </div>
    </div>

</body>

</html>