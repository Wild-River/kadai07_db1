<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $note = $_POST['note'];

    // $sql を prepare して、5つの値を bindValue で紐付けて実行
    $sql = 'INSERT INTO customers (name, company, email, phone, note) VALUES (:name, :company, :email, :phone, :note)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':company', $company, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $status = $stmt->execute(); //実行

    // 成功 → $_SESSION に記録して index.php へリダイレクト
    if (!$status) {
        sql_error($stmt);
    }
    redirect('customer_list.php');
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客登録 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <h1 class="page-title">顧客登録</h1>
        <div class="card">
            <?php if (!empty($error)) : ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./customer_create.php">
                <div class="form-group">
                    <label for="name" class="form-label">
                        氏名
                        <input type="text" id="name" name="name" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="company" class="form-label">
                        会社名
                        <input type="text" id="company" name="company" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        メール
                        <input type="email" id="email" name="email" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        電話番号
                        <input type="text" id="phone" name="phone" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="note" class="form-label">
                        備考
                        <input type="text" id="note" name="note" class="form-input">
                    </label>
                </div>

                <button type="submit" class="submit-btn">
                    送信する
                </button>
            </form>
        </div>
    </div>

</body>

</html>