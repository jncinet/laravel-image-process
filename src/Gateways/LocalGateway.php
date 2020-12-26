<?php

namespace Jncinet\ImageProcess\Gateways;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Jncinet\ImageProcess\Exceptions\InvalidArgumentException;

/**
 * Class LocalGateway
 * @package Jncinet\ImageProcess\Gateways
 */
class LocalGateway extends Gateway
{
    protected $params = [];

    /**
     * 图片本地路径
     *
     * @return string
     */
    public function url(): string
    {
        // 源文件完整路径 eg. /www/……/path/oldFilename.jpg
        $filePath = config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . $this->path;
        // 源文章后缀名 eg. jpg
        $fileSuffix = Support::getFileSuffix($this->path);
        // 新文件路径及名称 eg. path/oldFilename_newFilename.jpg
        $newFilePath = str_replace(
            '.' . $fileSuffix,
            Support::md5Filename($this->params) . '.' . $fileSuffix,
            $this->path
        );
        // 如果切圆角则后缀改为png
        if (array_key_exists('round', $this->params) && $fileSuffix != 'png') {
            $newFilePath = Str::replaceLast('.' . $fileSuffix, '.png', $newFilePath);
        }

        // 如果新文件名不存在则生成文件
        if (!Storage::exists($newFilePath) && Storage::exists($this->path)) {
            // 源图
            $img = Image::make($filePath);
            // 重置图片大小
            if (array_key_exists('resize', $this->params)) {
                $img = $img->fit($this->params['resize']['w'], $this->params['resize']['h'] ?: null);
            }
            // 圆角
            if (array_key_exists('round', $this->params)) {
                $canvas = Image::canvas($img->width(), $img->height());
                $r = $this->params['round'];
                for ($x = 0; $x < $img->width(); $x++) {
                    for ($y = 0; $y < $img->height(); $y++) {
                        $c = $img->pickColor($x, $y, 'array');
                        if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                            $canvas->pixel($c, $x, $y);
                        }
                    }
                }
                $img = $canvas;
            }
            // 水印
            if (array_key_exists('watermark', $this->params)) {
                // 判断混合水印还是单个水印
                if (count($this->params['watermark']) == count($this->params['watermark'], 1)) {
                    if (array_key_exists('image', $this->params['watermark'])) {
                        // 图片水印
                        $img = $img->insert(
                            $this->params['watermark']['image'],
                            $this->params['watermark']['position'],
                            $this->params['watermark']['x'],
                            $this->params['watermark']['y']
                        );
                    } elseif (array_key_exists('text', $this->params['watermark'])) {
                        // 文字水印
                        $img = $img->text(
                            $this->params['watermark']['text'],
                            $this->params['watermark']['x'],
                            $this->params['watermark']['y'],
                            function ($font) {
                                if (array_key_exists('file', $this->params['watermark'])) {
                                    $font->file($this->params['watermark']['file']);
                                }
                                if (array_key_exists('size', $this->params['watermark'])) {
                                    $font->size($this->params['watermark']['size']);
                                }
                                if (array_key_exists('color', $this->params['watermark'])) {
                                    $font->color($this->params['watermark']['color']);
                                }
                                if (array_key_exists('align', $this->params['watermark'])) {
                                    $font->align($this->params['watermark']['align']);
                                }
                                if (array_key_exists('valign', $this->params['watermark'])) {
                                    $font->valign($this->params['watermark']['valign']);
                                }
                                if (array_key_exists('angle', $this->params['watermark'])) {
                                    $font->angle($this->params['watermark']['angle']);
                                }
                            }
                        );
                    }
                } else {
                    foreach ($this->params['watermark'] as $param) {
                        if (array_key_exists('image', $param)) {
                            // 图片水印
                            $img = $img->insert(
                                $param['image'],
                                $param['position'],
                                $param['x'],
                                $param['y']
                            );
                        } elseif (array_key_exists('text', $param)) {
                            // 文字水印
                            $img = $img->text(
                                $param['text'],
                                $param['x'],
                                $param['y'],
                                function ($font) use ($param) {
                                    if (array_key_exists('file', $param)) {
                                        $font->file($param['file']);
                                    }
                                    if (array_key_exists('size', $param)) {
                                        $font->size($param['size']);
                                    }
                                    if (array_key_exists('color', $param)) {
                                        $font->color($param['color']);
                                    }
                                    if (array_key_exists('align', $param)) {
                                        $font->align($param['align']);
                                    }
                                    if (array_key_exists('valign', $param)) {
                                        $font->valign($param['valign']);
                                    }
                                    if (array_key_exists('angle', $param)) {
                                        $font->angle($param['angle']);
                                    }
                                }
                            );
                        }
                    }
                }
            }

            $img->save($newFilePath);
        }

        return Storage::url($newFilePath);
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
            $this->params['round'] = min($radius['radiusx'], $radius['radiusy']);
        } else {
            $this->params['round'] = $radius;
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
        $this->params['resize'] = Support::getParams($params, ['w', 'h']);

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
        // 水平位置、垂直位置
        if (array_key_exists('gravity', $params)) {
            $newParams['position'] = [
                'NorthWest' => 'top-left', 'North' => 'top', 'NorthEast' => 'top-right',
                'West' => 'left', 'Center' => 'center', 'East' => 'right',
                'SouthWest' => 'bottom-left', 'South' => 'bottom', 'SouthEast' => 'bottom-right',
            ][$params['gravity']];
        } else {
            $newParams['position'] = 'bottom-right';
        }
        // 横轴边距
        if (array_key_exists('dx', $params)) {
            $newParams['x'] = $params['dx'];
        } else {
            $newParams['x'] = 0;
        }
        // 纵轴边距
        if (array_key_exists('dy', $params)) {
            $newParams['y'] = $params['dy'];
        } else {
            $newParams['y'] = 0;
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
            $newParams['text'] = Support::toUnicode($params['text']);
        } else {
            throw new InvalidArgumentException('Watermark text not set');
        }
        // 水印字体
        if (array_key_exists('font', $params)) {
            $newParams['file'] = $params['font'];
        }
        // 文字大小
        if (array_key_exists('fontsize', $params)) {
            $newParams['size'] = $params['fontsize'];
        }
        // 文字颜色
        if (array_key_exists('fill', $params)) {
            $newParams['color'] = $params['fill'];
        } else {
            $newParams['color'] = '#000000';
        }
        // 水平位置、垂直位置
        if (array_key_exists('gravity', $params)) {
            $newParams['align'] = [
                'NorthWest' => 'left', 'North' => 'left', 'NorthEast' => 'left',
                'West' => 'center', 'Center' => 'center', 'East' => 'center',
                'SouthWest' => 'right', 'South' => 'right', 'SouthEast' => 'right',
            ][$params['gravity']];
            $newParams['valign'] = [
                'NorthWest' => 'top', 'North' => 'top', 'NorthEast' => 'top',
                'West' => 'middle', 'Center' => 'middle', 'East' => 'middle',
                'SouthWest' => 'bottom', 'South' => 'bottom', 'SouthEast' => 'bottom',
            ][$params['gravity']];
        }
        // 透明度
        if (array_key_exists('dissolve', $params)) {
            $newParams['color'] = array_merge(Support::HexToRGB($newParams['color']), [$params['dissolve'] / 100]);
        }
        // 横轴边距
        if (array_key_exists('dx', $params)) {
            $newParams['x'] = $params['dx'];
        } else {
            $newParams['x'] = 0;
        }
        // 纵轴边距
        if (array_key_exists('dy', $params)) {
            $newParams['y'] = $params['dy'];
        } else {
            $newParams['y'] = 0;
        }
        // 指定文字顺时针旋转角度。
        if (array_key_exists('rotate', $params)) {
            $newParams['angle'] = $params['rotate'];
        }
        return $newParams;
    }

    /**
     * 图片信息
     *
     * @return array
     */
    public function info(): array
    {
        $img = Image::make(config('filesystems.disks.public.root') . '/' . $this->path);

        return [
            'size' => $img->filesize() ?: 0,
            'format' => Support::getFileSuffix($this->path),
            'width' => $img->width() ?: 0,
            'height' => $img->height() ?: 0,
        ];
    }
}