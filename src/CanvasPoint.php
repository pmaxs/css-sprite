<?php
namespace Pmaxs\CssSprite;

/**
 * Class CanvasPoint
 */
class CanvasPoint implements CanvasInterface
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
     * Canvas points
     * @var array
     */
    protected $p = [];

    /**
     * Constructor
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
        if (empty($this->p)) {
            $this->w = max($this->w, $w);
            $this->h = max($this->h, $h);
            return $this->addArea(0, 0, $w, $h);
        } else {
            $x1 = $y1 = 0;
            $x2 = $this->w - $w + 1;
            $y2 = $this->h - $h + 1;

            for ($x = $x1; $x < $x2; $x++) {
                for ($y = $y1; $y < $y2; $y++) {
                    $ax1 = $x;
                    $ax2 = $ax1 + $w;

                    $ay1 = $y;
                    $ay2 = $ay1 + $h;

                    $empty = true;

                    for ($ax = $ax1; $ax < $ax2; $ax++) {
                        for ($ay = $ay1; $ay < $ay2; $ay++) {
                            if (!$this->isEmpty($ax, $ay)) {
                                $empty = false;
                                break 2;
                            }
                        }
                    }

                    if ($empty) {
                        return $this->addArea($ax1, $ay1, $w, $h);
                    }
                }
            }
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

        for ($x = $this->w - 1; $x >= 0; $x--) {
            $l = 0;
            for ($y = $this->h - 1; $y >= 0; $y--) {
                if (!empty($this->p[$x][$y])) {
                    $cw = max($cw, $x + 1);
                    $ch = max($ch, $y + 1);
                    $l = 1;
                } elseif ($l) {
                    break;
                }
            }
        }

        $this->w = $cw;
        $this->h = $ch;
    }

    /**
     * Checks if point on canvas is empty
     * @param $x
     * @param $y
     * @return bool
     */
    protected function isEmpty($x, $y)
    {
        return (empty($this->p[$x]) || empty($this->p[$x][$y]));
    }

    /**
     * Adds area on canvas
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @return Area area
     */
    protected function addArea($x, $y, $w, $h)
    {
        $x2 = $x + $w;
        $y2 = $y + $h;

        for ($px = $x; $px < $x2; $px++) {
            if (!isset($this->p[$px])) {
                $this->p[$px] = [];
            }

            for ($py = $y; $py < $y2; $py++) {
                $this->p[$px][$py] = 1;
            }
        }

        return new Area($x, $y, $w, $h);
    }
}