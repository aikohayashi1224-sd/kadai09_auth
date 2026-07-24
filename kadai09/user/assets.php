<?php
$page_title = '財産';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'bank_name', 'bank_branch', 'bank_type', 'bank_number', 'bank_note',
        'property_address', 'property_type', 'property_note',
        'investment_company', 'investment_type', 'investment_note',
        'insurance_company', 'insurance_number', 'insurance_receiver', 'insurance_note',
        'card_company', 'card_brand', 'card_note',
        'loan_company', 'loan_note'
    ];
    $data = ['user_id' => $user_id];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? '';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO assets
                (user_id,
                 bank_name, bank_branch, bank_type, bank_number, bank_note,
                 property_address, property_type, property_note,
                 investment_company, investment_type, investment_note,
                 insurance_company, insurance_number, insurance_receiver, insurance_note,
                 card_company, card_brand, card_note,
                 loan_company, loan_note)
            VALUES
                (:user_id,
                 :bank_name, :bank_branch, :bank_type, :bank_number, :bank_note,
                 :property_address, :property_type, :property_note,
                 :investment_company, :investment_type, :investment_note,
                 :insurance_company, :insurance_number, :insurance_receiver, :insurance_note,
                 :card_company, :card_brand, :card_note,
                 :loan_company, :loan_note)
            ON DUPLICATE KEY UPDATE
                bank_name           = VALUES(bank_name),
                bank_branch         = VALUES(bank_branch),
                bank_type           = VALUES(bank_type),
                bank_number         = VALUES(bank_number),
                bank_note           = VALUES(bank_note),
                property_address    = VALUES(property_address),
                property_type       = VALUES(property_type),
                property_note       = VALUES(property_note),
                investment_company  = VALUES(investment_company),
                investment_type     = VALUES(investment_type),
                investment_note     = VALUES(investment_note),
                insurance_company   = VALUES(insurance_company),
                insurance_number    = VALUES(insurance_number),
                insurance_receiver  = VALUES(insurance_receiver),
                insurance_note      = VALUES(insurance_note),
                card_company        = VALUES(card_company),
                card_brand          = VALUES(card_brand),
                card_note           = VALUES(card_note),
                loan_company        = VALUES(loan_company),
                loan_note           = VALUES(loan_note)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM assets WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>財産</h2>
            <p class="page-desc">メインの情報を1件記入し、書ききれない内容は備考欄に記載してください。</p>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">

                <h3 class="section-heading">銀行・金融機関</h3>
                <div class="form-group">
                    <label>銀行名</label>
                    <input type="text" name="bank_name" value="<?= htmlspecialchars($row['bank_name'] ?? '') ?>" placeholder="例：○○銀行">
                </div>
                <div class="form-group">
                    <label>支店名</label>
                    <input type="text" name="bank_branch" value="<?= htmlspecialchars($row['bank_branch'] ?? '') ?>" placeholder="例：渋谷支店">
                </div>
                <div class="form-group">
                    <label>口座種別</label>
                    <select name="bank_type">
                        <?php foreach (['', '普通', '当座', '貯蓄'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['bank_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>口座番号</label>
                    <input type="text" name="bank_number" value="<?= htmlspecialchars($row['bank_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>補足・その他の口座</label>
                    <textarea name="bank_note"><?= htmlspecialchars($row['bank_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">不動産</h3>
                <div class="form-group">
                    <label>所在地</label>
                    <input type="text" name="property_address" value="<?= htmlspecialchars($row['property_address'] ?? '') ?>" placeholder="例：東京都○○区△△1-2-3">
                </div>
                <div class="form-group">
                    <label>種別</label>
                    <select name="property_type">
                        <?php foreach (['', '持ち家（一戸建て）', '持ち家（マンション）', '土地', 'その他'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['property_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>補足</label>
                    <textarea name="property_note"><?= htmlspecialchars($row['property_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">株式・投資信託</h3>
                <div class="form-group">
                    <label>証券会社名</label>
                    <input type="text" name="investment_company" value="<?= htmlspecialchars($row['investment_company'] ?? '') ?>" placeholder="例：○○証券">
                </div>
                <div class="form-group">
                    <label>口座種別</label>
                    <select name="investment_type">
                        <?php foreach (['', '特定口座', 'NISA', 'iDeCo', '一般口座', 'その他'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['investment_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>補足・その他の証券口座</label>
                    <textarea name="investment_note"><?= htmlspecialchars($row['investment_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">保険</h3>
                <div class="form-group">
                    <label>保険会社名</label>
                    <input type="text" name="insurance_company" value="<?= htmlspecialchars($row['insurance_company'] ?? '') ?>" placeholder="例：○○生命">
                </div>
                <div class="form-group">
                    <label>証券番号</label>
                    <input type="text" name="insurance_number" value="<?= htmlspecialchars($row['insurance_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>受取人</label>
                    <input type="text" name="insurance_receiver" value="<?= htmlspecialchars($row['insurance_receiver'] ?? '') ?>" placeholder="例：山田花子（母）">
                </div>
                <div class="form-group">
                    <label>補足・その他の保険</label>
                    <textarea name="insurance_note"><?= htmlspecialchars($row['insurance_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">クレジットカード</h3>
                <div class="form-group">
                    <label>カード会社</label>
                    <input type="text" name="card_company" value="<?= htmlspecialchars($row['card_company'] ?? '') ?>" placeholder="例：○○カード">
                </div>
                <div class="form-group">
                    <label>ブランド</label>
                    <select name="card_brand">
                        <?php foreach (['', 'Visa', 'Mastercard', 'JCB', 'American Express', 'Diners', 'その他'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['card_brand'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>補足・その他のカード</label>
                    <textarea name="card_note"><?= htmlspecialchars($row['card_note'] ?? '') ?></textarea>
                </div>

                <h3 class="section-heading">ローン・借入</h3>
                <div class="form-group">
                    <label>借入先</label>
                    <input type="text" name="loan_company" value="<?= htmlspecialchars($row['loan_company'] ?? '') ?>" placeholder="例：○○銀行 住宅ローン">
                </div>
                <div class="form-group">
                    <label>補足</label>
                    <textarea name="loan_note"><?= htmlspecialchars($row['loan_note'] ?? '') ?></textarea>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>