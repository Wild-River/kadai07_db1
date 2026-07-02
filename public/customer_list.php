<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$sql = 'SELECT id, name, company, email, phone, note FROM customers ORDER BY name';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客一覧 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <h1 class="page-title">顧客一覧</h1>

        <div class="table-wrapper">
            <?php if (empty($customers)) : ?>
                <p>顧客が登録されていません</p>
            <?php else : ?>
                <table>
                    <thead>
                        <tr>
                            <th>氏名</th>
                            <th>会社名</th>
                            <th>メール</th>
                            <th>電話番号</th>
                            <th>備考</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer) : ?>
                            <tr>
                                <td><?= h($customer['name']) ?></td>
                                <td><?= h($customer['company'] ?? '-') ?></td>
                                <td><?= h($customer['email'] ?? '-') ?></td>
                                <td><?= h($customer['phone'] ?? '-') ?></td>
                                <td><?= h($customer['note'] ?? '-') ?></td>
                                <td class="actions-cell">
                                    <a href="customer_edit.php?id=<?= h($customer['id']) ?>" class="icon-btn" title="編集" aria-label="編集">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
