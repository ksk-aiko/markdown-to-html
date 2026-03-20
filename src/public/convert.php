<?php
declare(strict_types=1);

$markdown = $_POST['markdown'] ?? '';
$mode = $_POST['mode'] ?? 'preview';

/*
 * まずは最小実装:
 * - XSS回避のためエスケープ
 * - 改行だけ <br> に変換
 * 後で Parsedown に置き換える
 */
$html = nl2br(htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

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