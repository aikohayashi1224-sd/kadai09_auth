<?php
session_start();

if (!isset($_SESSION['trustee_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$trustee_id   = $_SESSION['trustee_id'];
$trustee_name = $_SESSION['trustee_name'];
$owner_id     = $_SESSION['trustee_user_id'];

// ユーザー情報を取得
$stmt = $pdo->prepare("SELECT name, last_checked_at, alert_days FROM users WHERE id = ?");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();

// この引受人への開示状態を取得
$stmt = $pdo->prepare("SELECT is_disclosed FROM trustees WHERE id = ? AND user_id = ?");
$stmt->execute([$trustee_id, $owner_id]);
$trustee = $stmt->fetch();

// 開示チェック
$can_view = false;
if ($trustee['is_disclosed'] == 1) {
    // ユーザーがこの引受人に手動開示している
    $can_view = true;
} elseif ($owner['last_checked_at'] !== null) {
    // 最終ログインからalert_days日以上経過しているか
    $last = new DateTime($owner['last_checked_at']);
    $now  = new DateTime();
    $diff = $now->diff($last)->days;
    if ($diff >= $owner['alert_days']) {
        $can_view = true;
    }
}

// 閲覧権限を取得
$stmt = $pdo->prepare("SELECT section FROM trustees_permissions WHERE trustee_id = ? AND is_visible = 1");
$stmt->execute([$trustee_id]);
$visible = $stmt->fetchAll(PDO::FETCH_COLUMN);

$sections = [
    'profile'    => '基本情報',
    'medical'    => '医療・健康',
    'assets'     => '財産',
    'digital'    => 'デジタル',
    'work'       => '仕事',
    'housing'    => '賃貸・契約',
    'belongings' => '動産・コレクション',
    'funeral'    => '葬儀',
    'contacts'   => '人間関係',
    'will'       => '遺言・形見',
    'messages'   => '引受人へのメッセージ',
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>引受人ダッシュボード - エンディングノート</title>
    <link rel="stylesheet" href="/gs_kadai/kadai09/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 class="site-title">📋 エンディングノート（引受人）</h1>
            <nav class="site-nav">
                <span><?= htmlspecialchars($trustee_name) ?>さん</span>
                <a href="logout.php">ログアウト</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2><?= htmlspecialchars($owner['name']) ?>さんのエンディングノート</h2>

            <?php if (!$can_view): ?>
                <!-- 開示条件を満たしていない -->
                <div style="text-align:center; padding:40px 0; color:#999;">
                    <p style="font-size:1.2rem; margin-bottom:12px;">現在、閲覧できません</p>
                    <p style="font-size:0.9rem;">
                        <?= htmlspecialchars($owner['name']) ?>さんが開示を許可するか、<br>
                        一定期間ログインがない場合に閲覧できるようになります。
                    </p>
                </div>

            <?php elseif (empty($visible)): ?>
                <p style="color:#999;">閲覧できるカテゴリがありません。</p>

            <?php else: ?>
                <p style="color:#777; margin-bottom:24px;">閲覧が許可されているカテゴリを確認できます。</p>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                    <?php foreach ($sections as $section => $label): ?>
                        <?php if (in_array($section, $visible)): ?>
                            <a href="view.php?section=<?= $section ?>"
                               style="display:block; padding:16px; background:#f8f9fa; border-radius:6px; text-decoration:none; color:#2c3e50; border:1px solid #eee; text-align:center;">
                                <?= $label ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>