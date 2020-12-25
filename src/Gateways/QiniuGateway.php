<?php

namespace Jncinet\ImageProcess\Gateways;

use Jncinet\ImageProcess\Exceptions\InvalidArgumentException;

/**
 * Class QiniuGateway
 * @package Jncinet\ImageProcess\Gateways
 */
class QiniuGateway extends Gateway
{
    protected $params = [];
    protected $param_modules = [];

    /**
     * 返回图片地址
     *
     * @return string
     */
    public function url()
    {
        $fop = Support::formatQiniuParams($this->params, $this->param_modules);
        return $this->url . '?' . $fop;
    }

    /**
     * 图片圆角
     *
     * @param string|array $radius
     * @return $this
     */
    public function round($radius)
    {
        if (empty($radius) || (is_array($radius) && count($radius) != 2)) {
            return $this;
        }

        if (is_array($radius)) {
            $this->params['roundPic'] = Support::getParams($radius, ['radiusx', 'radiusy']);
        } else {
            $this->params['roundPic'] = ['radius' => $radius];
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
        $this->param_modules['imageView2'] = $mode;
        switch ($mode) {
            case 0:
            case 4:
            case 5:
                $params['w'] = $params['l'];
                $params['h'] = $params['s'];
                $this->params['imageView2'] = Support::getParams($params, ['w', 'h']);
                break;
            case 1:
            case 2:
            case 3:
                $this->params['imageView2'] = Support::getParams($params, ['w', 'h']);
                break;
        }

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
                $this->param_modules['watermark'] = 1;
                break;
            case 'text': // 文字水印
                $this->params['watermark'] = $this->textWatermark($params);
                $this->param_modules['watermark'] = 2;
                break;
            case 'text_image': // 混合水印
                $this->param_modules['watermark'] = 3;
                foreach ($params as $param) {
                    if (array_key_exists('image', $param)) {
                        $this->params['watermark'][] = $this->imageWatermark($param);
                    } elseif (array_key_exists('text', $param)) {
                        $this->params['watermark'][] = $this->textWatermark($param);
                    }
                };
                break;
            case 'text_tile': // 文字平铺水印
                $this->param_modules['watermark'] = 4;
                $this->params['watermark'] = $this->textTileWatermark($params);
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
        // 水印图片
        if (array_key_exists('image', $params)) {
            $params['image'] = Support::urlSafe_base64_encode($params['image']);
        } else {
            throw new InvalidArgumentException('Watermark image not set');
        }
        return Support::getParams(
            $params,
            ['image', 'dissolve', 'gravity', 'dx', 'dy', 'ws', 'wst']
        );
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
        // 水印文字
        if (array_key_exists('text', $params)) {
            $params['text'] = Support::urlSafe_base64_encode($params['text']);
        } else {
            throw new InvalidArgumentException('Watermark text not set');
        }
        // 水印字体
        if (array_key_exists('font', $params)) {
            $params['font'] = Support::urlSafe_base64_encode($params['font']);
        }
        return Support::getParams(
            $params,
            ['text', 'font', 'fontsize', 'fill', 'dissolve', 'gravity', 'dx', 'dy']
        );
    }

    /**
     * 文字平铺水印
     *
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     */
    protected function textTileWatermark(array $params = [])
    {
        // 水印文字
        if (array_key_exists('text', $params)) {
            $params['text'] = Support::urlSafe_base64_encode($params['text']);
        } else {
            throw new InvalidArgumentException('Watermark text not set');
        }
        // 水印字体
        if (array_key_exists('font', $params)) {
            $params['font'] = Support::urlSafe_base64_encode($params['font']);
        }
        return Support::getParams(
            $params,
            ['text', 'font', 'fontsize', 'fill', 'dissolve', 'rotate', 'uw', 'uh', 'resize']
        );
    }

    /**
     * 图片信息
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function info(): array
    {
        $result = Support::requestApi($this->url . '?imageInfo');

        return is_array($result) ? [
            'size' => intval($result['size']),
            'format' => $result['format'],
            'width' => intval($result['width']),
            'height' => intval($result['height']),
        ] : [
            'size' => 0,
            'format' => Support::getFileSuffix($this->url),
            'width' => 0,
            'height' => 0,
        ];
    }
}