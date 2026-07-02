<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$keyword = $_GET['keyword'] ?? '';

$sql = "SELECT
            beans.id,
            beans.name,
            beans.supplier,
            beans.lot_no,
            beans.price,
            beans.kg_per_bag,
            SUM(CASE WHEN type = 'in'      THEN bags ELSE 0 END) AS total_in,
            SUM(CASE WHEN type = 'reserve' THEN bags ELSE 0 END) AS total_reserve,
            SUM(CASE WHEN type = 'out'     THEN bags ELSE 0 END) AS total_out
        FROM beans
        LEFT JOIN stock_movements ON beans.id = stock_movements.bean_id";

if ($keyword !== '') {
    // .= で文字列を組み立てるときは「継ぎ目でスペースが1個は入るようにする」のが鉄則（最初と最後に入れてある）
    $sql .= " WHERE beans.name LIKE :keyword OR beans.supplier LIKE :keyword ";
}

$sql .= " GROUP BY beans.id, beans.name, beans.supplier, beans.lot_no, beans.price, beans.kg_per_bag
        ORDER BY beans.name";

$stmt = $pdo->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
}
$stmt->execute();
$stocks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="get" action="">
        <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="商品名・仕入先で検索">
        <button type="submit">検索</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>商品名</th>
                <th>仕入先</th>
                <th>Lot No.</th>
                <th>販売定価</th>
                <th>kg/袋</th>
                <th>入荷</th>
                <th>予約</th>
                <th>販売</th>
                <th>在庫数</th>
                <th>在庫量(kg)</th>
                <th>未出荷</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stocks as $stock):
                // 在庫数 = 入荷 − 販売
                $zaiko = $stock['total_in'] - $stock['total_out'];
                $zaikoKg = $zaiko * $stock['kg_per_bag'];
                $isLow = $zaiko <= 5;   // 在庫5袋以下なら「少ない」とみなす
                // 未出荷 = 予約 − 販売
                $mishukka = $stock['total_reserve'] - $stock['total_out'];
            ?>
                <tr class="<?= $isLow ? 'low-stock' : '' ?>">
                    <td><?= h($stock['name']) ?></td>
                    <td><?= h($stock['supplier']) ?></td>
                    <td><?= h($stock['lot_no']) ?></td>
                    <td><?= h($stock['price']) ?></td>
                    <td><?= h($stock['kg_per_bag']) ?></td>
                    <td><?= h($stock['total_in']) ?></td>
                    <td><?= h($stock['total_reserve']) ?></td>
                    <td><?= h($stock['total_out']) ?></td>
                    <td><?= h($zaiko) ?></td>
                    <td><?= h($zaikoKg) ?></td>
                    <td><?= h($mishukka) ?></td>
                    <td>
                        <a href="bean_edit.php?id=<?= h($stock['id']) ?>">編集</a>
                        <form method="post" action="bean_delete.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?= h($stock['id']) ?>">
                            <button type="submit" onclick="return confirm('削除しますか？');">削除</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>