<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\RangeException;
use function Chevereto\Legacy\G\add_ending_slash;
use function Chevereto\Legacy\G\get_basename_without_extension;
use function Chevereto\Legacy\G\get_filename;
use function Chevereto\Legacy\G\get_image_fileinfo;
use function Chevereto\Legacy\G\is_writable;
use function Chevereto\Legacy\missing_values_to_exception;
use Exception;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;

class ImageResize
{
    private string  $file_extension;

    // filename => name.ext
    // file => /full/path/to/name.ext
    // name => name

    private string $resized_file;

    private array $resized;

    private Image $image;

    private string $source;

    private string $destination = '';

    private string $filename;

    private array $options = [];

    private int $width = 0;

    private int $height = 0;

    private array $source_image_fileinfo;

    public function __construct(string $source)
    {
        clearstatcache(true, $source);
        $this->source = $source;
        if (!file_exists($this->source)) {
            throw new Exception("Source file doesn't exists", 600);
        }
        $this->image = ImageManagerStatic::make($source);
    }

    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    public function setFilename(string $name): void
    {
        $this->filename = $name;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function resized(): array
    {
        return $this->resized;
    }

    public function exec(): void
    {
        $this->validateInput(); // Exception 1xx
        $source_filename = get_basename_without_extension($this->source);
        $this->file_extension = $this->source_image_fileinfo['extension'];
        if (!isset($this->filename)) {
            $this->filename = $source_filename;
        }
        $this->destination = add_ending_slash($this->destination);
        $this->resized_file = $this->destination . $this->filename . '.' . $this->file_extension;
        $this->resize();
        $this->resized = [
            'file' => $this->resized_file,
            'filename' => get_filename($this->resized_file),
            'name' => get_basename_without_extension($this->resized_file),
            'fileinfo' => get_image_fileinfo($this->resized_file),
        ];
    }

    protected function validateInput(): void
    {
        $check_missing = ['source'];
        missing_values_to_exception($this, Exception::class, $check_missing, 600);
        if (!$this->width && !$this->height) {
            throw new Exception('Missing width and/or height', 602);
        }
        if ($this->destination === '') {
            $this->destination = add_ending_slash(dirname($this->source));
        }
        $this->source_image_fileinfo = get_image_fileinfo($this->source);
        if (!$this->source_image_fileinfo) {
            throw new Exception("Can't get source image info", 611);
        }
        if (!is_dir($this->destination)) {
            $old_umask = umask(0);
            $make_destination = mkdir($this->destination, 0755, true);
            umask($old_umask);
            if (!$make_destination) {
                throw new Exception('Destination ' . $this->destination . ' is not a dir', 620);
            }
        }
        if (!is_writable($this->destination)) {
            throw new Exception("Can't write target destination dir " . $this->destination, 622);
        }
    }

    protected function resize(): void
    {
        $this->options['over_resize'] ??= false;
        $this->options['fitted'] ??= false;
        if ($this->width > 0 && $this->height === 0) {
            $this->height = (int) round($this->width / $this->source_image_fileinfo['ratio']);
        }
        if ($this->height > 0 && $this->width === 0) {
            $this->width = (int) round($this->height * $this->source_image_fileinfo['ratio']);
        }
        $imageSX = $this->source_image_fileinfo['width'];
        $imageSY = $this->source_image_fileinfo['height'];
        if (!$this->options['over_resize']) {
            if ($this->width > $imageSX) {
                throw new RangeException(
                    message('Target width is greater than the original image width'),
                    100
                );
            }
            if ($this->height > $imageSY) {
                throw new RangeException(
                    message('Target height is greater than the original image width'),
                    100
                );
            }
        }
        if ($this->options['fitted']) {
            $this->image->fit($this->width, $this->height);
        } else {
            $this->image->resize($this->width, $this->height);
        }
        $this->image->save($this->resized_file);
        if (!file_exists($this->resized_file)) {
            throw new Exception("Can't create final output image", 630);
        }
    }
}
