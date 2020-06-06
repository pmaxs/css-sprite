<?php
namespace Pmaxs\CssSprite;

/**
 * Class Sprite
 */
class Sprite
{
    /**
     *
     */
    protected $files = [];

    /**
     *
     */
    protected $sprite;

    /**
     *
     */
    protected $style;

    /**
     *
     */
    protected $errors = [];

    /**
     *
     */
    protected $options = [
        'exception_on_error' => 0,
        'url' => '%url%',
        'root_class' => 'sprite',
        'class_prefix' => 'sprite',
        'algorithm' => 'area', // area, point
    ];

    /**
     * Constructor
     * @param $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Return sprite
     * @return mixed
     */
    public function getSprite()
    {
        return $this->sprite;
    }

    /**
     * Return style
     * @return mixed
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Return errors
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return errors
     * @return mixed
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Add error
     * @param $error
     * @param $index
     * @return mixed
     * @throws \Exception
     */
    protected function addError($error, $index = null)
    {
        if ($index) $error = $index . '(' . $this->files[$index]['path'].') - '.$error;

        if (!empty($this->options['exception_on_error']) || empty($index)) throw new \Exception($error);

        if ($index) $this->files[$index]['errors'][] = $error;
        $this->errors[] = $error;
    }

    /**
     * Return options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options
     * @param array $options
     */
    public function setOptions(array $options = array())
    {
        $this->options = \array_replace($this->options, $options);
    }

    /**
     * Return files
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Sets files for sprite
     * @param $files
     */
    public function addFiles($files)
    {
        if (!\is_array($files)) $files = [$files];

        foreach ($files as $index => $path) {
            if (\is_dir($path)) {
                $this->addFiles($this->getFilesFromDirectory($path));
                continue;
            }

            if (\is_numeric($index)) {
                $index = \pathinfo($path, \PATHINFO_FILENAME);
            }

            $index = preg_replace('~\\s+~', '_', $index);

            if (!empty($this->files[$index])) {
                $index1 = $index;
                $i = 1;
                do $index .= $index1 . '_' . $i++; while (!empty($this->files[$index]));
            }

            $this->files[$index] = [
                'path' => $path,
                'errors' => [],
                'w' => 0,
                'h' => 0,
            ];

            if (!\is_file($path)) {
                $this->addError('File not exist', $index);
                continue;
            }
        }
    }

    /**
     * Clears file list
     * @param $files
     */
    public function clearFiles()
    {
        $this->files = [];
    }

    /**
     * Get files from directory
     * @param $directory
     * @return mixed
     */
    protected function getFilesFromDirectory($directory)
    {
        if (!\is_dir($directory)) {
            $this->addError('Directory "'.$directory.'" not exist');
            return [];
        }

        $files = [];

        $iterator = new \DirectoryIterator($directory);

        foreach ($iterator as $info) {
            if ($info->isDot()) continue;
            $files[] = $info->getPathname();
        }

        return $files;
    }

    /**
     * Creates sprite and styles
     * @param $files
     * @throws \Exception
     */
    public function process($files = null)
    {
        if (isset($files)) $this->addFiles($files);
        if (empty($this->files)) throw new \Exception('No files');

        $widths = [];
        $widthss = 0;
        $heights = [];
        $heightss = 0;
        $squares = [];
        $squaress = 0;

        foreach ($this->files as $index => $file) {
            try {
                if (!empty($file['errors'])) continue;

                $imagick1 = new \Imagick();
                $imagick1->readImage($file['path']);
                if (!$imagick1->valid()) {
                    $this->addError('Imagick is not valid', $index);
                    continue;
                }

                $width = $imagick1->getImageWidth();
                $height = $imagick1->getImageHeight();
                $square = $width * $height;

                $this->files[$index]['w'] = $width;
                $this->files[$index]['h'] = $height;

                $widths[$index] = $width;
                $widthss += $width;
                $heights[$index] = $height;
                $heightss += $height;
                $squares[$index] = $square;
                $squaress += $square;

                $imagick1->destroy();
                unset($imagick1);
            } catch (\Exception $e) {
                $this->addError($e->getMessage(), $index);
                continue;
            }
        }

        if (empty($widthss) || empty($heightss)) {
            $this->addError('No valid files');
        }

        $canvasWidth = \ceil(\sqrt($squaress) * $widthss / $heightss);
        $canvasHeight = \ceil(\sqrt($squaress) * $heightss / $widthss);

        $canvasClass = __NAMESPACE__ . '\\Canvas' . ucfirst($this->options['algorithm']);
        if (!\class_exists($canvasClass)) throw new \Exception('Unknown algorithm "' . $this->options['algorithm'] . '"');

        $canvas = new $canvasClass($canvasWidth, $canvasHeight);

        \arsort($squares);

        foreach ($squares as $index => $square) {
            $file = $this->files[$index];

            $area = $canvas->makeArea($file['w'], $file['h']);

            $this->files[$index]['x'] = $area['x'];
            $this->files[$index]['y'] = $area['y'];
        }

        $canvas->calculateSize();

        $imagick = new \Imagick();
        $imagick->newImage($canvas->w, $canvas->h, 'none');
        $imagick->setImagecolorspace(\Imagick::COLORSPACE_RGB);

        foreach ($this->files as $index => $file) {
            try {
                if (!empty($file['errors'])) continue;

                $imagick1 = new \Imagick();
                $imagick1->readImage($file['path']);
                if (!$imagick1->valid()) {
                    $this->addError('Imagick is not valid', $index);
                    continue;
                }

                $imagick->compositeImage($imagick1, $imagick1->getImageCompose(), $file['x'], $file['y']);

                $imagick1->destroy();
                unset($imagick1);
            } catch (\Exception $e) {
                $this->addError($e->getMessage(), $index);
                continue;
            }
        }

        $imagick->setImageFormat('png');
        $imagick->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
        $imagick->setImageCompressionQuality(0);
        $imagick->stripImage();

        $this->sprite = $imagick->getImageBlob();

        unset($canvas);

        $imagick->destroy();
        unset($canvas, $imagick);

        // style
        $this->style = ".{$this->options['root_class']} { display: inline-block; background: url({$this->options['url']}) no-repeat; overflow: hidden; }\n";
        foreach ($this->files as $index => $file) {
            $this->style.= ".{$this->options['root_class']}.{$this->options['class_prefix']}-".$index." {width: ".$file['w']."px; height: ".$file['h']."px; background-position: ".(0 - $file['x'])."px ".(0 - $file['y'])."px;}\n";
        }
    }
}
