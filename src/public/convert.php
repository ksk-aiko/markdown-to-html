<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\MarkdownConverter;

$markdown = $_POST['markdown'] ?? '';

$allowedModes = ['preview', 'download'];
$rawMode = $_POST['mode'] ?? 'preview';
$mode = in_array($rawMode, $allowedModes, true) ? $rawMode : 'preview';

$maxLength = 20000;

function sendSecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: noreferrer');
    header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");
}

function returnWithError(string $errorCode, string $markdown, string $mode): void
{
    http_response_code(422);
    sendSecurityHeaders();
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
    </head>
    <body>
        <form id="error-return-form" action="/" method="post">
            <input type="hidden" name="error" value="<?= htmlspecialchars($errorCode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="hidden" name="markdown" value="<?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <noscript><button type="submit">Back</button></noscript>
        </form>
        <script>
            document.getElementById('error-return-form').submit();
        </script>
    </body>
    </html>
    <?php
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $csrfToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    sendSecurityHeaders();
    echo 'Forbidden';
    exit;
}

if (trim($markdown) === '') {
    returnWithError('empty_markdown', $markdown, $mode);
}

if (mb_strlen($markdown) > $maxLength) {
    returnWithError('too_long', $markdown, $mode);
}

$converter = new MarkdownConverter();
$html = $converter->convert($markdown);

if ($mode === 'download') {
    sendSecurityHeaders();
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