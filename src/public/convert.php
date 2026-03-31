<?php
declare(strict_types=1);

// 追加: convert.php への直接アクセスを防ぎ、POST送信だけ受け付ける
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\MarkdownConverter;

$markdown = $_POST['markdown'] ?? '';
$mode = $_POST['mode'] ?? 'preview';

$converter = new MarkdownConverter();
$html = $converter->convert($markdown);

if ($mode === 'download') {
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="converted.html"');
    echo $html;
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview</title>
    <style>
        body {
            margin: 0;
            padding: 32px 16px;
            font-family: sans-serif;
            background-color: #f5f5f5;
            color: #222;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
        }

        .preview {
            padding: 24px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
        }

        .actions {
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Preview</h1>

        <section class="preview">
            <?= $html ?>
        </section>

        <div class="actions">
            <a href="/">Back</a>
        </div>
    </main>
</body>
</html>