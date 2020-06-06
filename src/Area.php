<?php
namespace Pmaxs\CssSprite;

/**
 * Class Area
 */
class Area implements \ArrayAccess
{
    /**
     * 1-st point x
     * @var int
     */
    protected $x;

    /**
     * 1-st point y
     * @var int
     */
    protected $y;

    /**
     * 2-nd point x
     * @var int
     */
    protected $x2;

    /**
     * 2-nd point y
     * @var int
     */
    protected $y2;

    /**
     * Width
     * @var int
     */
    protected $w;

    /**
     * Height
     * @var int
     */
    protected $h;

    /**
     * Area constructor.
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     */
    public function __construct($x, $y, $w, $h)
    {
        $this->x = $x;
        $this->y = $y;
        $this->x2 = $x + $w - 1;
        $this->y2 = $y + $h - 1;
        $this->w = $w;
        $this->h = $h;
    }

    public function __isset($name)
    {
        return isset($this->{$name});
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        throw new \Exception('Set method is not allowed');
    }

    public function __unset($name)
    {
        throw new \Exception('Unset method is not allowed');
    }

    public function offsetExists($name)
    {
        return isset($this->{$name});
    }

    public function offsetGet($name)
    {
        return $this->{$name};
    }

    public function offsetSet($name, $value)
    {
        throw new \Exception('Set method is not allowed');
    }

    public function offsetUnset($name)
    {
        throw new \Exception('Unset method is not allowed');
    }

    /**
     * Checks if area contains another area
     * @param Area $a
     * @return bool
     */
    public function containArea($a)
    {
        return
            $this->containPoint($a->x, $a->y)
            || $this->containPoint($a->x2, $a->y)
            || $this->containPoint($a->x, $a->y2)
            || $this->containPoint($a->x2, $a->y2)
            || $a->containPoint($this->x, $this->y)
            || $a->containPoint($this->x2, $this->y)
            || $a->containPoint($this->x, $this->y2)
            || $a->containPoint($this->x2, $this->y2);
    }

    /**
     * Checks if area contains point
     * @param $x
     * @param $y
     * @return bool
     */
    public function containPoint($x, $y)
    {
        return
            $x >= $this->x && $x <= $this->x2
            && $y >= $this->y && $y <= $this->y2;
    }
}