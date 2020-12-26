<?php

namespace Jncinet\ImageProcess\Contracts;

/**
 * Interface GatewayInterface
 * @package Jncinet\ImageProcess\Contracts
 */
interface GatewayInterface
{
    /**
     * @return mixed
     */
    public function url(): string;

    /**
     * @param int $mode
     * @param array $params
     * @return mixed
     */
    public function resize($mode = 0, array $params = []);

    /**
     * @param int $type
     * @param array $params
     * @return mixed
     */
    public function watermark(int $type, array $params = []);

    /**
     * @param array|int $radius
     * @return mixed
     */
    public function round($radius);

    /**
     * @param string $path
     * @return mixed
     */
    public function path(string $path);

    /**
     * @return array
     */
    public function info(): array;
}