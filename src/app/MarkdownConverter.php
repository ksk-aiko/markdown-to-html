<?php
declare(strict_types=1);

namespace App;

final class MarkdownConverter
{
    public function convert(string $markdown): string
    {
        return nl2br(
            htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }
}