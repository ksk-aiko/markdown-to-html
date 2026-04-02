<?php
declare(strict_types=1);

// Added: allow only POST access to this endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\MarkdownConverter;

$markdown = $_POST['markdown'] ?? '';
$mode = $_POST['mode'] ?? 'preview';

// Added: define a simple maximum input size
$maxLength = 20000;

// Added: reject overly large input and redirect to index with an error code
if (mb_strlen($markdown) > $maxLength) {
    header('Location: /?error=too_long', true, 303);
    exit;
}

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

        .back-button {
            padding: 10px 16px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            background-color: #ffffff;
            cursor: pointer;
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
            <form action="/" method="post">
                <input type="hidden" name="markdown" value="<?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button type="submit" class="back-button">Back</button>
            </form>
        </div>
    </main>
</body>
</html>