<?php
function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function typeLabels()
{
    return [
        'in'      => '入荷',
        'reserve' => '予約',
        'out'     => '販売',
    ];
}
