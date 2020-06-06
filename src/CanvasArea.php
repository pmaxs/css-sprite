<?php
namespace Pmaxs\CssSprite;

/**
 * Class CanvasArea
 */
class CanvasArea implements CanvasInterface
{
    /**
     * Canvas width
     * @var int
     */
    protected $w = 0;

    /**
     * Canvas height
     * @var int
     */
    protected $h = 0;

    /**
     * Canvas areas
     * @var array
     */
    protected $a = [];

    /**
     * CanvasArea constructor.
     * @param int $w
     * @param int $h
     */
    public function __construct($w = 0, $h = 0)
    {
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

    /**
     * @inheritdoc
     */
    public function makeArea($w, $h, $s = false)
    {
        $a = false;

        if (empty($this->a)) {
            $this->w = max($this->w, $w);
            $this->h = max($this->h, $h);
            return $this->addArea(new Area(0, 0, $w, $h));
        } else {
            foreach (array_reverse($this->a) as $aa) {
                if (($a = $this->addArea(new Area($aa->x2 + 1, $aa->y, $w, $h)))) {
                    return $a;
                }
                if (($a = $this->addArea(new Area($aa->x, $aa->y2 + 1, $w, $h)))) {
                    return $a;
                }
            }
        }

        if (!empty($a)) {
            return $a;
        }

        // extend canvas
        $this->w += 4;
        $this->h += 4;

        $a = $this->makeArea($w, $h, 1);

        if (!$s) {
            $this->calculateSize();
        }

        return $a;
    }

    /**
     * Calculates and sets current width and height
     */
    public function calculateSize()
    {
        $cw = $ch = 0;

        foreach ($this->a as $aa) {
            $cw = max($cw, $aa->x2 + 1);
            $ch = max($ch, $aa->y2 + 1);
        }

        $this->w = $cw;
        $this->h = $ch;
    }

    /**
     * Checks if area fit on canvas
     * @param Area $a
     * @return bool
     */
    protected function isFit($a)
    {
        return $a->x2 < $this->w && $a->y2 < $this->h;
    }

    /**
     * Checks if area on canvas is empty
     * @param Area $a
     * @return bool
     */
    protected function isEmpty($a)
    {
        foreach ($this->a as $aa) {
            if ($aa->containArea($a)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds area on canvas
     * @param Area $a
     * @return Area|bool
     */
    protected function addArea($a)
    {
        if ($this->isFit($a) && $this->isEmpty($a)) {
            $this->a[] = $a;
            return $a;
        }

        return false;
    }
}