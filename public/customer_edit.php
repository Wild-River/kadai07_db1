<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // URLの ?id=◯ から id を受け取る
    $id = $_GET['id'];

    // その id で customers から1件引く
    $sql = 'SELECT id, name, company, email, phone, note FROM customers WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $note = $_POST['note'];

    // UPDATE する
    $sql = 'UPDATE customers SET name = :name, company = :company, email = :email, phone = :phone, note = :note WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':company', $company, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $status = $stmt->execute();

    if (!$status) {
        // 失敗 → エラーメッセージを変数に入れる
        $error = $stmt->errorInfo();
        exit('送信エラー:' . $error[2]);
    }
    header('Location: customer_list.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客編集 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <h1 class="page-title">顧客編集</h1>
        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./customer_edit.php" id="edit-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        氏名
                        <input type="text" id="name" name="name" value="<?= h($customer['name']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="company" class="form-label">
                        会社名
                        <input type="text" id="company" name="company" value="<?= h($customer['company'] ?? '') ?>" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        メール
                        <input type="email" id="email" name="email" value="<?= h($customer['email'] ?? '') ?>" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        電話番号
                        <input type="text" id="phone" name="phone" value="<?= h($customer['phone'] ?? '') ?>" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="note" class="form-label">
                        備考
                        <input type="text" id="note" name="note" value="<?= h($customer['note'] ?? '') ?>" class="form-input">
                    </label>
                </div>

                <!-- 編集対象の id を入れておくと、送信時に $_POST['id'] で受け取れる -->
                <input type="hidden" name="id" value="<?= h($customer['id']) ?>">
            </form>

            <div class="form-actions">
                <!-- form="フォームのid"を付けると、ボタンがフォームの外にあっても指定したidのフォームに紐づけて送信できる -->
                <button type="submit" form="edit-form" class="submit-btn">
                    決定
                </button>

                <a href="customer_list.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>

</body>

</html>
