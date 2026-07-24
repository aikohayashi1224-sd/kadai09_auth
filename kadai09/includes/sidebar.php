<?php
// 現在のページを判定してアクティブリンクを付ける
$current = basename($_SERVER['PHP_SELF']);
function navLink($href, $label, $current) {
    $file  = basename($href);
    $class = ($file === $current) ? ' class="active"' : '';
    return "<li><a href=\"{$href}\"{$class}>{$label}</a></li>";
}
?>
<div class="sidebar">
    <div class="sidebar-nav">
        <h3>メニュー</h3>
        <ul>
            <?= navLink('dashboard.php',   'ダッシュボード',           $current) ?>
            <?= navLink('profile.php',     '基本情報',                 $current) ?>
            <?= navLink('medical.php',     '医療・健康',               $current) ?>
            <?= navLink('assets.php',      '財産',                     $current) ?>
            <?= navLink('digital.php',     'デジタル',                 $current) ?>
            <?= navLink('work.php',        '仕事',                     $current) ?>
            <?= navLink('housing.php',     '賃貸・契約',               $current) ?>
            <?= navLink('belongings.php',  '動産・コレクション',       $current) ?>
            <?= navLink('funeral.php',     '葬儀',                     $current) ?>
            <?= navLink('contacts.php',    '人間関係',                 $current) ?>
            <?= navLink('will.php',        '遺言・形見',               $current) ?>
            <?= navLink('messages.php',    '引受人へのメッセージ',     $current) ?>
            <?= navLink('trustees.php',    '引受人設定',               $current) ?>
            <?= navLink('settings.php',    'アカウント設定',           $current) ?>
        </ul>
    </div>
</div>
