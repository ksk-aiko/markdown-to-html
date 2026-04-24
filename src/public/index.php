<?php
declare(strict_types=1);

session_start();

$defaultMarkdown = <<<MD
# Sample Title

- item 1
- item 2

**bold text**
MD;

$maxLength = 20000;

$errorCode = $_POST['error'] ?? ($_GET['error'] ?? null);
$allowedErrorCodes = ['too_long', 'empty_markdown', 'invalid_csrf'];
$errorCode = in_array($errorCode, $allowedErrorCodes, true) ? $errorCode : null;

$errorMessage = null;
if ($errorCode === 'too_long') {
    $errorMessage = "Markdown must be {$maxLength} characters or less.";
} elseif ($errorCode === 'empty_markdown') {
    $errorMessage = "Markdown cannot be empty.";
} elseif ($errorCode === 'invalid_csrf') {
    $errorMessage = 'Session expired or invalid request.Please try again.';
} else {
    $errorMessage = null;
}

$markdown = $_POST['markdown'] ?? $defaultMarkdown;

$allowedModes = ['preview', 'download'];
$rawMode = $_POST['mode'] ?? 'preview';
$mode = in_array($rawMode, $allowedModes, true) ? $rawMode : 'preview';

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown to HTML</title>
    <style>
        .error-message {
            color: #b00020;
        }

        #monaco-editor {
            width: 100%;
            max-width: 980px;
            height: 420px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .is-monaco-enabled #markdown {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Markdown to HTML Converter</h1>

    <?php if ($errorMessage !== null): ?>
        <p class="error-message"><?=  htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <form id="convert-form" action="/convert.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <label for="markdown">Markdown</label><br>
        <div id="monaco-editor" aria-label="Markdown Editor"></div>
        <textarea id="markdown" name="markdown" rows="12" cols="80" maxlength="<?= $maxLength ?>"><?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea><br>
        <p>Maximum: <?= $maxLength ?> characters</p>

        <label for="mode">Output Mode</label>
        <select id="mode" name="mode">
            <option value="preview"<?= $mode === 'preview' ? ' selected' : '' ?>>Preview</option>
            <option value="download"<?= $mode === 'download' ? ' selected' : '' ?>>Download</option>
        </select><br><br>

        <button type="submit">Convert</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs/loader.js"></script>
    <script>
        (function () {
            var textarea = document.getElementById('markdown');
            var form = document.getElementById('convert-form');
            var mount = document.getElementById('monaco-editor');

            if (!window.require || !textarea || !form || !mount) {
                return;
            }

            document.body.classList.add('is-monaco-enabled');

            window.require.config({
                paths: {
                    vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs'
                }
            });

            window.require(['vs/editor/editor.main'], function () {
                var editor = monaco.editor.create(mount, {
                    value: textarea.value,
                    language: 'markdown',
                    theme: 'vs',
                    minimap: { enabled: true },
                    automaticLayout: true,
                });

                form.addEventListener('submit', function () {
                    textarea.value = editor.getValue();
                });
            });
        })();
    </script>
</body>
</html>