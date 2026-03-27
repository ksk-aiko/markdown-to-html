<?php
declare(strict_types=1);

namespace App;

use Parsedown;

final class MarkdownConverter
{
    private Parsedown $parser;

    public function __construct()
    {
        $this->parser = new Parsedown();
        $this->parser->setSafeMode(true);

    }

    public function convert(string $markdown): string
    {
        return $this->parser->text($markdown);
    }
}