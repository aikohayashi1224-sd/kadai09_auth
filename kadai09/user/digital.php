<?php
$page_title = 'デジタル';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'               => $user_id,
        'email_address'         => $_POST['email_address'] ?? '',
        'email_note'            => $_POST['email_note'] ?? '',
        'sns_service'           => $_POST['sns_service'] ?? '',
        'sns_id'                => $_POST['sns_id'] ?? '',
        'sns_note'              => $_POST['sns_note'] ?? '',
        'sns_after_death'       => $_POST['sns_after_death'] ?? '',
        'sns_post_message'      => $_POST['sns_post_message'] ?? '',
        'sns_post_person'       => $_POST['sns_post_person'] ?? '',
        'sns_password'          => $_POST['sns_password'] ?? '',
        'sns_password_location' => $_POST['sns_password_location'] ?? '',
        'subscription_service'  => $_POST['subscription_service'] ?? '',
        'subscription_fee'      => (int)str_replace(',', '', $_POST['subscription_fee'] ?? ''),
        'subscription_cancel'   => $_POST['subscription_cancel'] ?? '',
        'subscription_note'     => $_POST['subscription_note'] ?? '',
        'password_management'   => $_POST['password_management'] ?? '',
        'device_disposal'       => $_POST['device_disposal'] ?? '',
    ];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO digital
                (user_id, email_address, email_note,
                 sns_service, sns_id, sns_note, sns_after_death,
                 sns_post_message, sns_post_person,
                 sns_password, sns_password_location,
                 subscription_service, subscription_fee, subscription_cancel, subscription_note,
                 password_management, device_disposal)
            VALUES
                (:user_id, :email_address, :email_note,
                 :sns_service, :sns_id, :sns_note, :sns_after_death,
                 :sns_post_message, :sns_post_person,
                 :sns_password, :sns_password_location,
                 :subscription_service, :subscription_fee, :subscription_cancel, :subscription_note,
                 :password_management, :device_disposal)
            ON DUPLICATE KEY UPDATE
                email_address         = VALUES(email_address),
                email_note            = VALUES(email_note),
                sns_service           = VALUES(sns_service),
                sns_id                = VALUES(sns_id),
                sns_note              = VALUES(sns_note),
                sns_after_death       = VALUES(sns_after_death),
                sns_post_message      = VALUES(sns_post_message),
                sns_post_person       = VALUES(sns_post_person),
                sns_password          = VALUES(sns_password),
                sns_password_location = VALUES(sns_password_location),
                subscription_service  = VALUES(subscription_service),
                subscription_fee      = VALUES(subscription_fee),
                subscription_cancel   = VALUES(subscription_cancel),
                subscription_note     = VALUES(subscription_note),
                password_management   = VALUES(password_management),
                device_disposal       = VALUES(device_disposal)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM digital WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>デジタル</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">

                <h3 class="section-heading">メール</h3>
                <div class="form-group">
                    <label>メインのメールアドレス</label>
                    <input type="email" name="email_address" value="<?= htmlspecialchars($row['email_address'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>その他・対応希望</label>
                    <textarea name="email_note"><?= htmlspecialchars($row['email_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">SNS・ソーシャルメディア</h3>
                <div class="form-group">
                    <label>メインのSNSサービス名</label>
                    <input type="text" name="sns_service" value="<?= htmlspecialchars($row['sns_service'] ?? '') ?>" placeholder="例：Instagram、X（Twitter）">
                </div>
                <div class="form-group">
                    <label>アカウントID・URL</label>
                    <input type="text" name="sns_id" value="<?= htmlspecialchars($row['sns_id'] ?? '') ?>" placeholder="例：@username">
                </div>
                <div class="form-group">
                    <label>その他のアカウント</label>
                    <textarea name="sns_note" placeholder="例：Facebook、LinkedIn など"><?= htmlspecialchars($row['sns_note'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>死後のアカウント対応希望</label>
                    <select name="sns_after_death">
                        <?php foreach (['', '削除してほしい', '追悼アカウントにしてほしい', 'そのままにしてほしい', '家族に任せる'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['sns_after_death'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>死後に投稿してほしいメッセージ（任意）</label>
                    <textarea name="sns_post_message" placeholder="例：皆さんお世話になりました。ありがとう。"><?= htmlspecialchars($row['sns_post_message'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>投稿を依頼する人</label>
                    <input type="text" name="sns_post_person" value="<?= htmlspecialchars($row['sns_post_person'] ?? '') ?>" placeholder="例：姉（山田花子）">
                </div>
                <div class="form-group">
                    <label>SNSパスワードの保管場所</label>
                    <input type="text" name="sns_password_location" value="<?= htmlspecialchars($row['sns_password_location'] ?? '') ?>" placeholder="例：手帳の○ページ、1Passwordに保存">
                </div>
                <div class="form-group">
                    <label>SNSパスワード（任意）</label>
                    <input type="text" name="sns_password" value="<?= htmlspecialchars($row['sns_password'] ?? '') ?>">
                    <small style="color:#e74c3c;">※ 入力する場合は引受人のみ閲覧できるよう権限設定してください。</small>
                </div>

                <h3 class="section-heading">サブスクリプション</h3>
                <div class="form-group">
                    <label>契約しているサービス一覧</label>
                    <textarea name="subscription_service" placeholder="例：Netflix、Spotify、Adobe CC、iCloud 200GB"><?= htmlspecialchars($row['subscription_service'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>月額合計（円・目安）</label>
                    <input type="text" name="subscription_fee" value="<?= $row['subscription_fee'] ? number_format($row['subscription_fee']) : '' ?>" placeholder="例：5,000" inputmode="numeric">
                </div>
                <div class="form-group">
                    <label>解約方法・手順</label>
                    <textarea name="subscription_cancel" placeholder="例：各サービスのアカウント設定から解約、クレジットカードを止めれば自動停止"><?= htmlspecialchars($row['subscription_cancel'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>補足</label>
                    <textarea name="subscription_note"><?= htmlspecialchars($row['subscription_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">パスワード管理・デバイス</h3>
                <div class="form-group">
                    <label>パスワード管理の方法・保管場所</label>
                    <textarea name="password_management" placeholder="例：1Passwordを使用、マスターパスワードは手帳の○ページ"><?= htmlspecialchars($row['password_management'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>デバイスの処分・引き継ぎ希望</label>
                    <textarea name="device_disposal" placeholder="例：iPhone → 弟へ、MacBook → データ消去後廃棄"><?= htmlspecialchars($row['device_disposal'] ?? '') ?></textarea>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>