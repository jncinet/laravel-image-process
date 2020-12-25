<?php

namespace Jncinet\ImageProcess\Gateways;

use Jncinet\ImageProcess\Contracts\GatewayInterface;

abstract class Gateway implements GatewayInterface
{
    protected $url = '';
    protected $path = '';

    /**
     * 设置图片路径
     *
     * @param $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;
        $this->url = Support::imageUrl($path);

        return $this;
    }
}