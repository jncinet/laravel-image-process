<?php

namespace Jncinet\ImageProcess\Gateways;

use Jncinet\ImageProcess\Exceptions\InvalidArgumentException;

/**
 * Class OssGateway
 * @package Jncinet\ImageProcess\Gateways
 */
class OssGateway extends Gateway
{
    protected $params = [];

    /**
     * 返回图片地址
     *
     * @return string
     */
    public function url(): string
    {
        $fop = Support::formatOssParams($this->params);
        return $this->url . '?x-oss-process=' . $fop;
    }

    /**
     * 图片圆角
     *
     * @param int|array $radius
     * @return $this
     */
    public function round($radius)
    {
        if (empty($radius) || (is_array($radius) && count($radius) != 2)) {
            return $this;
        }

        if (is_array($radius)) {
            $this->params['circle'] = ['r' => min($radius['radiusx'], $radius['radiusy'])];
        } else {
            $this->params['circle'] = ['r' => $radius];
        }

        return $this;
    }

    /**
     * 图片缩放
     *
     * @param int $mode
     * @param array $params
     * @return $this
     */
    public function resize($mode = 0, array $params = [])
    {
        $arrVar = ['lfit', 'mfit', 'pad', 'fixed', 'fill', 'undefined'];
        $params['m'] = $arrVar[$mode];
        $this->params['resize'] = Support::getParams(
            $params,
            ['m', 'w', 'h', 'l', 's', 'limit', 'color']
        );

        return $this;
    }

    /**
     * 水印
     *
     * @param string $type
     * @param array $params
     * @return $this
     * @throws InvalidArgumentException
     */
    public function watermark($type = 'image', $params = [])
    {
        switch ($type) {
            case 'image': // 图片水印
                $this->params['watermark'] = $this->imageWatermark($params);
                break;
            case 'text': // 文字水印
                $this->params['watermark'] = $this->textWatermark($params);
                break;
            case 'text_image': // 混合水印
                foreach ($params as $param) {
                    if (array_key_exists('image', $param)) {
                        $this->params['watermark'][] = $this->imageWatermark($param);
                    } elseif (array_key_exists('text', $param)) {
                        $this->params['watermark'][] = $this->textWatermark($param);
                    }
                };
                break;
            case 'text_tile': // 文字平铺水印
                $params['tile'] = 1;
                $this->params['watermark'] = $this->textWatermark($params);
                break;
        }

        return $this;
    }

    /**
     * 图片水印
     *
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     */
    protected function imageWatermark(array $params = [])
    {
        $newParams = [];
        // 水印图片
        if (array_key_exists('image', $params)) {
            $newParams['image'] = Support::urlSafe_base64_encode($params['image']);
        } else {
            throw new InvalidArgumentException('Watermark image not set');
        }
        // 透明度
        if (array_key_exists('dissolve', $params)) {
            $newParams['t'] = $params['dissolve'];
        }
        // 水印位置
        if (array_key_exists('gravity', $params)) {
            $arrVar = [
                'NorthWest' => 'nw', // 左上
                'North' => 'north', // 中上
                'NorthEast' => 'ne', // 右上
                'West' => 'west', // 左中
                'Center' => 'center', // 中部
                'East' => 'east', // 右中
                'SouthWest' => 'sw', // 左下
                'South' => 'south', // 中下
                'SouthEast' => 'se' // 右下
            ];
            $newParams['g'] = $arrVar[$params['gravity']];
        }
        // 横轴边距
        if (array_key_exists('dx', $params)) {
            $newParams['x'] = $params['dx'];
        }
        // 纵轴边距
        if (array_key_exists('dy', $params)) {
            $newParams['y'] = $params['dy'];
        }
        // 指定水印的中线垂直偏移。当水印位置在左中、中部、右中时，可以指定水印位置根据中线往上或者往下偏移。
        if (array_key_exists('voffset', $params)) {
            $newParams['voffset'] = $params['voffset'];
        }
        return $newParams;
    }

    /**
     * 文字水印
     *
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     */
    protected function textWatermark(array $params = [])
    {
        $newParams = [];
        // 水印文字
        if (array_key_exists('text', $params)) {
            $newParams['text'] = Support::urlSafe_base64_encode($params['text']);
        } else {
            throw new InvalidArgumentException('Watermark text not set');
        }
        // 水印字体
        if (array_key_exists('font', $params)) {
            $newParams['type'] = Support::urlSafe_base64_encode($params['font']);
        }
        // 水印文字大小
        if (array_key_exists('fontsize', $params)) {
            $newParams['size'] = $params['fontsize'];
        }
        // 水印文字颜色
        if (array_key_exists('fill', $params)) {
            $newParams['color'] = $params['fill'];
        }
        // 透明度
        if (array_key_exists('dissolve', $params)) {
            $newParams['t'] = $params['dissolve'];
        }
        // 水印位置
        if (array_key_exists('gravity', $params)) {
            $arrVar = [
                'NorthWest' => 'nw', // 左上
                'North' => 'north', // 中上
                'NorthEast' => 'ne', // 右上
                'West' => 'west', // 左中
                'Center' => 'center', // 中部
                'East' => 'east', // 右中
                'SouthWest' => 'sw', // 左下
                'South' => 'south', // 中下
                'SouthEast' => 'se' // 右下
            ];
            $newParams['g'] = $arrVar[$params['gravity']];
        }
        // 横轴边距
        if (array_key_exists('dx', $params)) {
            $newParams['x'] = $params['dx'];
        }
        // 纵轴边距
        if (array_key_exists('dy', $params)) {
            $newParams['y'] = $params['dy'];
        }
        // 指定文字顺时针旋转角度。
        if (array_key_exists('rotate', $params)) {
            $newParams['rotate'] = $params['rotate'];
        }
        // 指定文字水印的阴影透明度。
        if (array_key_exists('shadow', $params)) {
            $newParams['shadow'] = $params['shadow'];
        }
        // 指定水印的中线垂直偏移。当水印位置在左中、中部、右中时，可以指定水印位置根据中线往上或者往下偏移。
        if (array_key_exists('voffset', $params)) {
            $newParams['voffset'] = $params['voffset'];
        }
        if (array_key_exists('tile', $params)) {
            $newParams['fill'] = $params['tile'];
        }
        return $newParams;
    }

    /**
     * 图片信息
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function info(): array
    {
        $result = Support::requestApi($this->url . '?x-oss-process=image/info');

        return is_array($result)
            ? [
                'size' => intval($result['FileSize']['value']),
                'format' => $result['Format']['value'],
                'width' => intval($result['ImageWidth']['value']),
                'height' => intval($result['ImageHeight']['value']),
            ] : [
                'size' => 0,
                'format' => Support::getFileSuffix($this->url),
                'width' => 0,
                'height' => 0,
            ];
    }
}