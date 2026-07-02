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
            <!-- 失敗時の$error の中身は $stmt->errorInfo() の配列
             （exit('送信エラー:' . $error[2]); で $error[2] と添字を付けているのがその証拠）
             将来この exit を消して画面にエラーを出す作りに変えると、h() は文字列を想定しているので配列を渡すと警告が出ます。
             今は exit で止まるので実害はない。 -->
            <?php if (!empty($error)) : ?>
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

        </div>
    </div>

</body>

</html>