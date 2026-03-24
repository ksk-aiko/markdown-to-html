<?php
declare(strict_types=1);

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
</head>
<body>
    <h1>Preview</h1>
    <div><?= $html ?></div>
    <hr>
    <a href="/">Back</a>
</body>
</html>