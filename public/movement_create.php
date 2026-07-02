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
    <form action="./movement_create.php" method="post">
        <label for="bean_id">
            生豆
            <select name="bean_id" id="bean_id" required>
                <?php foreach ($beans as $bean): ?>
                    <option value="<?= h($bean['id']); ?>"><?= h($bean['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label for="type">
            種類
            <select name="type" id="type">
                <option value="in">入荷</option>
                <option value="reserve">予約</option>
                <option value="out">販売</option>
            </select>
        </label>

        <label for="number">
            袋数
            <input type="number" name="bags" id="number" min="1" required>
        </label>
        <label for="date">
            日付
            <input type="date" name="moved_at" id="date" required>
        </label>
        <button type="submit">記録する</button>
    </form>
</body>

</html>