<?php
function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sql_error($stmt)
{
    $error = $stmt->errorInfo();
    exit('送信エラー:' . $error[2]);
}

//リダイレクト
function redirect($file_name)
{
    header("Location: " . $file_name);
    exit();
}

function typeLabels()
{
    return [
        'in'      => '入荷',
        'reserve' => '予約',
        'out'     => '販売',
    ];
}

function sortLink(string $label, string $key, string $currentSort, string $currentOrder, string $keyword): string
{
    $nextOrder = ($currentSort === $key && $currentOrder === 'ASC') ? 'desc' : 'asc';
    $params = ['sort' => $key, 'order' => $nextOrder];
    if ($keyword !== '') {
        $params['keyword'] = $keyword;
    }
    $arrow = '';
    if ($currentSort === $key) {
        $arrow = $currentOrder === 'ASC' ? ' ▲' : ' ▼';
    }
    return '<a href="?' . h(http_build_query($params)) . '">' . h($label) . $arrow . '</a>';
}
