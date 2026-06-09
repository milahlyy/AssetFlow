<?php
require_once 'database/db.php';

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function setup_env($key, $default = null) {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function split_sql_statements($sql) {
    $statements = [];
    $statement = '';
    $length = strlen($sql);
    $quote = null;
    $escape = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];

        if ($quote !== null) {
            $statement .= $char;

            if ($escape) {
                $escape = false;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                continue;
            }

            if ($char === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
            $statement .= $char;
            continue;
        }

        if ($char === ';') {
            $trimmed = trim($statement);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $statement = '';
            continue;
        }

        $statement .= $char;
    }

    $trimmed = trim($statement);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

function table_count($conn, $table) {
    try {
        return (int) $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (PDOException $e) {
        return null;
    }
}

$setupToken = setup_env('SETUP_TOKEN');
$providedToken = $_GET['token'] ?? $_POST['token'] ?? '';
$authorized = $setupToken && hash_equals($setupToken, $providedToken);
$messages = [];
$error = null;

if (!$setupToken) {
    http_response_code(403);
    $error = 'SETUP_TOKEN belum diset di Railway Variables.';
} elseif (!$authorized) {
    http_response_code(403);
    $error = 'Token setup salah atau belum dikirim.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset_import') {
        try {
            $sqlPath = __DIR__ . '/database/assetflow.sql';
            if (!is_file($sqlPath)) {
                throw new RuntimeException('File database/assetflow.sql tidak ditemukan.');
            }

            $sql = file_get_contents($sqlPath);
            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('File database/assetflow.sql kosong atau gagal dibaca.');
            }

            $conn->exec('SET FOREIGN_KEY_CHECKS=0');
            $conn->exec('DROP TABLE IF EXISTS `loans`');
            $conn->exec('DROP TABLE IF EXISTS `assets`');
            $conn->exec('DROP TABLE IF EXISTS `users`');
            $conn->exec('SET FOREIGN_KEY_CHECKS=1');

            $count = 0;
            foreach (split_sql_statements($sql) as $statement) {
                $conn->exec($statement);
                $count++;
            }

            $messages[] = "Import selesai. $count SQL statement berhasil dijalankan.";
        } catch (Throwable $e) {
            http_response_code(500);
            $error = $e->getMessage();
        }
    } else {
        http_response_code(400);
        $error = 'Action tidak valid.';
    }
}

$counts = [
    'users' => $authorized ? table_count($conn, 'users') : null,
    'assets' => $authorized ? table_count($conn, 'assets') : null,
    'loans' => $authorized ? table_count($conn, 'loans') : null,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AssetFlow Railway Setup</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        main {
            width: min(680px, calc(100% - 32px));
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 12px 32px rgba(31, 41, 55, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        p {
            line-height: 1.5;
        }

        .notice {
            padding: 12px 14px;
            border-radius: 6px;
            margin: 14px 0;
        }

        .ok {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }

        th, td {
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 8px;
        }

        button {
            border: 0;
            border-radius: 6px;
            background: #b91c1c;
            color: white;
            font-weight: 700;
            padding: 12px 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main>
        <h1>AssetFlow Railway Setup</h1>
        <p>Halaman sementara untuk reset database Railway dan import seed dari <strong>database/assetflow.sql</strong>.</p>

        <?php if ($error): ?>
            <div class="notice error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php foreach ($messages as $message): ?>
            <div class="notice ok"><?= h($message) ?></div>
        <?php endforeach; ?>

        <?php if ($authorized): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tabel</th>
                        <th>Jumlah row sekarang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($counts as $table => $count): ?>
                        <tr>
                            <td><?= h($table) ?></td>
                            <td><?= $count === null ? 'Belum ada tabel' : h($count) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="post" onsubmit="return confirm('Reset database Railway dan import ulang assetflow.sql? Data lama akan hilang.');">
                <input type="hidden" name="token" value="<?= h($providedToken) ?>">
                <input type="hidden" name="action" value="reset_import">
                <button type="submit">Reset & Import Database</button>
            </form>
        <?php else: ?>
            <p>Set <strong>SETUP_TOKEN</strong> di Railway Variables, lalu buka halaman ini dengan <strong>?token=TOKEN_KAMU</strong>.</p>
        <?php endif; ?>
    </main>
</body>
</html>
