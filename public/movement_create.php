<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

// GET/POST どちらでも、selectの選択肢は必要なので常に引く
$sql = 'SELECT id, name FROM beans ORDER BY name';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$beans = $stmt->fetchAll();

$sql = 'SELECT id, name, company FROM customers ORDER BY name';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$customers = $stmt->fetchAll();

$sql = 'SELECT stock_movements.id, stock_movements.type, stock_movements.bags, stock_movements.moved_at, beans.name,
        customers.name AS customer_name, customers.company AS customer_company
        FROM stock_movements
        JOIN beans ON stock_movements.bean_id = beans.id
        LEFT JOIN customers ON stock_movements.customer_id = customers.id
        ORDER BY stock_movements.moved_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$movements = $stmt->fetchAll();

$typeLabels = typeLabels();

// POST（記録を送信したとき）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beanId = $_POST['bean_id'];
    $type = $_POST['type'];
    $bags = $_POST['bags'];
    $movedAt = $_POST['moved_at'];
    // 入荷（in）は顧客と無関係なので customer_id は常にNULL
    $customerId = ($type === 'reserve' || $type === 'out') && !empty($_POST['customer_id'])
        ? $_POST['customer_id']
        : null;

    if ($type === 'out' || $type === 'reserve') {
        $sql = "SELECT
            SUM(CASE WHEN type = 'in'      THEN bags ELSE 0 END) AS total_in,
            SUM(CASE WHEN type = 'reserve' THEN bags ELSE 0 END) AS total_reserve,
            SUM(CASE WHEN type = 'out'     THEN bags ELSE 0 END) AS total_out
        FROM stock_movements
        WHERE bean_id = :bean_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':bean_id', $beanId, PDO::PARAM_INT);
        $status = $stmt->execute();
        $row = $stmt->fetch();
        $totalIn      = $row['total_in']; //入荷
        $totalReserve = $row['total_reserve']; //予約
        $totalOut     = $row['total_out']; //販売

        // 販売のとき
        if ($type === 'out') {
            // 販売：実在庫（入荷 − 販売）を超えないか
            $zaiko = $totalIn - $totalOut;
            // 在庫より多く売ろうとしていたら弾く
            if ($bags > $zaiko) {
                $error = "在庫が足りません（現在の在庫: {$zaiko}袋）";
            }
        } else {
            // 予約：空き在庫（入荷 − 予約）を超えないか
            $aki = $totalIn - $totalReserve;
            if ($bags > $aki) {
                $error = "予約できる在庫が足りません（予約可能: {$aki}袋）";
            }
        }
    }

    if (empty($error)) {
        $sql = 'INSERT INTO stock_movements (bean_id, customer_id, type, bags, moved_at)
            VALUES (:bean_id, :customer_id, :type, :bags, :moved_at)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':bean_id', $beanId, PDO::PARAM_INT);
        $stmt->bindValue(':customer_id', $customerId, $customerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        $stmt->bindValue(':bags', $bags, PDO::PARAM_INT);
        $stmt->bindValue(':moved_at', $movedAt, PDO::PARAM_STR);
        $status = $stmt->execute();

        if (!$status) {
            // 失敗 → エラーメッセージを変数に入れる
            $error = $stmt->errorInfo();
            exit('送信エラー:' . $error[2]);
        }
        header('Location: index.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>入出荷記録 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <h1 class="page-title">入出荷記録</h1>
        <div class="card">
            <?php if (!empty($error)) : ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form action="./movement_create.php" method="post">
                <div class="form-group">
                    <label for="bean_id" class="form-label">
                        生豆
                        <select name="bean_id" id="bean_id" class="form-input" required>
                            <?php foreach ($beans as $bean): ?>
                                <option value="<?= h($bean['id']); ?>"><?= h($bean['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">
                        種類
                        <select name="type" id="type" class="form-input">
                            <option value="in">入荷</option>
                            <option value="reserve">予約</option>
                            <option value="out">販売</option>
                        </select>
                    </label>
                </div>

                <div class="form-group" id="customer_group" style="display:none;">
                    <label for="customer_id" class="form-label">
                        顧客
                        <select name="customer_id" id="customer_id" class="form-input">
                            <option value="">選択してください</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= h($customer['id']); ?>">
                                    <?= h($customer['name']); ?><?= $customer['company'] ? '（' . h($customer['company']) . '）' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    <label for="number" class="form-label">
                        袋数
                        <input type="number" name="bags" id="number" min="1" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="date" class="form-label">
                        日付
                        <input type="date" name="moved_at" id="date" class="form-input" required>
                    </label>
                </div>

                <button type="submit" class="submit-btn">記録する</button>
            </form>
        </div>

        <div class="table-wrapper">
            <?php if (empty($movements)) : ?>
                <p>記録がありません</p>
            <?php else : ?>
                <table>
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>生豆</th>
                            <th>種類</th>
                            <th>袋数</th>
                            <th>顧客</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement) : ?>
                            <tr>
                                <td><?= h($movement['moved_at']) ?></td>
                                <td><?= h($movement['name']) ?></td>
                                <td><?= h($typeLabels[$movement['type']]) ?></td>
                                <td><?= h($movement['bags']) ?></td>
                                <td>
                                    <?php if ($movement['customer_name']) : ?>
                                        <?= h($movement['customer_name']) ?><?= $movement['customer_company'] ? '（' . h($movement['customer_company']) . '）' : '' ?>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <a href="movement_edit.php?id=<?= h($movement['id']) ?>" class="icon-btn" title="編集" aria-label="編集">
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

    <script>
        const typeSelect = document.getElementById('type');
        const customerGroup = document.getElementById('customer_group');
        const customerSelect = document.getElementById('customer_id');

        function toggleCustomerField() {
            const needsCustomer = typeSelect.value === 'reserve' || typeSelect.value === 'out';
            customerGroup.style.display = needsCustomer ? '' : 'none';
            if (!needsCustomer) {
                customerSelect.value = '';
            }
        }

        typeSelect.addEventListener('change', toggleCustomerField);
        toggleCustomerField();
    </script>
</body>

</html>