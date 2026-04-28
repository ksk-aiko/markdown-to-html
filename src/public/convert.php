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
        :root {
            --page-max-width: 1380px;
            --workspace-height: clamp(520px, calc(100dvh - 170px), 760px);
            --line: #bdbdbd;
        }

        html {
            overflow-y: scroll;
        }

        body {
            margin: 0;
            padding: 20px 16px;
            box-sizing: border-box;
            font-family: sans-serif;
            background-color: #efefef;
            color: #222;
        }

        .container {
            max-width: var(--page-max-width);
            margin: 0 auto;
        }

        .preview {
            height: var(--workspace-height);
            min-height: 0;
            overflow: auto;
            padding: 14px;
            background-color: #ffffff;
            border: 1px solid var(--line);
            border-radius: 0;
            box-sizing: border-box;
        }

        .actions {
            margin-top: 24px;
        }

        .back-button {
            padding: 10px 16px;
            border: 1px solid #8f8f8f;
            border-radius: 3px;
            background-color: #f7f7f7;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .preview {
                height: 45vh;
            }
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