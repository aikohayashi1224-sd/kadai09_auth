<?php
$page_title = '遺言・形見';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'       => $user_id,
        'will_exists'   => $_POST['will_exists'] ?? '',
        'will_location' => $_POST['will_location'] ?? '',
        'keepsake_note' => $_POST['keepsake_note'] ?? '',
        'donation_note' => $_POST['donation_note'] ?? '',
    ];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO will (user_id, will_exists, will_location, keepsake_note, donation_note)
            VALUES (:user_id, :will_exists, :will_location, :keepsake_note, :donation_note)
            ON DUPLICATE KEY UPDATE
                will_exists   = VALUES(will_exists),
                will_location = VALUES(will_location),
                keepsake_note = VALUES(keepsake_note),
                donation_note = VALUES(donation_note)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM will WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>遺言・形見</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">

                <h3 class="section-heading">遺言書</h3>
                <div class="form-group">
                    <label>遺言書の有無</label>
                    <select name="will_exists">
                        <?php foreach (['', 'あり（自筆証書遺言）', 'あり（公正証書遺言）', 'あり（秘密証書遺言）', 'なし', '作成予定'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['will_exists'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>保管場所・担当弁護士・公証役場など</label>
                    <textarea name="will_location"
                              placeholder="例：自宅金庫、○○法律事務所（担当：田中弁護士）"><?= htmlspecialchars($row['will_location'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">形見分け</h3>
                <div class="form-group">
                    <label>形見として渡したいもの・希望</label>
                    <textarea name="keepsake_note" style="height:140px;"
                              placeholder="例：祖母から受け継いだ帯留めは姉へ、カメラは弟へ渡してほしい"><?= htmlspecialchars($row['keepsake_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">寄付・社会貢献</h3>
                <div class="form-group">
                    <label>寄付・寄贈の希望</label>
                    <textarea name="donation_note"
                              placeholder="例：○○基金に寄付してほしい、蔵書は地域図書館へ"><?= htmlspecialchars($row['donation_note'] ?? '') ?></textarea>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>