## Laravel ImageProcess

## 安装
```shell
$ composer require jncinet/laravel-image-process
```

## 方法

* @method path(string $path) 输入图片地址
* @method string url() 返回图片地址
* @method round(array | int $radius) 圆角
* @method resize($mode = 0, array $params = []) 设置置尺寸
* @method watermark($type = 'image', $params = []) 水印
* @method array info() 获取图片信息

## 示例
参数以七牛配置以基础自适应为本地、阿里云
[七牛图片缩放说明文档](https://developer.qiniu.com/dora/api/1279/basic-processing-images-imageview2)  
[七牛图片水印说明文档](https://developer.qiniu.com/dora/api/1316/image-watermarking-processing-watermark)
```php
// 获取图片信息
app('image-process')->path('源图地址')->info();

/* 修改图片大小、添加圆角、水印 */
// 圆角参数：
$round_params = ['radiusx' => 100, 'radiusy' => 100]
// 或
$round_params = 100;

// 重置大小，参考上面的 [七牛图片缩放说明文档] 配置
$aliyun_mode = [0=>'lfit', 1=>'mfit', 2=>'pad', 3=>'fixed', 4=>'fill', 5=>'undefined'];
$mode = '对应七牛缩放规则的值,使用阿里云时根据上方数组索引，本地处理会忽略，';
$resize_params = ['w' => 100, 'h' => 200, 'l' => '最长', 's' => '最短', 'limit'=>'阿里专用', 'color'=>'阿里专用'];

// 水印
$type = 'text=文字水印 | image=图片水印 | text_image=混合水印 | text_tile=文字平铺水印，本地驱动时此值无效可使用混合水印处理';
$watermark_params = [
   'image' => '图片地址','dissolve'=>'透明度',
   // 或混合水印
   ['image' => '图片地址','dissolve'=>'透明度',]
   ['text' => '文字','dissolve'=>'透明度',]
   ['text' => '文字','dissolve'=>'透明度',]
];

app('image-process')->path('源图地址')
    ->round($round_params)
    ->resize($mode, $resize_params)
    ->watermark($type, $watermark_params)
    ->url();
```