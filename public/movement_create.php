<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

// GET/POST どちらでも、selectの選択肢は必要なので常に引く
$sql = 'SELECT id, name FROM beans ORDER BY name';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$beans = $stmt->fetchAll();

// POST（記録を送信したとき）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beanId = $_POST['bean_id'];
    $type = $_POST['type'];
    $bags = $_POST['bags'];
    $movedAt = $_POST['moved_at'];

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
        $sql = 'INSERT INTO stock_movements (bean_id, type, bags, moved_at)
            VALUES (:bean_id, :type, :bags, :moved_at)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':bean_id', $beanId, PDO::PARAM_INT);
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
<html lang="en">

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
            <!-- 失敗時の$error の中身は $stmt->errorInfo() の配列
             （exit('送信エラー:' . $error[2]); で $error[2] と添字を付けているのがその証拠）
             将来この exit を消して画面にエラーを出す作りに変えると、h() は文字列を想定しているので配列を渡すと警告が出ます。
             今は exit で止まるので実害はない。 -->
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
    </div>
</body>

</html>