<?php
declare(strict_types=1);

$defaultMarkdown = <<<MD
# Sample Title

- item 1
- item 2

**bold text**
MD;

// Added: define the same maximum length used by convert.php
$maxLength = 20000;

// Added: accept an optional error code from query string
$errorCode = $_GET['error'] ?? null;

// Added: map error code to a user-friendly message
$errorMessage = null;
if ($errorCode === 'too_long') {
    $errorMessage = "Markdown must be {$maxLength} characters or less.";
}

// Changed: restore posted values when returning from the preview page
$markdown = $_POST['markdown'] ?? $defaultMarkdown;
$mode = $_POST['mode'] ?? 'preview';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown to HTML</title>
     <!-- Added: dedicated style for error messages -->
    <style>
        .error-message {
            color: #b00020;
        }
    </style>
</head>
<body>
    <h1>Markdown to HTML Converter</h1>

    <!-- Added: show an error message when an error code is provided -->
    <?php if ($errorMessage !== null): ?>
        <p class="error-message"><?=  htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="/convert.php" method="post">
        <label for="markdown">Markdown</label><br>
        <!-- Added: inform the browser and the user about the input limit -->
        <textarea id="markdown" name="markdown" rows="12" cols="80" maxlength="<?= $maxLength ?>"><?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea><br>
        <p>Maximum: <?= $maxLength ?> characters</p>

        <label for="mode">Output Mode</label>
        <select id="mode" name="mode">
            <option value="preview"<?= $mode === 'preview' ? ' selected' : '' ?>>Preview</option>
            <option value="download"<?= $mode === 'download' ? ' selected' : '' ?>>Download</option>
        </select><br><br>

        <button type="submit">Convert</button>
    </form>
</body>
</html>