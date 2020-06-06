<?php
namespace Pmaxs\CssSprite;

/**
 * Interface CanvasInterface
 */
interface CanvasInterface
{
    /**
     * Finds free place on canvas and places area there
     * @param int $w
     * @param int $h
     * @param bool $s
     * @return Area
     */
    public function makeArea($w, $h, $s = false);
}