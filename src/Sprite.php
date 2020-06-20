<?php
namespace Pmaxs\CssSprite;

/**
 * Class Sprite
 */
class Sprite
{
    /** @var array */
    protected $files = [];

    /** @var string */
    protected $sprite;

    /** @var string */
    protected $style;

    /** @var array */
    protected $errors = [];

    /** @var array */
    protected $options = [
        'exception_on_error' => 0,
        'url' => '%url%',
        'root_class' => 'sprite',
        'class_prefix' => 'sprite',
        'quality' => 100,
        'compression' => \Imagick::COMPRESSION_UNDEFINED,
        'format' => 'png',
        'force_width' => 0,
        'force_height' => 0,
        'algorithm' => 'area', // area, point
    ];

    /**
     * Sprite constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Returns sprite
     * @return string
     */
    public function getSprite()
    {
        return $this->sprite;
    }

    /**
     * Returns style
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Returns errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Checks if has errors
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Adds error
     * @param $error
     * @param null $index
     * @throws \Exception
     */
    protected function addError($error, $index = null)
    {
        if ($index) {
            $error = $index . '(' . $this->files[$index]['path'].') - '.$error;
            $this->files[$index]['errors'][] = $error;
        }

        $this->errors[] = $error;

        if (!empty($this->options['exception_on_error']) || empty($index)) {
            throw new \Exception($error);
        }
    }

    /**
     * Returns options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets options
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = array_replace($this->options, $options);
    }

    /**
     * Returns files
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Adds files to sprite
     * @param $files
     * @throws \Exception
     */
    public function addFiles($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $index => $path) {
            if (is_dir($path)) {
                $this->addFiles($this->getFilesFromDirectory($path));
                continue;
            }

            if (is_numeric($index)) {
                $index = pathinfo($path, \PATHINFO_FILENAME);
            }

            $index = preg_replace('~\\s+~', '_', $index);

            if (!empty($this->files[$index])) {
                $index1 = $index;
                $i = 1;
                do {
                    $index .= $index1 . '_' . $i++;
                } while (!empty($this->files[$index]));
            }

            $this->files[$index] = [
                'path' => $path,
                'errors' => [],
                'w' => 0,
                'h' => 0,
            ];

            if (!is_file($path)) {
                $this->addError('File not exist', $index);
                continue;
            }
        }
    }

    /**
     * Removes all files from sprite
     */
    public function clearFiles()
    {
        $this->files = [];
    }

    /**
     * Gets files from directory
     * @param $directory
     * @return array
     * @throws \Exception
     */
    protected function getFilesFromDirectory($directory)
    {
        if (!is_dir($directory)) {
            $this->addError('Directory "'.$directory.'" not exist');
            return [];
        }

        $files = [];

        $iterator = new \DirectoryIterator($directory);

        foreach ($iterator as $info) {
            if ($info->isDot()) {
                continue;
            }

            $files[] = $info->getPathname();
        }

        return $files;
    }

    /**
     * @param \Imagick $image
     * @return \Imagick
     * @throws \ImagickException
     */
    private function configureImage(\Imagick $image) {
        $width = $image->getImageWidth();
        if($this->options['force_width'] > 0) {
            $width = $this->options['force_width'];
        }

        $height = $image->getImageHeight();
        if($this->options['force_height'] > 0) {
            $height = $this->options['force_height'];
        }

        if($this->options['force_height'] && !$this->options['force_width']) {
            $image->scaleImage(0, $this->options['force_height']); // forces aspect ratio
        } else if($this->options['force_width'] && !$this->options['force_height']) {
            $image->scaleImage($this->options['force_width'], 0); // forces aspect ratio
        } else if($this->options['force_height'] && $this->options['force_width']) {
            $image->scaleImage($width, $height);                       // brand new dimensions
        }

        return $image;
    }

    /**
     * Creates sprite and styles
     * @param null $files
     * @throws \ImagickException
     */
    public function process($files = null)
    {
        if (isset($files)) {
            $this->addFiles($files);
        }

        if (empty($this->files)) {
            throw new \Exception('No files');
        }

        $widths = [];
        $widthss = 0;
        $heights = [];
        $heightss = 0;
        $squares = [];
        $squaress = 0;

        foreach ($this->files as $index => $file) {
            try {
                if (!empty($file['errors'])) {
                    continue;
                }

                $imagick1 = new \Imagick();
                $imagick1->readImage($file['path']);
                if (!$imagick1->valid()) {
                    $this->addError('Imagick is not valid', $index);
                    continue;
                }

                $imagick1 = $this->configureImage($imagick1);
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

        $canvasWidth = ceil(sqrt($squaress) * $widthss / $heightss);
        $canvasHeight = ceil(sqrt($squaress) * $heightss / $widthss);

        $canvasClass = __NAMESPACE__ . '\\Canvas' . ucfirst($this->options['algorithm']);
        if (!class_exists($canvasClass)) {
            throw new \Exception('Unknown algorithm "' . $this->options['algorithm'] . '"');
        }

        $canvas = new $canvasClass($canvasWidth, $canvasHeight);

        arsort($squares);

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
                if (!empty($file['errors'])) {
                    continue;
                }

                $imagick1 = new \Imagick();
                $imagick1->readImage($file['path']);
                if (!$imagick1->valid()) {
                    $this->addError('Imagick is not valid', $index);
                    continue;
                }

                $imagick1 = $this->configureImage($imagick1);

                $imagick->compositeImage($imagick1, $imagick1->getImageCompose(), $file['x'], $file['y']);

                $imagick1->destroy();
                unset($imagick1);
            } catch (\Exception $e) {
                $this->addError($e->getMessage(), $index);
                continue;
            }
        }

        $quality = $this->options['quality'];
        if($quality === 100) $quality = 0;

        $imagick->setImageFormat($this->options['format']);
        $imagick->setImageCompression($this->options['compression']);
        $imagick->setImageCompressionQuality($quality);
        $imagick->stripImage();

        $this->sprite = $imagick->getImageBlob();

        unset($canvas);

        $imagick->destroy();
        unset($canvas, $imagick);

        // style
        $this->style = ".{$this->options['root_class']} { display: inline-block; background: url({$this->options['url']}) no-repeat; overflow: hidden; }\n";
        foreach ($this->files as $index => $file) {
            if (!empty($file['errors'])) {
                continue;
            }

            $this->style.=
                ".{$this->options['root_class']}.{$this->options['class_prefix']}-{$index} { "
                . "width: {$file['w']}px; "
                . "height: {$file['h']}px; "
                . "background-position: ".(0 - $file['x'])."px ".(0 - $file['y'])."px; "
                . "}\n";
        }
    }
}
