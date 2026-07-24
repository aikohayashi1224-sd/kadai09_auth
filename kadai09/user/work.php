<?php
$page_title = '仕事';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'employment_type', 'company_name', 'company_phone', 'company_address',
        'contact_person', 'contact_phone',
        'accountant_name', 'accountant_phone', 'work_note'
    ];
    $data = ['user_id' => $user_id];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? '';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO work
                (user_id, employment_type, company_name, company_phone, company_address,
                 contact_person, contact_phone,
                 accountant_name, accountant_phone, work_note)
            VALUES
                (:user_id, :employment_type, :company_name, :company_phone, :company_address,
                 :contact_person, :contact_phone,
                 :accountant_name, :accountant_phone, :work_note)
            ON DUPLICATE KEY UPDATE
                employment_type  = VALUES(employment_type),
                company_name     = VALUES(company_name),
                company_phone    = VALUES(company_phone),
                company_address  = VALUES(company_address),
                contact_person   = VALUES(contact_person),
                contact_phone    = VALUES(contact_phone),
                accountant_name  = VALUES(accountant_name),
                accountant_phone = VALUES(accountant_phone),
                work_note        = VALUES(work_note)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM work WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>仕事</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">

                <h3 class="section-heading">雇用形態・勤務先</h3>
                <div class="form-group">
                    <label>雇用形態</label>
                    <select name="employment_type">
                        <?php foreach (['', '会社員', '公務員', '自営業・フリーランス', 'パート・アルバイト', '無職', 'その他'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['employment_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>会社・屋号名</label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($row['company_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>会社の電話番号</label>
                    <input type="tel" name="company_phone" value="<?= htmlspecialchars($row['company_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>会社の住所</label>
                    <input type="text" name="company_address" value="<?= htmlspecialchars($row['company_address'] ?? '') ?>">
                </div>

                <h3 class="section-heading">連絡先（上司・担当者）</h3>
                <div class="form-group">
                    <label>担当者名・部署</label>
                    <input type="text" name="contact_person" value="<?= htmlspecialchars($row['contact_person'] ?? '') ?>" placeholder="例：総務部 田中様">
                </div>
                <div class="form-group">
                    <label>担当者の電話番号</label>
                    <input type="tel" name="contact_phone" value="<?= htmlspecialchars($row['contact_phone'] ?? '') ?>">
                </div>

                <h3 class="section-heading">税理士・顧問（自営業の方）</h3>
                <div class="form-group">
                    <label>税理士・顧問名</label>
                    <input type="text" name="accountant_name" value="<?= htmlspecialchars($row['accountant_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>電話番号</label>
                    <input type="tel" name="accountant_phone" value="<?= htmlspecialchars($row['accountant_phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>備考・引き継ぎ事項</label>
                    <textarea name="work_note" placeholder="例：進行中の案件、取引先への連絡依頼など"><?= htmlspecialchars($row['work_note'] ?? '') ?></textarea>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
