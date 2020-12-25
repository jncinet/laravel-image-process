<?php

namespace Jncinet\ImageProcess\Contracts;

interface GatewayInterface
{
    public function url();

    public function resize($mode = 0, array $params = []);

    public function watermark(int $type, array $params = []);

    public function round($radius);

    public function path($path);

    /**
     * @return array
     */
    public function info(): array;
}