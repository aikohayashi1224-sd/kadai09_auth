<?php
session_start();

if (!isset($_SESSION['trustee_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$trustee_id = $_SESSION['trustee_id'];
$owner_id   = $_SESSION['trustee_user_id'];
$section    = $_GET['section'] ?? '';

// 権限チェック
$stmt = $pdo->prepare("SELECT is_visible FROM trustees_permissions WHERE trustee_id = ? AND section = ? AND item = 'all'");
$stmt->execute([$trustee_id, $section]);
$perm = $stmt->fetch();

if (!$perm || !$perm['is_visible']) {
    header('Location: dashboard.php');
    exit;
}

$section_labels = [
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

$page_title = $section_labels[$section] ?? 'エンディングノート';

// データ取得
$data  = [];
$items = [];

switch ($section) {
    case 'profile':
        $stmt = $pdo->prepare("SELECT * FROM profile WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'medical':
        $stmt = $pdo->prepare("SELECT * FROM medical WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'assets':
        $stmt = $pdo->prepare("SELECT * FROM assets WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'digital':
        $stmt = $pdo->prepare("SELECT * FROM digital WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'work':
        $stmt = $pdo->prepare("SELECT * FROM work WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'housing':
        $stmt = $pdo->prepare("SELECT * FROM housing WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'belongings':
        $stmt = $pdo->prepare("SELECT * FROM belongings WHERE user_id = ? ORDER BY category, id");
        $stmt->execute([$owner_id]);
        $items = $stmt->fetchAll();
        break;
    case 'funeral':
        $stmt = $pdo->prepare("SELECT * FROM funeral WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'contacts':
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY contact_type, id");
        $stmt->execute([$owner_id]);
        $items = $stmt->fetchAll();
        break;
    case 'will':
        $stmt = $pdo->prepare("SELECT * FROM will WHERE user_id = ?");
        $stmt->execute([$owner_id]);
        $data = $stmt->fetch() ?: [];
        break;
    case 'messages':
        $stmt = $pdo->prepare("SELECT message FROM messages WHERE user_id = ? AND trustee_id = ?");
        $stmt->execute([$owner_id, $trustee_id]);
        $data = $stmt->fetch() ?: [];
        break;
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function row($label, $value) {
    if ($value === '' || $value === null) return;
    echo "<tr><th>" . htmlspecialchars($label) . "</th><td>" . htmlspecialchars($value) . "</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?> - エンディングノート</title>
    <link rel="stylesheet" href="/kadai09/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 class="site-title">📋 エンディングノート（引受人）</h1>
            <nav class="site-nav">
                <a href="dashboard.php">← 一覧に戻る</a>
                <a href="logout.php">ログアウト</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2><?= h($page_title) ?></h2>

            <?php if ($section === 'belongings' || $section === 'contacts'): ?>
                <!-- 複数件テーブル表示 -->
                <?php if (empty($items)): ?>
                    <p style="color:#999;">データがありません。</p>
                <?php else: ?>
                    <table>
                        <?php if ($section === 'belongings'): ?>
                        <thead>
                            <tr>
                                <th>カテゴリ</th>
                                <th>品名</th>
                                <th>ブランド</th>
                                <th>購入価格</th>
                                <th>保管場所</th>
                                <th>処分方法</th>
                                <th>渡す相手</th>
                                <th>備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= h($item['category']) ?></td>
                                <td><?= h($item['item_name']) ?></td>
                                <td><?= h($item['brand']) ?></td>
                                <td><?= $item['purchase_price'] ? number_format($item['purchase_price']) . '円' : '-' ?></td>
                                <td><?= h($item['storage_location']) ?></td>
                                <td><?= h($item['disposal']) ?></td>
                                <td><?= h($item['disposal_person']) ?></td>
                                <td><?= h($item['item_note']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php elseif ($section === 'contacts'): ?>
                        <thead>
                            <tr>
                                <th>種別</th>
                                <th>氏名</th>
                                <th>続柄</th>
                                <th>電話番号</th>
                                <th>メール</th>
                                <th>備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= h($item['contact_type']) ?></td>
                                <td><?= h($item['name']) ?></td>
                                <td><?= h($item['relationship']) ?></td>
                                <td><?= h($item['phone']) ?></td>
                                <td><?= h($item['email']) ?></td>
                                <td><?= h($item['contact_note']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php endif; ?>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'messages'): ?>
                <!-- メッセージ表示 -->
                <?php if (empty($data['message'])): ?>
                    <p style="color:#999;">メッセージはありません。</p>
                <?php else: ?>
                    <div style="background:#f9f6f0; border-left:4px solid #c5a059; padding:20px; border-radius:4px; line-height:2; white-space:pre-wrap;"><?= h($data['message']) ?></div>
                <?php endif; ?>

            <?php else: ?>
                <!-- 1件テーブル表示 -->
                <?php if (empty($data)): ?>
                    <p style="color:#999;">データがありません。</p>
                <?php else: ?>
                <table>
                    <tbody>
                    <?php if ($section === 'profile'): ?>
                        <?php row('姓名', ($data['last_name'] ?? '') . ' ' . ($data['first_name'] ?? '')); ?>
                        <?php row('ふりがな', ($data['last_name_kana'] ?? '') . ' ' . ($data['first_name_kana'] ?? '')); ?>
                        <?php row('生年月日', $data['birth_date'] ?? ''); ?>
                        <?php row('血液型', $data['blood_type'] ?? ''); ?>
                        <?php row('住所', $data['address'] ?? ''); ?>
                        <?php row('出身地', $data['hometown'] ?? ''); ?>
                        <?php row('マイナンバー', $data['my_number'] ?? ''); ?>

                    <?php elseif ($section === 'medical'): ?>
                        <?php row('担当医', $data['doctor_name'] ?? ''); ?>
                        <?php row('病院名', $data['doctor_hospital'] ?? ''); ?>
                        <?php row('病院電話', $data['doctor_phone'] ?? ''); ?>
                        <?php row('既往歴・持病', $data['diseases'] ?? ''); ?>
                        <?php row('アレルギー', $data['allergies'] ?? ''); ?>
                        <?php row('常用薬', $data['medications'] ?? ''); ?>
                        <?php row('延命治療', $data['life_support'] ?? ''); ?>
                        <?php row('臓器提供', $data['organ_donation'] ?? ''); ?>
                        <?php
                        // 臓器提供の詳細（提供する場合のみ表示）
                        $organ_map = [
                            'organ_heart'           => '心臓',
                            'organ_lung'            => '肺',
                            'organ_liver'           => '肝臓',
                            'organ_kidney'          => '腎臓',
                            'organ_pancreas'        => '膵臓',
                            'organ_small_intestine' => '小腸',
                            'organ_eye'             => '眼球',
                            'organ_skin'            => '皮膚',
                            'organ_bone'            => '骨・骨髄',
                        ];
                        $donated = [];
                        foreach ($organ_map as $col => $label) {
                            if (!empty($data[$col])) $donated[] = $label;
                        }
                        if ($donated) {
                            echo '<tr><th>提供する臓器</th><td>' . h(implode('、', $donated)) . '</td></tr>';
                        }
                        ?>

                    <?php elseif ($section === 'assets'): ?>
                        <?php row('銀行名・支店', trim(($data['bank_name'] ?? '') . ' ' . ($data['bank_branch'] ?? '') . ' ' . ($data['bank_type'] ?? ''))); ?>
                        <?php row('口座番号', $data['bank_number'] ?? ''); ?>
                        <?php row('銀行備考', $data['bank_note'] ?? ''); ?>
                        <?php row('不動産住所', $data['property_address'] ?? ''); ?>
                        <?php row('不動産種別', $data['property_type'] ?? ''); ?>
                        <?php row('不動産備考', $data['property_note'] ?? ''); ?>
                        <?php row('証券会社', $data['investment_company'] ?? ''); ?>
                        <?php row('投資種別', $data['investment_type'] ?? ''); ?>
                        <?php row('投資備考', $data['investment_note'] ?? ''); ?>
                        <?php row('保険会社', $data['insurance_company'] ?? ''); ?>
                        <?php row('証券番号', $data['insurance_number'] ?? ''); ?>
                        <?php row('受取人', $data['insurance_receiver'] ?? ''); ?>
                        <?php row('保険備考', $data['insurance_note'] ?? ''); ?>
                        <?php row('クレジットカード', trim(($data['card_company'] ?? '') . ' ' . ($data['card_brand'] ?? ''))); ?>
                        <?php row('カード備考', $data['card_note'] ?? ''); ?>
                        <?php row('ローン会社', $data['loan_company'] ?? ''); ?>
                        <?php row('ローン備考', $data['loan_note'] ?? ''); ?>

                    <?php elseif ($section === 'digital'): ?>
                        <?php row('メールアドレス', $data['email_address'] ?? ''); ?>
                        <?php row('メール補足', $data['email_note'] ?? ''); ?>
                        <?php row('SNSサービス', $data['sns_service'] ?? ''); ?>
                        <?php row('SNS ID', $data['sns_id'] ?? ''); ?>
                        <?php row('その他SNS', $data['sns_note'] ?? ''); ?>
                        <?php row('死後の対応', $data['sns_after_death'] ?? ''); ?>
                        <?php row('死後の投稿内容', $data['sns_post_message'] ?? ''); ?>
                        <?php row('投稿依頼先', $data['sns_post_person'] ?? ''); ?>
                        <?php row('パスワード保管場所', $data['sns_password_location'] ?? ''); ?>
                        <?php row('サブスク一覧', $data['subscription_service'] ?? ''); ?>
                        <?php row('月額合計', !empty($data['subscription_fee']) ? number_format($data['subscription_fee']) . '円' : ''); ?>
                        <?php row('解約方法', $data['subscription_cancel'] ?? ''); ?>
                        <?php row('サブスク備考', $data['subscription_note'] ?? ''); ?>
                        <?php row('パスワード管理', $data['password_management'] ?? ''); ?>
                        <?php row('デバイス処分', $data['device_disposal'] ?? ''); ?>

                    <?php elseif ($section === 'work'): ?>
                        <?php row('雇用形態', $data['employment_type'] ?? ''); ?>
                        <?php row('会社名', $data['company_name'] ?? ''); ?>
                        <?php row('会社電話', $data['company_phone'] ?? ''); ?>
                        <?php row('会社住所', $data['company_address'] ?? ''); ?>
                        <?php row('担当者', $data['contact_person'] ?? ''); ?>
                        <?php row('担当者電話', $data['contact_phone'] ?? ''); ?>
                        <?php row('税理士', $data['accountant_name'] ?? ''); ?>
                        <?php row('税理士電話', $data['accountant_phone'] ?? ''); ?>
                        <?php row('備考', $data['work_note'] ?? ''); ?>

                    <?php elseif ($section === 'housing'): ?>
                        <?php row('住居種別', $data['housing_type'] ?? ''); ?>
                        <?php row('物件名', $data['property_name'] ?? ''); ?>
                        <?php row('住所', $data['property_address'] ?? ''); ?>
                        <?php row('管理会社', $data['management_company'] ?? ''); ?>
                        <?php row('管理会社電話', $data['management_phone'] ?? ''); ?>
                        <?php row('契約満了日', $data['contract_end_date'] ?? ''); ?>
                        <?php row('保証人', $data['guarantor_name'] ?? ''); ?>
                        <?php row('保証人電話', $data['guarantor_phone'] ?? ''); ?>
                        <?php row('住宅ローン銀行', $data['mortgage_bank'] ?? ''); ?>
                        <?php row('電力会社', $data['utility_electric'] ?? ''); ?>
                        <?php row('ガス会社', $data['utility_gas'] ?? ''); ?>
                        <?php row('水道', $data['utility_water'] ?? ''); ?>
                        <?php row('インターネット', $data['internet_provider'] ?? ''); ?>
                        <?php row('携帯キャリア', $data['phone_carrier'] ?? ''); ?>
                        <?php row('備考', $data['housing_note'] ?? ''); ?>

                    <?php elseif ($section === 'funeral'): ?>
                        <?php row('葬儀形式', $data['funeral_type'] ?? ''); ?>
                        <?php row('宗派', $data['religion'] ?? ''); ?>
                        <?php row('希望・メッセージ', $data['funeral_note'] ?? ''); ?>

                    <?php elseif ($section === 'will'): ?>
                        <?php row('遺言書', $data['will_exists'] ?? ''); ?>
                        <?php row('保管場所', $data['will_location'] ?? ''); ?>
                        <?php row('形見分け', $data['keepsake_note'] ?? ''); ?>
                        <?php row('寄付希望', $data['donation_note'] ?? ''); ?>

                    <?php endif; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>