<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$typeLabels = typeLabels();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // URLの ?id=◯ から id を受け取る
    $id = $_GET['id'];

    // その id で beans から1件引く
    $sql = 'SELECT id, name, supplier, lot_no, price, kg_per_bag FROM beans WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $bean = $stmt->fetch();

    $sql = 'SELECT stock_movements.type, stock_movements.bags, stock_movements.moved_at,
            customers.name AS customer_name, customers.company AS customer_company
            FROM stock_movements
            LEFT JOIN customers ON stock_movements.customer_id = customers.id
            WHERE stock_movements.bean_id = :id
            ORDER BY stock_movements.moved_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $movements = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $supplier = $_POST['supplier'];
    $lot_no = $_POST['lot_no'];
    $price = $_POST['price'];
    $kg_per_bag = $_POST['kg_per_bag'];

    // UPDATE する
    $sql = 'UPDATE beans SET name = :name, supplier = :supplier, lot_no = :lot_no, price = :price, kg_per_bag = :kg_per_bag WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':supplier', $supplier, PDO::PARAM_STR);
    $stmt->bindValue(':lot_no', $lot_no, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_INT);
    $stmt->bindValue(':kg_per_bag', $kg_per_bag, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $status = $stmt->execute();

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
    <title>生豆編集 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <h1 class="page-title">生豆編集</h1>
        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./bean_edit.php" id="edit-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        商品名
                        <input type="text" id="name" name="name" value="<?= h($bean['name']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="supplier" class="form-label">
                        仕入先
                        <input type="text" id="supplier" name="supplier" value="<?= h($bean['supplier']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="lot_no" class="form-label">
                        Lot No.
                        <input type="text" id="lot_no" name="lot_no" value="<?= h($bean['lot_no']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">
                        販売定価
                        <input type="number" id="price" name="price" value="<?= h($bean['price']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="kg_per_bag" class="form-label">
                        kg/袋
                        <input type="number" step="1" id="kg_per_bag" name="kg_per_bag" value="<?= h($bean['kg_per_bag']) ?>" class="form-input" required>
                    </label>
                </div>

                <!-- 編集対象の id を入れておくと、送信時に $_POST['id'] で受け取れる -->
                <input type="hidden" name="id" value="<?= h($bean['id']) ?>">
            </form>

            <div class="form-actions">
                <!-- form="フォームのid"を付けると、ボタンがフォームの外にあっても指定したidのフォームに紐づけて送信できる -->
                <button type="submit" form="edit-form" class="submit-btn">
                    決定
                </button>

                <form method="post" action="bean_delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($bean['id']) ?>">
                    <button type="submit" class="delete-btn">
                        削除
                    </button>
                </form>

                <a href="index.php" class="back-btn">戻る</a>
            </div>

            <div class="table-wrapper">
                <?php if (empty($movements)): ?>
                    <p>記録がありません</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th>種類</th>
                                <th>袋数</th>
                                <th>顧客</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td><?= h($movement['moved_at']) ?></td>
                                    <td><?= h($typeLabels[$movement['type']]) ?></td>
                                    <td><?= h($movement['bags']) ?></td>
                                    <td>
                                        <?php if ($movement['customer_name']) : ?>
                                            <?= h($movement['customer_name']) ?><?= $movement['customer_company'] ? '（' . h($movement['customer_company']) . '）' : '' ?>
                                        <?php else : ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>


    </div>

</body>

</html>