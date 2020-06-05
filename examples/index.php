<?php
require '../vendor/autoload.php';

function getTime() {
    $mtime = microtime();
    $mtime = explode(' ', $mtime);
    return ((float)$mtime[1] + (float)$mtime[0]);
}

$t1 = getTime();

$CssSprite = new \Pmaxs\CssSprite\Sprite([
    'exception_on_error' => 0,
    'url' => '/stripe.png',
    'root_class' => 'sprite',
    'class_prefix' => 'sprite_item',
    'algorithm' => 'area', // area, point
]);
$CssSprite->addFiles([
    __DIR__ . '/data1',
    __DIR__ . '/data2',
    __DIR__ . '/anonymous.png',
    __FILE__
]);
$CssSprite->process();

$t2 = getTime();

echo "<h2>Stat</h2>";
echo "process time: " . (round($t2 - $t1, 3)) . " sec; memory usage: " . (round(memory_get_peak_usage(true) / 1024 / 1024, 1)) . " Mb";
echo "<h2>Sprite</h2>";
echo "<img src='data:image/png;base64," . base64_encode($CssSprite->getSprite()) . "' />";
echo "<h2>CSS</h2>";
echo nl2br($CssSprite->getStyle());
echo "<h2>Errors</h2>";
echo nl2br(implode("\n", $CssSprite->getErrors()));

