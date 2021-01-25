<?php

declare(strict_types=1);

namespace Beaniegel\ImageStorage;

final class Image
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function raw(): string
    {
        return $this->content;
    }

    public function base64Encoded(): string
    {
        return base64_encode($this->content);
    }
}
