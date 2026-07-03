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
        --  LEFT JOIN にすると「beans に登録されている生豆は、履歴が無くても必ず表示される（在庫0として）」
        LEFT JOIN stock_movements ON beans.id = stock_movements.bean_id";

if ($keyword !== '') {
    // .= で文字列を組み立てるときは「継ぎ目でスペースが1個は入るようにする」のが鉄則（最初と最後に入れてある）
    // EMULATE_PREPARES が false なので、同じ名前のプレースホルダを2箇所で使うことはできない
    $sql .= " WHERE beans.name LIKE :keyword1 OR beans.supplier LIKE :keyword2 ";
}

$sql .= " GROUP BY beans.id, beans.name, beans.supplier, beans.lot_no, beans.price, beans.kg_per_bag
        ORDER BY beans.name";

$stmt = $pdo->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword1', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->bindValue(':keyword2', '%' . $keyword . '%', PDO::PARAM_STR);
}
$stmt->execute();
$stocks = $stmt->fetchAll();

// グラフ・サマリー用の集計（入荷袋数を「出荷済み」「予約中・未出荷」「未予約在庫」に分解）
$chartLabels = [];
$chartShipped = [];
$chartPending = [];
$chartUnreserved = [];
$totalShipped = 0;
$totalPending = 0;
$totalUnreserved = 0;
foreach ($stocks as $stock) {
    $shipped = (int) $stock['total_out'];
    $pending = max((int) $stock['total_reserve'] - $shipped, 0); // 予約したが未出荷の袋数
    $unreserved = max((int) $stock['total_in'] - $shipped - $pending, 0);
    $chartLabels[] = $stock['name'];
    $chartShipped[] = $shipped;
    $chartPending[] = $pending;
    $chartUnreserved[] = $unreserved;
    $totalShipped += $shipped;
    $totalPending += $pending;
    $totalUnreserved += $unreserved;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫一覧 | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/header.php'; ?>

    <div class="container">
        <div class="insight-row">
            <div class="card stat-tile">
                <div class="section-head">
                    <p class="section-title">現在の在庫状況</p>
                    <p class="stat-note">未予約・予約中・出荷済みの割合</p>
                </div>
                <div id="statusDonutChart"></div>
            </div>
            <div class="card chart-card">
                <div class="section-head">
                    <p class="section-title">生豆別 入荷内訳</p>
                    <p class="section-sub">入荷袋数を「出荷済み」「予約中・未出荷」「未予約在庫」に分解</p>
                </div>
                <div id="stockChart"></div>
            </div>
        </div>

        <form method="get" action="" class="search-form">
            <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="商品名・仕入先で検索" autocomplete="off">
            <button type="submit">検索</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th></th>
                        <th>仕入先</th>
                        <th>Lot No.</th>
                        <th>販売定価</th>
                        <th>kg/袋</th>
                        <th>入荷</th>
                        <th>予約</th>
                        <th>販売</th>
                        <th>在庫数</th>
                        <th>未出荷</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $stock):
                        // 在庫数 = 入荷 − 販売
                        $zaiko = $stock['total_in'] - $stock['total_out'];
                        $isLow = $zaiko <= 5;   // 在庫5袋以下なら「少ない」とみなす
                        // 未出荷 = 予約 − 販売
                        $mishukka = $stock['total_reserve'] - $stock['total_out'];
                    ?>
                        <tr class="<?= $isLow ? 'low-stock' : '' ?>">
                            <td><?= h($stock['name']) ?></td>
                            <td class="actions-cell">
                                <a href="bean_edit.php?id=<?= h($stock['id']) ?>" class="icon-btn" title="編集" aria-label="編集">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </td>
                            <td><?= h($stock['supplier']) ?></td>
                            <td><?= h($stock['lot_no']) ?></td>
                            <td><?= h(number_format($stock['price'])) ?></td>
                            <td><?= h($stock['kg_per_bag']) ?></td>
                            <td><?= h($stock['total_in']) ?></td>
                            <td><?= h($stock['total_reserve']) ?></td>
                            <td><?= h($stock['total_out']) ?></td>
                            <td><?= h($zaiko) ?></td>
                            <td><?= h($mishukka) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
        const chartShipped = <?= json_encode($chartShipped) ?>;
        const chartPending = <?= json_encode($chartPending) ?>;
        const chartUnreserved = <?= json_encode($chartUnreserved) ?>;
        const totalShipped = <?= json_encode($totalShipped) ?>;
        const totalPending = <?= json_encode($totalPending) ?>;
        const totalUnreserved = <?= json_encode($totalUnreserved) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="js/chart.js"></script>
</body>

</html>