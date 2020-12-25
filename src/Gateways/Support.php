<?php

namespace Jncinet\ImageProcess\Gateways;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

/**
 * Class Support
 * @package Jncinet\ImageProcess\Gateways
 */
class Support
{
    /**
     * @param $uri
     * @param array $params
     * @param string $method
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function requestApi($uri, array $params = [], $method = 'GET')
    {
        $client = new Client(['timeout' => 2.0, 'verify' => false]);

        $response = $client->request($method, $uri, $params);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function imageUrl($path)
    {
        $arrPath = parse_url($path);

        if (empty($arrPath['host']) && Storage::exists($path)) {
            return Storage::url($path);
        }

        $url = empty($arrPath['scheme']) ? 'http://' : $arrPath['scheme'] . '://';
        $url .= $arrPath['host'] . $arrPath['path'];
        return $url;
    }

    /**
     * 格式化七牛参数
     *
     * @param array $params
     * @param array $param_modules
     * @return string
     */
    public static function formatQiniuParams(array $params = [], array $param_modules = [])
    {
        $str = '';
        foreach ($params as $key => $value) {
            $str .= array_key_first($params) == $key ? '' : '|';
            $str .= $key;
            if (isset($params_module[$key])) {
                $str .= '/' . $param_modules[$key];
            }
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $ik => $item) {
                        $prefix = array_key_first($v) == $ik ? '/' : '';
                        $suffix = array_key_last($v) == $ik ? '' : '/';
                        $str .= $prefix . $ik . '/' . $item . $suffix;
                    }
                } else {
                    $prefix = array_key_first($value) == $k ? '/' : '';
                    $suffix = array_key_last($value) == $k ? '' : '/';
                    $str .= $prefix . $k . '/' . $v . $suffix;
                }
            }
        }
        return $str;
    }

    /**
     * 去除多余参数
     *
     * @param array $params
     * @param array $key_list
     * @return array
     */
    public static function getParams($params = [], $key_list = [])
    {
        foreach ($params as $key => $param) {
            if (!array_key_exists($key, $key_list)) {
                unset($params[$key]);
            }
        }
        return $params;
    }

    /**
     * 格式化OSS参数
     *
     * @param array $params
     * @return string
     */
    public static function formatOssParams(array $params = [])
    {
        $str = '';
        foreach ($params as $key => $value) {
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    // 添加处理名称
                    $str .= '/' . $key . ',';
                    foreach ($v as $ik => $item) {
                        // 非最后参数前添加分隔符
                        $suffix = array_key_last($v) == $ik ? '' : ',';
                        $str .= $ik . '_' . $item . $suffix;
                    }
                } else {
                    // 首个参数前添加处理名称
                    $str .= array_key_first($value) == $k ? '/' . $key . ',' : '';
                    // 非最后参数前添加分隔符
                    $suffix = array_key_last($value) == $k ? '' : ',';
                    $str .= $k . '_' . $v . $suffix;
                }
            }
        }
        return $str;
    }

    /**
     * 格式化本地图片参数
     *
     * @param array $params
     * @return string
     */
    public static function formatLocalParams(array $params = [])
    {
        return '';
    }

    /**
     * @param $str
     * @return bool|string
     */
    public static function urlSafe_base64_decode($str)
    {
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }

    /**
     * @param $data
     * @return mixed|string
     */
    public static function urlSafe_base64_encode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    /**
     * 获取图片后缀
     *
     * @param string $path
     * @return bool|string
     */
    public static function getFileSuffix($path)
    {
        return substr(strrchr($path, '.'), 1);
    }

    /**
     * 将 utf-8 字符串转为 Unicode 编码格式
     *
     * @param $string
     * @return string
     */
    public static function toUnicode($string)
    {
        $str = mb_convert_encoding($string, 'UCS-2', 'UTF-8');
        $arrStr = str_split($str, 2);
        $uniStr = '';
        foreach ($arrStr as $n) {
            $dec = hexdec(bin2hex($n));
            $uniStr .= '&#' . $dec . ';';
        }
        return $uniStr;
    }

    /**
     * @param $hex
     * @return array
     */
    public static function HexToRGB($hex)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return array($r, $g, $b);
    }

    /**
     * @param $rgb
     * @return string
     */
    public static function RGBToHex($rgb)
    {
        $hex = '#';
        $hex .= str_pad(dechex($rgb[0]), 2, '0', STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, '0', STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, '0', STR_PAD_LEFT);

        return $hex;
    }

    /**
     * 以参数生成新的文件名
     *
     * @param array $params
     * @return string
     */
    public static function md5Filename(array $params = [])
    {
        return md5(json_encode($params));
    }
}