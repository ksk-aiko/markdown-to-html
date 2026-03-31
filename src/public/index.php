<?php
declare(strict_types=1);

$defaultMarkdown = <<<MD
# Sample Title

- item 1
- item 2

** bold text**
MD;

$markdown = $_POST['markdown'] ?? $defaultMarkdown;
$mode = $_POST['mode'] ?? 'preview';

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown to HTML</title>
</head>
<body>
    <h1>Markdown to HTML Converter</h1>

    <form action="/convert.php" method="post">
        <label for="markdown">Markdown</label><br>
        <textarea id="markdown" name="markdown" rows="12" cols="80" placeholder="# Hello"></textarea><br><br>

        <label for="mode">Output Mode</label>
        <select id="mode" name="mode">
            <option value="preview"<?= $mode === 'preview' ? ' selected' : '' ?>>Preview</option>
            <option value="download"<?= $mode === 'download' ? ' selected' : '' ?>>Download</option>
        </select><br><br>

        <button type="submit">Convert</button>
    </form>
</body>
</html>