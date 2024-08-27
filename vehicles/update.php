<?php
session_start();
$errors = []; 
require '../config/config.php'; 
require '../config/validation.php';  

// ログインチェック
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header('Location: ../login/index.php');
    exit();
}


$logged_in_workclass = $_SESSION['account']['workclass'] ?? null;

function convertToJapaneseEra($year) {
    $eras = [
        ['name' => '令和', 'start' => 2019],
        ['name' => '平成', 'start' => 1989],
    ];

    foreach ($eras as $era) {
        if ($year >= $era['start']) {
            $eraYear = $year - $era['start'] + 1;
            return $era['name'] . ($eraYear === 1 ? '元' : $eraYear) . '年';
        }
    }
    return $year;
}

function generateJapaneseYearOptions($startYear, $endYear, $selectedYear) {
    $options = '';
    for ($year = $startYear; $year <= $endYear; $year++) { 
        $eraYear = convertToJapaneseEra($year);
        $selected = ($year == $selectedYear) ? 'selected' : '';
        $options .= "<option value=\"$year\" $selected>$eraYear</option>";
    }
    return $options;
}

try {
    $pdo = new PDO($dsnVehicles, $userVehicles, $passwordVehicles);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('データベース接続エラー: ' . $e->getMessage());
}

$car_id = $_GET['car_id'] ?? null;

if ($car_id && is_numeric($car_id)) {
    $car_id = intval($car_id);
} else {
    exit('無効な車両IDです。');
}

// 車両情報を取得する
$stmt = $pdo->prepare('SELECT * FROM vehicless WHERE car_id = :car_id');
$stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$stmt->execute();
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    exit('車両データが見つかりませんでした。');
}



// POSTリクエストでの更新処理
// POSTリクエストでの更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'] ?? null;

    if ($car_id && is_numeric($car_id)) {
        $car_id = intval($car_id);
        echo 'POSTされたcar_id: ' . $car_id; // ここでcar_idが正しく取得されているか確認
    } else {
        exit('無効な車両IDです。');
    }

    try {
        // データベースの更新処理
        $sql = '
            UPDATE vehicless 
            SET 
                car_number_name = :car_number_name,  
                car_model = :car_model,
                car_name = :car_name,
                car_transpottaition = :car_transpottaition,
                car_classification_no = :car_classification_no,
                car_purpose = :car_purpose,
                car_number01 = :car_number01,
                car_number02 = :car_number02,
                car_chassis_number = :car_chassis_number,
                first_registration_year = :first_registration_year,
                first_registration_month = :first_registration_month,
                vehicle_inspection_year = :vehicle_inspection_year,
                vehicle_inspection_month = :vehicle_inspection_month,
                vehicle_inspection_day = :vehicle_inspection_day,
                compulsory_automobile_year = :compulsory_automobile_year,
                compulsory_automobile_month = :compulsory_automobile_month,
                compulsory_automobile_day = :compulsory_automobile_day,
                owner_name = :owner_name,
                owner_address = :owner_address,
                user_name = :user_name,
                user_address = :user_address,
                headquarters_address = :headquarters_address
            WHERE car_id = :car_id';

        $stmt_update = $pdo->prepare($sql);

        // 値をバインド
        $stmt_update->bindValue(':car_number_name', $_POST['car_number_name'], PDO::PARAM_INT);  
        $stmt_update->bindValue(':car_model', $_POST['car_model'], PDO::PARAM_STR);
        $stmt_update->bindValue(':car_name', $_POST['car_name'], PDO::PARAM_STR);
        $stmt_update->bindValue(':car_transpottaition', $_POST['car_transpottaition'], PDO::PARAM_STR);
        $stmt_update->bindValue(':car_classification_no', $_POST['car_classification_no'], PDO::PARAM_INT);
        $stmt_update->bindValue(':car_purpose', $_POST['car_purpose'], PDO::PARAM_STR);
        $stmt_update->bindValue(':car_number01', $_POST['car_number01'], PDO::PARAM_INT);
        $stmt_update->bindValue(':car_number02', $_POST['car_number02'], PDO::PARAM_INT);
        $stmt_update->bindValue(':car_chassis_number', $_POST['car_chassis_number'], PDO::PARAM_STR);
        $stmt_update->bindValue(':first_registration_year', $_POST['first_registration_year'], PDO::PARAM_INT);
        $stmt_update->bindValue(':first_registration_month', $_POST['first_registration_month'], PDO::PARAM_INT);
        $stmt_update->bindValue(':vehicle_inspection_year', $_POST['vehicle_inspection_year'], PDO::PARAM_INT);
        $stmt_update->bindValue(':vehicle_inspection_month', $_POST['vehicle_inspection_month'], PDO::PARAM_INT);
        $stmt_update->bindValue(':vehicle_inspection_day', $_POST['vehicle_inspection_day'], PDO::PARAM_INT);
        $stmt_update->bindValue(':compulsory_automobile_year', $_POST['compulsory_automobile_year'], PDO::PARAM_INT);
        $stmt_update->bindValue(':compulsory_automobile_month', $_POST['compulsory_automobile_month'], PDO::PARAM_INT);
        $stmt_update->bindValue(':compulsory_automobile_day', $_POST['compulsory_automobile_day'], PDO::PARAM_INT);
        $stmt_update->bindValue(':owner_name', $_POST['owner_name'], PDO::PARAM_STR);
        $stmt_update->bindValue(':owner_address', $_POST['owner_address'], PDO::PARAM_STR);
        $stmt_update->bindValue(':user_name', $_POST['user_name'], PDO::PARAM_STR);
        $stmt_update->bindValue(':user_address', $_POST['user_address'], PDO::PARAM_STR);
        $stmt_update->bindValue(':headquarters_address', $_POST['headquarters_address'], PDO::PARAM_INT);
        $stmt_update->bindValue(':car_id', $car_id, PDO::PARAM_INT);
        
        // SQL文の実行
        if ($stmt_update->execute()) {
            // 更新が成功したらlist.phpにリダイレクト
            header('Location: list.php');
            exit();
        } else {
            echo '更新に失敗しました。';
        }
    } catch (PDOException $e) {
        $errors[] = 'データベースエラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        var_dump($errors); // デバッグ用
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <title>車両編集</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="register.css">
        <!-- <script src="https://yubinbango.github.io/yubinbango/yubinbango.js"></script> -->
    </head>

    <header>
        <h1>車両編集</h1>
        <!-- パンクズナビ -->
        <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="../main_menu/index.php">
                    <span itemprop="name">メインメニュー</span>
                </a>
                <meta itemprop="position" content="1" />
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="list.php">
                    <span itemprop="name">車両一覧</span>
                </a>
                <meta itemprop="position" content="2" />
            </li>
        </ol>
    </header>

    <body>
    <form action="update.php" method="POST">
    <input type="hidden" name="car_id" value="<?= htmlspecialchars($vehicle['car_id'], ENT_QUOTES, 'UTF-8'); ?>">
    <div class="h-adr">
        <table class="first-table">
            <tr>
                <th>車番<span class="required"> *</span></th>
                <td>
                    <input type="text" name="car_number_name" value="<?= htmlspecialchars($vehicle['car_number_name'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
                <th>車種<span class="required"> *</span></th>
                <td>
                    <input type="text" name="car_model" value="<?= htmlspecialchars($vehicle['car_model'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>車名<span class="required"> *</span></th>
                <td>
                    <input type="text" name="car_name" value="<?= htmlspecialchars($vehicle['car_name'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>車両番号<span class="required"> *</span></th>
                <td colspan="3">
                    <input type="text" class="text small" name="car_transpottaition" value="<?= htmlspecialchars($vehicle['car_transpottaition'], ENT_QUOTES, 'UTF-8'); ?>">&nbsp;
                    <input type="text" class="text small" name="car_classification_no" value="<?= htmlspecialchars($vehicle['car_classification_no'], ENT_QUOTES, 'UTF-8'); ?>">&nbsp;
                    <input type="text" class="text small" name="car_purpose" value="<?= htmlspecialchars($vehicle['car_purpose'], ENT_QUOTES, 'UTF-8'); ?>">&nbsp;
                    <input type="text" class="text small" name="car_number01" value="<?= htmlspecialchars($vehicle['car_number01'], ENT_QUOTES, 'UTF-8'); ?>"> -
                    <input type="text" class="text small" name="car_number02" value="<?= htmlspecialchars($vehicle['car_number02'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>車台番号<span class="required"> *</span></th>
                <td>
                    <input type="text" class="text" name="car_chassis_number" value="<?= htmlspecialchars($vehicle['car_chassis_number'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>初年度登録年月<span class="required"> *</span></th>
                <td colspan="2">
                    <select name="first_registration_year">
                        <?= generateJapaneseYearOptions(1999, date("Y") + 1, $vehicle['first_registration_year']) ?>
                    </select>年
                    <select name="first_registration_month">
                        <?= generateMonthOptions($vehicle['first_registration_month']) ?>
                    </select>月
                </td>
            </tr>
            <tr>
                <th>車検有効期限<span class="required"> *</span></th>
                <td colspan="3">
                    <select name="vehicle_inspection_year">
                        <?= generateJapaneseYearOptions(date("Y") - 3, date("Y") + 3, $vehicle['vehicle_inspection_year']) ?>
                    </select>年
                    <select name="vehicle_inspection_month">
                        <?= generateMonthOptions($vehicle['vehicle_inspection_month']) ?>
                    </select>月
                    <select name="vehicle_inspection_day">
                        <?= generateDayOptions($vehicle['vehicle_inspection_day']) ?>
                    </select>日
                </td>
            </tr>
            <tr>
                <th>自賠責有効期限<span class="required"> *</span></th>
                <td colspan="3">
                    <select name="compulsory_automobile_year">
                        <?= generateJapaneseYearOptions(date("Y") - 2, date("Y") + 2, $vehicle['compulsory_automobile_year']) ?>
                    </select>年
                    <select name="compulsory_automobile_month">
                        <?= generateMonthOptions($vehicle['compulsory_automobile_month']) ?>
                    </select>月
                    <select name="compulsory_automobile_day">
                        <?= generateDayOptions($vehicle['compulsory_automobile_day']) ?>
                    </select>日
                </td>
            </tr>
        </table>
    </div>

    <div class="h-adr">
        <table class="second-table">
            <tr>
                <th colspan="2">所有者・使用者情報</th>
            </tr>
            <tr>
                <th>所有者の氏名<br>又は名称</th>
                <td colspan="2">
                    <input type="text" class="input large" name="owner_name" value="<?= htmlspecialchars($vehicle['owner_name'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>所有者の住所</th>
                <td colspan="2">
                    <input type="text" class="input large" name="owner_address" value="<?= htmlspecialchars($vehicle['owner_address'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>使用者の氏名<br>又は名称</th>
                <td colspan="2">
                    <input type="text" class="input large" name="user_name" value="<?= htmlspecialchars($vehicle['user_name'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>使用者の住所</th>
                <td colspan="2">
                    <input type="text" class="input large" name="user_address" value="<?= htmlspecialchars($vehicle['user_address'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr>
                <th>使用本拠地の<br>位置<span class="required"> *</span></th>
                <td>
                    <select name="headquarters_address" class="large-select">
                        <?= generateSelectOptions(HEADQUARTERS_ADDRESS, $vehicle['headquarters_address']); ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>

    <?php if (isset($logged_in_workclass) && ($logged_in_workclass === 1 || $logged_in_workclass === 2)): ?>
        <div class="flex">
        <input type="submit" value="更新">
            
            <input type="submit" value="休車" name="submit">
        </div>
    <?php endif; ?>
</form>