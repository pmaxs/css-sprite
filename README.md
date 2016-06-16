CSS sprite generator.

Installation
------------

    composer require pmaxs/css-sprite

Usage
-----

```php
<?php
require '../vendor/autoload.php';

$CssSprite = new \Pmaxs\CssSprite\Sprite();
$CssSprite->addFiles([
    __DIR__ . '/data1',
    __DIR__ . '/data2',
    __DIR__ . '/anonymous.png',
    __FILE__
]);
$CssSprite->process();

echo "<img src='data:image/png;base64," . base64_encode($CssSprite->getSprite()) . "' />";
echo "<p></p>";
echo $CssSprite->getStyle();