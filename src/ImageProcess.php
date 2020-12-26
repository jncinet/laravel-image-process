<?php

namespace Jncinet\ImageProcess;

use Illuminate\Support\Str;
use Jncinet\ImageProcess\Contracts\GatewayInterface;
use Jncinet\ImageProcess\Exceptions\InvalidGatewayException;

/**
 * Class ImageProcess
 * @method $this path(string $path)
 * @method string url()
 * @method $this round(array | int $radius)
 * @method $this resize($mode = 0, array $params = [])
 * @method $this watermark($type = 'image', $params = [])
 * @method array info()
 * @package Jncinet\ImageProcess
 */
class ImageProcess
{
    /**
     * @param $method
     * @param $params
     * @return mixed
     * @throws InvalidGatewayException
     */
    public function __call($method, $params)
    {
        $gateway = __NAMESPACE__ . '\\Gateways\\' . Str::studly(config('filesystems.default')) . 'Gateway';

        if (class_exists($gateway)) {
            $app = new $gateway();

            if ($app instanceof GatewayInterface) {
                return $app->$method(...$params);
            }

            throw new InvalidGatewayException("Gateway [{$gateway}] Must Be An Instance Of GatewayInterface");
        }

        throw new InvalidGatewayException("Gateway [{$gateway}] not exists");
    }
}