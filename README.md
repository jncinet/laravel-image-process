## Laravel ImageProcess


```shell
$ composer require jncinet/laravel-image-process
```

### 说明

* @method path(string $path) 输入图片地址
* @method string url() 返回图片地址
* @method round(array | int $radius) 圆角
* @method resize($mode = 0, array $params = []) 设置置尺寸
* @method watermark($type = 'image', $params = []) 水印
* @method array info() 获取图片信息

### 使用示例
```php
// 获取图片信息
app('image-process')->path('源图地址')->info();
// 返回固定图片尺寸地址
app('image-process')->path('源图地址')->resize(0, ['w'=>100, 'h'=>200])->url();
```