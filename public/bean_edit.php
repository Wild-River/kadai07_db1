<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // URLの ?id=◯ から id を受け取る
    $id = $_GET['id'];

    // その id で beans から1件引く
    $sql = 'SELECT id, name, supplier, lot_no, price, kg_per_bag FROM beans WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $bean = $stmt->fetch();
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

    // 成功 → $_SESSION に記録して index.php へリダイレクト
    if (!$status) {
        // 失敗 → エラーメッセージを変数に入れる
        $error = $stmt->errorInfo();
        exit('送信エラー:' . $error[2]);
    }
    header('Location: index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- 失敗時の$error の中身は $stmt->errorInfo() の配列
     （exit('送信エラー:' . $error[2]); で $error[2] と添字を付けているのがその証拠）
     将来この exit を消して画面にエラーを出す作りに変えると、h() は文字列を想定しているので配列を渡すと警告が出ます。
     今は exit で止まるので実害はない。 -->
    <?php if (!empty($error)) {
        echo h($error);
    } ?>
    <form method="post" action="./bean_edit.php">
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
                <input type="number" step="0.01" id="kg_per_bag" name="kg_per_bag" value="<?= h($bean['kg_per_bag']) ?>" class="form-input" required>
            </label>
        </div>

        <!-- 編集対象の id を入れておくと、送信時に $_POST['id'] で受け取れる -->
        <input type="hidden" name="id" value="<?= h($bean['id']) ?>">

        <button type="submit" class="submit-btn">
            送信する
        </button>
    </form>

</body>

</html>