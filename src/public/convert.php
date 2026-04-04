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

// Changed: if input is too long, return to index via POST and stop processing
if (mb_strlen($markdown) > $maxLength) {
    http_response_code(422);
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
    </head>
    <body>
        <!-- Added: preserve error code and previous input values -->
        <form id="error-return-form" action="/" method="post">
            <input type="hidden" name="error" value="too_long">
            <input type="hidden" name="markdown" value="<?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <noscript><button type="submit">Back</button></noscript>
        </form>

        <!-- Added: auto-submit only in the too-long branch -->
        <script>
            document.getElementById('error-return-form').submit();
        </script>
    </body>
    </html>
    <?php
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
            <!-- Changed: normal back form (no forced error, no auto-submit) -->
            <form action="/" method="post">
                <input type="hidden" name="markdown" value="<?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button type="submit" class="back-button">Back</button>
            </form>
        </div>
    </main>
</body>
</html>