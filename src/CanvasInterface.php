<?php
namespace Pmaxs\CssSprite;

/**
 * Interface CanvasInterface
 */
interface CanvasInterface
{
    /**
     * Finds free place on canvas and places area there
     * @param $w width
     * @param $h height
     * @param bool $s recalculate width and height
     * @return Area
     */
    public function makeArea($w, $h, $s = false);
}