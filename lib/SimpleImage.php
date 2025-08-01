<?php

//
// SimpleImage
//
//  A PHP class that makes working with images as simple as possible.
//
//  Developed and maintained by Cory LaViska <https://github.com/claviska>.
//
//  Copyright A Beautiful Site, LLC.
//
//  Source: https://github.com/claviska/SimpleImage
//
//  Licensed under the MIT license <http://opensource.org/licenses/MIT>
//

// namespace claviska;

// use Exception;
// use GdImage;
// use League\ColorExtractor\Color;
// use League\ColorExtractor\ColorExtractor;
// use League\ColorExtractor\Palette;

/**
 * A PHP class that makes working with images as simple as possible.
 */
class SimpleImage
{
    public const
        ERR_FILE_NOT_FOUND = 1;

    public const
        ERR_FONT_FILE = 2;

    public const
        ERR_FREETYPE_NOT_ENABLED = 3;

    public const
        ERR_GD_NOT_ENABLED = 4;

    public const
        ERR_INVALID_COLOR = 5;

    public const
        ERR_INVALID_DATA_URI = 6;

    public const
        ERR_INVALID_IMAGE = 7;

    public const
        ERR_LIB_NOT_LOADED = 8;

    public const
        ERR_UNSUPPORTED_FORMAT = 9;

    public const
        ERR_WEBP_NOT_ENABLED = 10;

    public const
        ERR_WRITE = 11;

    public const
        ERR_INVALID_FLAG = 12;

    protected array $flags;

    protected $image = null;

    protected string $mimeType;

    protected null|array|false $exif = null;

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Magic methods
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Creates a new SimpleImage object.
     *
     * @param  string  $image An image file or a data URI to load.
     * @param  array  $flags Optional override of default flags.
     *
     * @throws Exception Thrown if the GD library is not found; file|URI or image data is invalid.
     */
    public function __construct(string $image = '', array $flags = [])
    {
        // Check for the required GD extension
        if (extension_loaded('gd')) {
            // Ignore JPEG warnings that cause imagecreatefromjpeg() to fail
            ini_set('gd.jpeg_ignore_warning', '1');
        } else {
            throw new Exception('Required extension GD is not loaded.', self::ERR_GD_NOT_ENABLED);
        }

        // Associative array of flags.
        $this->flags = [
            'sslVerify' => true, // Skip SSL peer validation
        ];

        // Override default flag values.
        foreach ($flags as $flag => $value) {
            $this->setFlag($flag, $value);
        }

        // Load an image through the constructor
        if (preg_match('/^data:(.*?);/', $image)) {
            $this->fromDataUri($image);
        } elseif ($image) {
            $this->fromFile($image);
        }
    }

    /**
     * Destroys the image resource.
     */
    public function __destruct()
    {
        $this->reset();
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Helper functions
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Checks if the SimpleImage object has loaded an image.
     */
    public function hasImage(): bool
    {
        return $this->image instanceof GdImage;
    }

    /**
     * Destroys the image resource.
     */
    public function reset(): static
    {
        if ($this->hasImage()) {
            imagedestroy($this->image);
        }

        return $this;
    }

    /**
     * Set flag value.
     *
     * @param  string  $flag Name of the flag to set.
     * @param  bool  $value State of the flag.
     *
     * @throws Exception Thrown if flag does not exist (no default value).
     */
    public function setFlag(string $flag, bool $value): void
    {
        // Throw if flag does not exist
        if (! in_array($flag, array_keys($this->flags))) {
            throw new Exception('Invalid flag.', self::ERR_INVALID_FLAG);
        }

        // Set flag value by name
        $this->flags[$flag] = $value;
    }

    /**
     * Get flag value.
     *
     * @param  string  $flag Name of the flag to get.
     */
    public function getFlag(string $flag): ?bool
    {
        return in_array($flag, array_keys($this->flags)) ? $this->flags[$flag] : null;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Loaders
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Loads an image from a data URI.
     *
     * @param  string  $uri A data URI.
     * @return SimpleImage
     *
     * @throws Exception Thrown if URI or image data is invalid.
     */
    public function fromDataUri(string $uri): static
    {
        // Basic formatting check
        preg_match('/^data:(.*?);/', $uri, $matches);
        if (! count($matches)) {
            throw new Exception('Invalid data URI.', self::ERR_INVALID_DATA_URI);
        }

        // Determine mime type
        $this->mimeType = $matches[1];
        if (! preg_match('/^image\/(gif|jpeg|png)$/', $this->mimeType)) {
            throw new Exception(
                'Unsupported format: ' . $this->mimeType,
                self::ERR_UNSUPPORTED_FORMAT
            );
        }

        // Get image data
        $uri = base64_decode(strval(preg_replace('/^data:(.*?);base64,/', '', $uri)));
        $this->image = imagecreatefromstring($uri);
        if (! $this->image) {
            throw new Exception('Invalid image data.', self::ERR_INVALID_IMAGE);
        }

        return $this;
    }

    /**
     * Loads an image from a file.
     *
     * @param  string  $file The image file to load.
     * @return SimpleImage
     *
     * @throws Exception Thrown if file or image data is invalid.
     */
    public function fromFile(string $file): static
    {
        // Set fopen options.
        $sslVerify = $this->getFlag('sslVerify'); // Don't perform peer validation when true
        $opts = [
            'ssl' => [
                'verify_peer' => $sslVerify,
                'verify_peer_name' => $sslVerify,
            ],
        ];

        // Check if the file exists and is readable.
        $file = @file_get_contents($file, false, stream_context_create($opts));
        if ($file === false) {
            throw new Exception("File not found: $file", self::ERR_FILE_NOT_FOUND);
        }

        // Create image object from string
        $this->image = imagecreatefromstring($file);

        // Get image info
        $info = @getimagesizefromstring($file);
        if ($info === false) {
            throw new Exception("Invalid image file: $file", self::ERR_INVALID_IMAGE);
        }
        $this->mimeType = $info['mime'];

        if (! $this->image) {
            throw new Exception('Unsupported format: ' . $this->mimeType, self::ERR_UNSUPPORTED_FORMAT);
        }

        switch ($this->mimeType) {
            case 'image/gif':
                // Copy the gif over to a true color image to preserve its transparency. This is a
                // workaround to prevent imagepalettetotruecolor() from borking transparency.
                $width = imagesx($this->image);
                $height = imagesx($this->image);

                $gif = imagecreatetruecolor((int) $width, (int) $height);
                $alpha = imagecolorallocatealpha($gif, 0, 0, 0, 127);
                imagecolortransparent($gif, $alpha ?: null);
                imagefill($gif, 0, 0, $alpha);

                imagecopy($this->image, $gif, 0, 0, 0, 0, $width, $height);
                imagedestroy($gif);
                break;
            case 'image/jpeg':
                // Load exif data from JPEG images
                if (function_exists('exif_read_data')) {
                    $this->exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($file));
                }
                break;
        }

        // Convert pallete images to true color images
        imagepalettetotruecolor($this->image);

        return $this;
    }

    /**
     * Creates a new image.
     *
     * @param  int  $width The width of the image.
     * @param  int  $height The height of the image.
     * @param  string|array  $color Optional fill color for the new image (default 'transparent').
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function fromNew(int $width, int $height, string|array $color = 'transparent'): static
    {
        $this->image = imagecreatetruecolor($width, $height);

        // Use PNG for dynamically created images because it's lossless and supports transparency
        $this->mimeType = 'image/png';

        // Fill the image with color
        $this->fill($color);

        return $this;
    }

    /**
     * Creates a new image from a string.
     *
     * @param  string  $string The raw image data as a string.
     * @return SimpleImage
     *
     * @throws Exception
     *
     * @example
     *    $string = file_get_contents('image.jpg');
     */
    public function fromString(string $string): SimpleImage|static
    {
        return $this->fromFile('data://;base64,' . base64_encode($string));
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Savers
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Generates an image.
     *
     * @param  string|null  $mimeType The image format to output as a mime type (defaults to the original mime type).
     * @param  array|int  $options Array or Image quality as a percentage (default 100).
     * @return array Returns an array containing the image data and mime type ['data' => '', 'mimeType' => ''].
     *
     * @throws Exception Thrown when WEBP support is not enabled or unsupported format.
     */
    public function generate(string $mimeType = null, array|int $options = 100): array
    {
        // Format defaults to the original mime type
        $mimeType = $mimeType ?: $this->mimeType;

        $quality = null;
        // allow $options to be an int for backwards compatibility to v3
        if (is_int($options)) {
            $quality = $options;
            $options = [];
        }

        // get quality if passed as an option
        if (is_array($options) && array_key_exists('quality', $options)) {
            $quality = intval($options['quality']);
        }

        // Ensure quality is a valid integer
        if ($quality === null) {
            $quality = 100;
        }
        $quality = (int) round(self::keepWithin((int) $quality, 0, 100));

        $alpha = true;
        // get alpha if passed as an option
        if (is_array($options) && array_key_exists('alpha', $options)) {
            $alpha = boolval($options['alpha']);
        }

        $interlace = null; // keep the same
        // get interlace if passed as an option
        if (is_array($options) && array_key_exists('interlace', $options)) {
            $interlace = boolval($options['interlace']);
        }

        // get raw stream from image* functions in providing no path
        $file = null;

        // Capture output
        ob_start();

        // Generate the image
        switch ($mimeType) {
            case 'image/gif':
                imagesavealpha($this->image, $alpha);
                imagegif($this->image, $file);
                break;
            case 'image/jpeg':
                imageinterlace($this->image, $interlace);
                imagejpeg($this->image, $file, $quality);
                break;
            case 'image/png':
                $filters = -1; // imagepng default
                // get filters if passed as an option
                if (is_array($options) && array_key_exists('filters', $options)) {
                    $filters = intval($options['filters']);
                }
                // compression param is called quality in imagepng but that would be
                // misleading in context of SimpleImage
                $compression = -1; // defaults to zlib default which is 6
                // get compression if passed as an option
                if (is_array($options) && array_key_exists('compression', $options)) {
                    $compression = intval($options['compression']);
                }
                if ($compression !== -1) {
                    $compression = (int) round(self::keepWithin($compression, 0, 10));
                }
                imagesavealpha($this->image, $alpha);
                imagepng($this->image, $file, $compression, $filters);
                break;
            case 'image/webp':
                // Not all versions of PHP will have webp support enabled
                if (! function_exists('imagewebp')) {
                    throw new Exception(
                        'WEBP support is not enabled in your version of PHP.',
                        self::ERR_WEBP_NOT_ENABLED
                    );
                }
                // useless but recommended, see https://www.php.net/manual/en/function.imagesavealpha.php
                imagesavealpha($this->image, $alpha);
                imagewebp($this->image, $file, $quality);
                break;
            case 'image/bmp':
            case 'image/x-ms-bmp':
            case 'image/x-windows-bmp':
                // Not all versions of PHP support bmp
                if (! function_exists('imagebmp')) {
                    throw new Exception(
                        'BMP support is not available in your version of PHP.',
                        self::ERR_UNSUPPORTED_FORMAT
                    );
                }
                $compression = true; // imagebmp default
                // get compression if passed as an option
                if (is_array($options) && array_key_exists('compression', $options)) {
                    $compression = is_int($options['compression']) ?
                        $options['compression'] > 0 : boolval($options['compression']);
                }
                imageinterlace($this->image, $interlace);
                imagebmp($this->image, $file, $compression);
                break;
            case 'image/avif':
                // Not all versions of PHP support avif
                if (! function_exists('imageavif')) {
                    throw new Exception(
                        'AVIF support is not available in your version of PHP.',
                        self::ERR_UNSUPPORTED_FORMAT
                    );
                }
                $speed = -1; // imageavif default
                // get speed if passed as an option
                if (is_array($options) && array_key_exists('speed', $options)) {
                    $speed = intval($options['speed']);
                    $speed = self::keepWithin($speed, 0, 10);
                }
                // useless but recommended, see https://www.php.net/manual/en/function.imagesavealpha.php
                imagesavealpha($this->image, $alpha);
                imageavif($this->image, $file, $quality, $speed);
                break;
            default:
                throw new Exception('Unsupported format: ' . $mimeType, self::ERR_UNSUPPORTED_FORMAT);
        }

        // Stop capturing
        $data = ob_get_contents();
        ob_end_clean();

        return [
            'data' => $data,
            'mimeType' => $mimeType,
        ];
    }

    /**
     * Generates a data URI.
     *
     * @param  string|null  $mimeType The image format to output as a mime type (defaults to the original mime type).
     * @param  array|int  $options Array or Image quality as a percentage (default 100).
     * @return string Returns a string containing a data URI.
     *
     * @throws Exception
     */
    public function toDataUri(string $mimeType = null, array|int $options = 100): string
    {
        $image = $this->generate($mimeType, $options);

        return 'data:' . $image['mimeType'] . ';base64,' . base64_encode($image['data']);
    }

    /**
     * Forces the image to be downloaded to the clients machine. Must be called before any output is sent to the screen.
     *
     * @param  string  $filename The filename (without path) to send to the client (e.g. 'image.jpeg').
     * @param  string|null  $mimeType The image format to output as a mime type (defaults to the original mime type).
     * @param  array|int  $options Array or Image quality as a percentage (default 100).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function toDownload(string $filename, string $mimeType = null, array|int $options = 100): static
    {
        $image = $this->generate($mimeType, $options);

        // Set download headers
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Length: ' . strlen($image['data']));
        header('Content-Transfer-Encoding: Binary');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo $image['data'];

        return $this;
    }

    /**
     * Writes the image to a file.
     *
     * @param  string  $file The image format to output as a mime type (defaults to the original mime type).
     * @param  string|null  $mimeType Image quality as a percentage (default 100).
     * @param  array|int  $options Array or Image quality as a percentage (default 100).
     * @return SimpleImage
     *
     * @throws Exception Thrown if failed write to file.
     */
    public function toFile(string $file, string $mimeType = null, array|int $options = 100): static
    {
        $image = $this->generate($mimeType, $options);

        // Save the image to file
        if (! file_put_contents($file, $image['data'])) {
            throw new Exception("Failed to write image to file: $file", self::ERR_WRITE);
        }

        return $this;
    }

    /**
     * Outputs the image to the screen. Must be called before any output is sent to the screen.
     *
     * @param  string|null  $mimeType The image format to output as a mime type (defaults to the original mime type).
     * @param  array|int  $options Array or Image quality as a percentage (default 100).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function toScreen(string $mimeType = null, array|int $options = 100): static
    {
        $image = $this->generate($mimeType, $options);

        // Output the image to stdout
        header('Content-Type: ' . $image['mimeType']);
        echo $image['data'];

        return $this;
    }

    /**
     * Generates an image string.
     *
     * @param  string|null  $mimeType The image format to output as a mime type (defaults to the original mime type).
     * @param  array|int  $options Array or Image quality as a percentage (default 100).
     *
     * @throws Exception
     */
    public function toString(string $mimeType = null, array|int $options = 100): string
    {
        return $this->generate($mimeType, $options)['data'];
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Utilities
    //////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Ensures a numeric value is always within the min and max range.
     *
     * @param  int|float  $value A numeric value to test.
     * @param  int|float  $min The minimum allowed value.
     * @param  int|float  $max The maximum allowed value.
     */
    protected static function keepWithin(int|float $value, int|float $min, int|float $max): int|float
    {
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * Gets the image's current aspect ratio.
     *
     * @return float|int Returns the aspect ratio as a float.
     */
    public function getAspectRatio(): float|int
    {
        return $this->getWidth() / $this->getHeight();
    }

    /**
     * Gets the image's exif data.
     *
     * @return array|null Returns an array of exif data or null if no data is available.
     */
    public function getExif(): ?array
    {
        // returns null if exif value is falsy: null, false or empty array.
        return $this->exif ?: null;
    }

    /**
     * Gets the image's current height.
     */
    public function getHeight(): int
    {
        return (int) imagesy($this->image);
    }

    /**
     * Gets the mime type of the loaded image.
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Gets the image's current orientation.
     *
     * @return string One of the values: 'landscape', 'portrait', or 'square'
     */
    public function getOrientation(): string
    {
        $width = $this->getWidth();
        $height = $this->getHeight();

        if ($width > $height) {
            return 'landscape';
        }
        if ($width < $height) {
            return 'portrait';
        }

        return 'square';
    }

    /**
     * Gets the resolution of the image
     *
     * @return array|bool The resolution as an array of integers: [96, 96]
     */
    public function getResolution(): bool|array
    {
        return imageresolution($this->image);
    }

    /**
     * Gets the image's current width.
     */
    public function getWidth(): int
    {
        return (int) imagesx($this->image);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Manipulation
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Same as PHP's imagecopymerge, but works with transparent images. Used internally for overlay.
     *
     * @param  GdImage  $dstIm Destination image.
     * @param  GdImage  $srcIm Source image.
     * @param  int  $dstX x-coordinate of destination point.
     * @param  int  $dstY y-coordinate of destination point.
     * @param  int  $srcX x-coordinate of source point.
     * @param  int  $srcY y-coordinate of source point.
     * @param  int  $srcW Source width.
     * @param  int  $srcH Source height.
     * @return bool true if success.
     */
    protected static function imageCopyMergeAlpha(GdImage $dstIm, GdImage $srcIm, int $dstX, int $dstY, int $srcX, int $srcY, int $srcW, int $srcH, int $pct): bool
    {
        // Are we merging with transparency?
        if ($pct < 100) {
            // Disable alpha blending and "colorize" the image using a transparent color
            imagealphablending($srcIm, false);
            imagefilter($srcIm, IMG_FILTER_COLORIZE, 0, 0, 0, round(127 * ((100 - $pct) / 100)));
        }

        imagecopy($dstIm, $srcIm, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH);

        return true;
    }

    /**
     * Rotates an image so the orientation will be correct based on its exif data. It is safe to call
     * this method on images that don't have exif data (no changes will be made).
     *
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function autoOrient(): static
    {
        $exif = $this->getExif();

        if (! $exif || ! isset($exif['Orientation'])) {
            return $this;
        }

        switch ($exif['Orientation']) {
            case 1: // Do nothing!
                break;
            case 2: // Flip horizontally
                $this->flip('x');
                break;
            case 3: // Rotate 180 degrees
                $this->rotate(180);
                break;
            case 4: // Flip vertically
                $this->flip('y');
                break;
            case 5: // Rotate 90 degrees clockwise and flip vertically
                $this->flip('y')->rotate(90);
                break;
            case 6: // Rotate 90 clockwise
                $this->rotate(90);
                break;
            case 7: // Rotate 90 clockwise and flip horizontally
                $this->flip('x')->rotate(90);
                break;
            case 8: // Rotate 90 counterclockwise
                $this->rotate(-90);
                break;
        }

        return $this;
    }

    /**
     * Proportionally resize the image to fit inside a specific width and height.
     *
     * @param  int  $maxWidth The maximum width the image can be.
     * @param  int  $maxHeight The maximum height the image can be.
     * @return SimpleImage
     */
    public function bestFit(int $maxWidth, int $maxHeight): static
    {
        // If the image already fits, there's nothing to do
        if ($this->getWidth() <= $maxWidth && $this->getHeight() <= $maxHeight) {
            return $this;
        }

        // Calculate max width or height based on orientation
        if ($this->getOrientation() === 'portrait') {
            $height = $maxHeight;
            $width = (int) round($maxHeight * $this->getAspectRatio());
        } else {
            $width = $maxWidth;
            $height = (int) round($maxWidth / $this->getAspectRatio());
        }

        // Reduce to max width
        if ($width > $maxWidth) {
            $width = $maxWidth;
            $height = (int) round($width / $this->getAspectRatio());
        }

        // Reduce to max height
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width = (int) round($height * $this->getAspectRatio());
        }

        return $this->resize($width, $height);
    }

    /**
     * Crop the image.
     *
     * @param  int|float  $x1 Top left x coordinate.
     * @param  int|float  $y1 Top left y coordinate.
     * @param  int|float  $x2 Bottom right x coordinate.
     * @param  int|float  $y2 Bottom right x coordinate.
     * @return SimpleImage
     */
    public function crop(int|float $x1, int|float $y1, int|float $x2, int|float $y2): static
    {
        // Keep crop within image dimensions
        $x1 = self::keepWithin($x1, 0, $this->getWidth());
        $x2 = self::keepWithin($x2, 0, $this->getWidth());
        $y1 = self::keepWithin($y1, 0, $this->getHeight());
        $y2 = self::keepWithin($y2, 0, $this->getHeight());

        // Avoid using native imagecrop() because of a bug with PNG transparency
        $dstW = abs($x2 - $x1);
        $dstH = abs($y2 - $y1);
        $newImage = imagecreatetruecolor((int) $dstW, (int) $dstH);
        $transparentColor = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagecolortransparent($newImage, $transparentColor ?: null);
        imagefill($newImage, 0, 0, $transparentColor);

        // Crop it
        imagecopyresampled(
            $newImage,
            $this->image,
            0,
            0,
            (int) round(min($x1, $x2)),
            (int) round(min($y1, $y2)),
            (int) $dstW,
            (int) $dstH,
            (int) $dstW,
            (int) $dstH
        );

        // Swap out the new image
        $this->image = $newImage;

        return $this;
    }

    /**
     * Applies a duotone filter to the image.
     *
     * @param  string|array  $lightColor The lightest color in the duotone.
     * @param  string|array  $darkColor The darkest color in the duotone.
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function duotone(string|array $lightColor, string|array $darkColor): static
    {
        $lightColor = self::normalizeColor($lightColor);
        $darkColor = self::normalizeColor($darkColor);

        // Calculate averages between light and dark colors
        $redAvg = $lightColor['red'] - $darkColor['red'];
        $greenAvg = $lightColor['green'] - $darkColor['green'];
        $blueAvg = $lightColor['blue'] - $darkColor['blue'];

        // Create a matrix of all possible duotone colors based on gray values
        $pixels = [];
        for ($i = 0; $i <= 255; $i++) {
            $grayAvg = $i / 255;
            $pixels['red'][$i] = $darkColor['red'] + $grayAvg * $redAvg;
            $pixels['green'][$i] = $darkColor['green'] + $grayAvg * $greenAvg;
            $pixels['blue'][$i] = $darkColor['blue'] + $grayAvg * $blueAvg;
        }

        // Apply the filter pixel by pixel
        for ($x = 0; $x < $this->getWidth(); $x++) {
            for ($y = 0; $y < $this->getHeight(); $y++) {
                $rgb = $this->getColorAt($x, $y);
                $gray = min(255, round(0.299 * $rgb['red'] + 0.114 * $rgb['blue'] + 0.587 * $rgb['green']));
                $this->dot($x, $y, [
                    'red' => $pixels['red'][$gray],
                    'green' => $pixels['green'][$gray],
                    'blue' => $pixels['blue'][$gray],
                ]);
            }
        }

        return $this;
    }

    /**
     * Proportionally resize the image to a specific width.
     *
     * @param  int  $width The width to resize the image to.
     * @return SimpleImage
     *
     *@deprecated
     *    This method was deprecated in version 3.2.2 and will be removed in version 4.0.
     *    Please use `resize(null, $height)` instead.
     */
    public function fitToWidth(int $width): static
    {
        return $this->resize($width);
    }

    /**
     * Flip the image horizontally or vertically.
     *
     * @param  string  $direction The direction to flip: x|y|both.
     * @return SimpleImage
     */
    public function flip(string $direction): static
    {
        match ($direction) {
            'x' => imageflip($this->image, IMG_FLIP_HORIZONTAL),
            'y' => imageflip($this->image, IMG_FLIP_VERTICAL),
            'both' => imageflip($this->image, IMG_FLIP_BOTH),
            default => $this,
        };

        return $this;
    }

    /**
     * Reduces the image to a maximum number of colors.
     *
     * @param  int  $max The maximum number of colors to use.
     * @param  bool  $dither Whether or not to use a dithering effect (default true).
     * @return SimpleImage
     */
    public function maxColors(int $max, bool $dither = true): static
    {
        imagetruecolortopalette($this->image, $dither, max(1, $max));

        return $this;
    }

    /**
     * Place an image on top of the current image.
     *
     * @param  string|SimpleImage  $overlay The image to overlay. This can be a filename, a data URI, or a SimpleImage object.
     * @param  string  $anchor The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center').
     * @param  float|int  $opacity The opacity level of the overlay 0-1 (default 1).
     * @param  int  $xOffset Horizontal offset in pixels (default 0).
     * @param  int  $yOffset Vertical offset in pixels (default 0).
     * @param  bool  $calculateOffsetFromEdge Calculate Offset referring to the edges of the image (default false).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function overlay(string|SimpleImage $overlay, string $anchor = 'center', float|int $opacity = 1, int $xOffset = 0, int $yOffset = 0, bool $calculateOffsetFromEdge = false): static
    {
        // Load overlay image
        if (! ($overlay instanceof SimpleImage)) {
            $overlay = new SimpleImage($overlay);
        }

        // Convert opacity
        $opacity = (int) round(self::keepWithin($opacity, 0, 1) * 100);

        // Get available space
        $spaceX = $this->getWidth() - $overlay->getWidth();
        $spaceY = $this->getHeight() - $overlay->getHeight();

        // Set default center
        $x = (int) round(($spaceX / 2) + ($calculateOffsetFromEdge ? 0 : $xOffset));
        $y = (int) round(($spaceY / 2) + ($calculateOffsetFromEdge ? 0 : $yOffset));

        // Determine if top|bottom
        if (str_contains($anchor, 'top')) {
            $y = $yOffset;
        } elseif (str_contains($anchor, 'bottom')) {
            $y = $spaceY + ($calculateOffsetFromEdge ? -$yOffset : $yOffset);
        }

        // Determine if left|right
        if (str_contains($anchor, 'left')) {
            $x = $xOffset;
        } elseif (str_contains($anchor, 'right')) {
            $x = $spaceX + ($calculateOffsetFromEdge ? -$xOffset : $xOffset);
        }

        // Perform the overlay
        self::imageCopyMergeAlpha(
            $this->image,
            $overlay->image,
            $x,
            $y,
            0,
            0,
            $overlay->getWidth(),
            $overlay->getHeight(),
            $opacity
        );

        return $this;
    }

    /**
     * Resize an image to the specified dimensions. If only one dimension is specified, the image will be resized proportionally.
     *
     * @param  int|null  $width The new image width.
     * @param  int|null  $height The new image height.
     * @return SimpleImage
     */
    public function resize(int $width = null, int $height = null): static
    {
        // No dimensions specified
        if (! $width && ! $height) {
            return $this;
        }

        // Resize to width
        if ($width && ! $height) {
            $height = (int) round($width / $this->getAspectRatio());
        }

        // Resize to height
        if (! $width && $height) {
            $width = (int) round($height * $this->getAspectRatio());
        }

        // If the dimensions are the same, there's no need to resize
        if ($this->getWidth() === $width && $this->getHeight() === $height) {
            return $this;
        }

        // We can't use imagescale because it doesn't seem to preserve transparency properly. The
        // workaround is to create a new truecolor image, allocate a transparent color, and copy the
        // image over to it using imagecopyresampled.
        $newImage = imagecreatetruecolor($width, $height);
        $transparentColor = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagecolortransparent($newImage, $transparentColor);
        imagefill($newImage, 0, 0, $transparentColor);
        imagecopyresampled(
            $newImage,
            $this->image,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $this->getWidth(),
            $this->getHeight()
        );

        // Swap out the new image
        $this->image = $newImage;

        return $this;
    }

    /**
     * Sets an image's resolution, as per https://www.php.net/manual/en/function.imageresolution.php
     *
     * @param  int  $res_x The horizontal resolution in DPI.
     * @param  int|null  $res_y The vertical resolution in DPI
     * @return SimpleImage
     */
    public function resolution(int $res_x, int $res_y = null): static
    {
        if (is_null($res_y)) {
            imageresolution($this->image, $res_x);
        } else {
            imageresolution($this->image, $res_x, $res_y);
        }

        return $this;
    }

    /**
     * Rotates the image.
     *
     * @param  int  $angle The angle of rotation (-360 - 360).
     * @param  string|array  $backgroundColor The background color to use for the uncovered zone area after rotation (default 'transparent').
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function rotate(int $angle, string|array $backgroundColor = 'transparent'): static
    {
        // Rotate the image on a canvas with the desired background color
        $backgroundColor = $this->allocateColor($backgroundColor);

        $this->image = imagerotate(
            $this->image,
            - (self::keepWithin($angle, -360, 360)),
            $backgroundColor
        );
        imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 0, 0, 0, 127));

        return $this;
    }

    /**
     * Adds text to the image.
     *
     * @param  string  $text The desired text.
     * @param  array  $options
     *    An array of options.
     *       - fontFile* (string) - The TrueType (or compatible) font file to use.
     *       - size (integer) - The size of the font in pixels (default 12).
     *       - color (string|array) - The text color (default black).
     *       - anchor (string) - The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center').
     *       - xOffset (integer) - The horizontal offset in pixels (default 0).
     *       - yOffset (integer) - The vertical offset in pixels (default 0).
     *       - shadow (array) - Text shadow params.
     *          - x* (integer) - Horizontal offset in pixels.
     *          - y* (integer) - Vertical offset in pixels.
     *          - color* (string|array) - The text shadow color.
     *       - $calculateOffsetFromEdge (bool) - Calculate offsets from the edge of the image (default false).
     *       - $baselineAlign (bool) - Align the text font with the baseline. (default true).
     * @param  array|null  $boundary
     *    If passed, this variable will contain an array with coordinates that surround the text: [x1, y1, x2, y2, width, height].
     *    This can be used for calculating the text's position after it gets added to the image.
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function text(string $text, array $options, array &$boundary = null): static
    {
        // Check for freetype support
        if (! function_exists('imagettftext')) {
            throw new Exception(
                'Freetype support is not enabled in your version of PHP.',
                self::ERR_FREETYPE_NOT_ENABLED
            );
        }

        // Default options
        $options = array_merge([
            'fontFile' => null,
            'size' => 12,
            'color' => 'black',
            'anchor' => 'center',
            'xOffset' => 0,
            'yOffset' => 0,
            'shadow' => null,
            'calculateOffsetFromEdge' => false,
            'baselineAlign' => true,
        ], $options);

        // Extract and normalize options
        $fontFile = $options['fontFile'];
        $size = ($options['size'] / 96) * 72; // Convert px to pt (72pt per inch, 96px per inch)
        $color = $this->allocateColor($options['color']);
        $anchor = $options['anchor'];
        $xOffset = $options['xOffset'];
        $yOffset = $options['yOffset'];
        $calculateOffsetFromEdge = $options['calculateOffsetFromEdge'];
        $baselineAlign = $options['baselineAlign'];
        $angle = 0;

        // Calculate the bounding box dimensions
        //
        // Since imagettfbox() returns a bounding box from the text's baseline, we can end up with
        // different heights for different strings of the same font size. For example, 'type' will often
        // be taller than 'text' because the former has a descending letter.
        //
        // To compensate for this, we created a temporary bounding box to measure the maximum height
        // that the font used can occupy. Based on this, we can adjust the text vertically so that it
        // appears inside the box with a good consistency.
        //
        // See: https://github.com/claviska/SimpleImage/issues/165
        //

        $boxText = imagettfbbox($size, $angle, $fontFile, $text);
        if (! $boxText) {
            throw new Exception("Unable to load font file: $fontFile", self::ERR_FONT_FILE);
        }

        $boxWidth = abs($boxText[4] - $boxText[0]);
        $boxHeight = abs($boxText[5] - $boxText[1]);

        // Calculate Offset referring to the edges of the image.
        // Just invert the value for bottom|right;
        if ($calculateOffsetFromEdge) {
            if (str_contains($anchor, 'bottom')) {
                $yOffset *= -1;
            }
            if (str_contains($anchor, 'right')) {
                $xOffset *= -1;
            }
        }

        // Align the text font with the baseline.
        // I use $yOffset to inject the vertical alignment correction value.
        if ($baselineAlign) {
            // Create a temporary box to obtain the maximum height that this font can use.
            $boxFull = imagettfbbox($size, $angle, $fontFile, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
            // Based on the maximum height, the text is aligned.
            if (str_contains($anchor, 'bottom')) {
                $yOffset -= $boxFull[1];
            } elseif (str_contains($anchor, 'top')) {
                $yOffset += abs($boxFull[5]) - $boxHeight;
            } else { // center
                $boxFullHeight = abs($boxFull[1]) + abs($boxFull[5]);
                $yOffset += ($boxFullHeight / 2) - ($boxHeight / 2) - abs($boxFull[1]);
            }
        } else {
            // Prevents fonts rendered outside the box boundary from being cut.
            // Example: 'Scriptina' font, some letters invade the space of the previous or subsequent letter.
            $yOffset -= $boxText[1];
        }

        // Prevents fonts rendered outside the box boundary from being cut.
        // Example: 'Scriptina' font, some letters invade the space of the previous or subsequent letter.
        $xOffset -= $boxText[0];

        // Determine position
        switch ($anchor) {
            case 'top left':
                $x = $xOffset;
                $y = $yOffset + $boxHeight;
                break;
            case 'top right':
                $x = $this->getWidth() - $boxWidth + $xOffset;
                $y = $yOffset + $boxHeight;
                break;
            case 'top':
                $x = ($this->getWidth() / 2) - ($boxWidth / 2) + $xOffset;
                $y = $yOffset + $boxHeight;
                break;
            case 'bottom left':
                $x = $xOffset;
                $y = $this->getHeight() + $yOffset;
                break;
            case 'bottom right':
                $x = $this->getWidth() - $boxWidth + $xOffset;
                $y = $this->getHeight() + $yOffset;
                break;
            case 'bottom':
                $x = ($this->getWidth() / 2) - ($boxWidth / 2) + $xOffset;
                $y = $this->getHeight() + $yOffset;
                break;
            case 'left':
                $x = $xOffset;
                $y = ($this->getHeight() / 2) - (($boxHeight / 2) - $boxHeight) + $yOffset;
                break;
            case 'right':
                $x = $this->getWidth() - $boxWidth + $xOffset;
                $y = ($this->getHeight() / 2) - (($boxHeight / 2) - $boxHeight) + $yOffset;
                break;
            default: // center
                $x = ($this->getWidth() / 2) - ($boxWidth / 2) + $xOffset;
                $y = ($this->getHeight() / 2) - (($boxHeight / 2) - $boxHeight) + $yOffset;
                break;
        }
        $x = (int) round($x);
        $y = (int) round($y);

        // Pass the boundary back by reference
        $boundary = [
            'x1' => $x + $boxText[0],
            'y1' => $y + $boxText[1] - $boxHeight, // $y is the baseline, not the top!
            'x2' => $x + $boxWidth + $boxText[0],
            'y2' => $y + $boxText[1],
            'width' => $boxWidth,
            'height' => $boxHeight,
        ];

        // Text shadow
        if (is_array($options['shadow'])) {
            imagettftext(
                $this->image,
                $size,
                $angle,
                $x + $options['shadow']['x'],
                $y + $options['shadow']['y'],
                $this->allocateColor($options['shadow']['color']),
                $fontFile,
                $text
            );
        }

        // Draw the text
        imagettftext($this->image, $size, $angle, $x, $y, $color, $fontFile, $text);

        return $this;
    }

    /**
     * Adds text with a line break to the image.
     *
     * @param  string  $text The desired text.
     * @param  array  $options
     *  An array of options.
     *     - fontFile* (string) - The TrueType (or compatible) font file to use.
     *     - size (integer) - The size of the font in pixels (default 12).
     *     - color (string|array) - The text color (default black).
     *     - anchor (string) - The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center').
     *     - xOffset (integer) - The horizontal offset in pixels (default 0). Has no effect when anchor is 'center'.
     *     - yOffset (integer) - The vertical offset in pixels (default 0). Has no effect when anchor is 'center'.
     *     - shadow (array) - Text shadow params.
     *       - x* (integer) - Horizontal offset in pixels.
     *       - y* (integer) - Vertical offset in pixels.
     *       - color* (string|array) - The text shadow color.
     *     - $calculateOffsetFromEdge (bool) - Calculate offsets from the edge of the image (default false).
     *     - width (int) - Width of text box (default image width).
     *     - align (string) - How to align text: 'left', 'right', 'center', 'justify' (default 'left').
     *     - leading (float) - Increase/decrease spacing between lines of text (default 0).
     *     - opacity (float) - The opacity level of the text 0-1 (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function textBox(string $text, array $options): static
    {
        // default width of image
        $maxWidth = $this->getWidth();
        // Default options
        $options = array_merge([
            'fontFile' => null,
            'size' => 12,
            'color' => 'black',
            'anchor' => 'center',
            'xOffset' => 0,
            'yOffset' => 0,
            'shadow' => null,
            'calculateOffsetFromEdge' => false,
            'width' => $maxWidth,
            'align' => 'left',
            'leading' => 0,
            'opacity' => 1,
        ], $options);

        // Extract and normalize options
        $fontFile = $options['fontFile'];
        $fontSize = $fontSizePx = $options['size'];
        $fontSize = ($fontSize / 96) * 72; // Convert px to pt (72pt per inch, 96px per inch)
        $color = $options['color'];
        $anchor = $options['anchor'];
        $xOffset = $options['xOffset'];
        $yOffset = $options['yOffset'];
        $shadow = $options['shadow'];
        $calculateOffsetFromEdge = $options['calculateOffsetFromEdge'];
        $maxWidth = intval($options['width']);
        $leading = $options['leading'];
        $leading = self::keepWithin($leading, ($fontSizePx * -1), $leading);
        $opacity = $options['opacity'];

        $align = $options['align'];
        if ($align == 'right') {
            $align = 'top right';
        } elseif ($align == 'center') {
            $align = 'top';
        } elseif ($align == 'justify') {
            $align = 'justify';
        } else {
            $align = 'top left';
        }

        [$lines, $isLastLine, $lastLineHeight] = self::textSeparateLines($text, $fontFile, $fontSize, $maxWidth);

        $maxHeight = (int) round(((is_countable($lines) ? count($lines) : 0) - 1) * ($fontSizePx * 1.2 + $leading) + $lastLineHeight);

        $imageText = new SimpleImage();
        $imageText->fromNew($maxWidth, $maxHeight);

        // Align left/center/right
        if ($align != 'justify') {
            foreach ($lines as $key => $line) {
                if ($align == 'top') {
                    $line = trim($line);
                } // If is justify = 'center'
                $imageText->text($line, ['fontFile' => $fontFile, 'size' => $fontSizePx, 'color' => $color, 'anchor' => $align, 'xOffset' => 0, 'yOffset' => $key * ($fontSizePx * 1.2 + $leading), 'shadow' => $shadow, 'calculateOffsetFromEdge' => true]);
            }

            // Justify
        } else {
            foreach ($lines as $keyLine => $line) {
                // Check if there are spaces at the beginning of the sentence
                $spaces = 0;
                if (preg_match("/^\s+/", $line, $match)) {
                    // Count spaces
                    $spaces = strlen($match[0]);
                    $line = ltrim($line);
                }

                // Separate words
                $words = preg_split("/\s+/", $line);
                // Include spaces with the first word
                $words[0] = str_repeat(' ', $spaces) . $words[0];

                // Calculates the space occupied by all words
                $wordsSize = [];
                foreach ($words as $key => $word) {
                    $wordBox = imagettfbbox($fontSize, 0, $fontFile, $word);
                    $wordWidth = abs($wordBox[4] - $wordBox[0]);
                    $wordsSize[$key] = $wordWidth;
                }
                $wordsSizeTotal = array_sum($wordsSize);

                // Calculates the required space between words
                $countWords = count($words);
                $wordSpacing = 0;
                if ($countWords > 1) {
                    $wordSpacing = ($maxWidth - $wordsSizeTotal) / ($countWords - 1);
                    $wordSpacing = round($wordSpacing, 3);
                }

                $xOffsetJustify = 0;
                foreach ($words as $key => $word) {
                    if ($isLastLine[$keyLine]) {
                        if ($key < (count($words) - 1)) {
                            continue;
                        }
                        $word = $line;
                    }
                    $imageText->text(
                        $word,
                        ['fontFile' => $fontFile, 'size' => $fontSizePx, 'color' => $color, 'anchor' => 'top left', 'xOffset' => $xOffsetJustify, 'yOffset' => $keyLine * ($fontSizePx * 1.2 + $leading), 'shadow' => $shadow, 'calculateOffsetFromEdge' => true]
                    );
                    // Calculate offset for next word
                    $xOffsetJustify += $wordsSize[$key] + $wordSpacing;
                }
            }
        }

        $this->overlay($imageText, $anchor, $opacity, $xOffset, $yOffset, $calculateOffsetFromEdge);

        return $this;
    }

    /**
     * Receives a text and breaks into LINES.
     */
    private function textSeparateLines(string $text, string $fontFile, float $fontSize, int $maxWidth): array
    {
        $lines = [];
        $words = self::textSeparateWords($text);
        $countWords = count($words) - 1;
        $lines[0] = '';
        $lineKey = 0;
        $isLastLine = [];
        for ($i = 0; $i < $countWords; $i++) {
            $word = $words[$i];
            $isLastLine[$lineKey] = false;
            if ($word === PHP_EOL) {
                $isLastLine[$lineKey] = true;
                $lineKey++;
                $lines[$lineKey] = '';

                continue;
            }
            $lineBox = imagettfbbox($fontSize, 0, $fontFile, $lines[$lineKey] . $word);
            if (abs($lineBox[4] - $lineBox[0]) < $maxWidth) {
                $lines[$lineKey] .= $word . ' ';
            } else {
                $lineKey++;
                $lines[$lineKey] = $word . ' ';
            }
        }
        $isLastLine[$lineKey] = true;
        // Exclude space of right
        $lines = array_map('rtrim', $lines);
        // Calculate height of last line
        $boxFull = imagettfbbox($fontSize, 0, $fontFile, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $lineBox = imagettfbbox($fontSize, 0, $fontFile, $lines[$lineKey]);
        // Height of last line = ascender of $boxFull + descender of $lineBox
        $lastLineHeight = abs($lineBox[1]) + abs($boxFull[5]);

        return [$lines, $isLastLine, $lastLineHeight];
    }

    /**
     * Receives a text and breaks into WORD / SPACE / NEW LINE.
     */
    private function textSeparateWords(string $text): array
    {
        // Normalizes line break
        $text = strval(preg_replace('/(\r\n|\n|\r)/', PHP_EOL, $text));
        $text = explode(PHP_EOL, $text);
        $newText = [];
        foreach ($text as $line) {
            $newText = array_merge($newText, explode(' ', $line), [PHP_EOL]);
        }

        return $newText;
    }

    /**
     * Creates a thumbnail image. This function attempts to get the image as close to the provided
     * dimensions as possible, then crops the remaining overflow to force the desired size. Useful
     * for generating thumbnail images.
     *
     * @param  int  $width The thumbnail width.
     * @param  int  $height The thumbnail height.
     * @param  string  $anchor The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center').
     * @return SimpleImage
     */
    public function thumbnail(int $width, int $height, string $anchor = 'center'): SimpleImage|static
    {
        // Determine aspect ratios
        $currentRatio = $this->getHeight() / $this->getWidth();
        $targetRatio = $height / $width;

        // Fit to height/width
        if ($targetRatio > $currentRatio) {
            $this->resize(null, $height);
        } else {
            $this->resize($width);
        }

        switch ($anchor) {
            case 'top':
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = 0;
                $y2 = $height;
                break;
            case 'bottom':
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            case 'left':
                $x1 = 0;
                $x2 = $width;
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
            case 'right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
            case 'top left':
                $x1 = 0;
                $x2 = $width;
                $y1 = 0;
                $y2 = $height;
                break;
            case 'top right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = 0;
                $y2 = $height;
                break;
            case 'bottom left':
                $x1 = 0;
                $x2 = $width;
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            case 'bottom right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            default:
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
        }

        // Return the cropped thumbnail image
        return $this->crop($x1, $y1, $x2, $y2);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Drawing
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Draws an arc.
     *
     * @param  int  $x The x coordinate of the arc's center.
     * @param  int  $y The y coordinate of the arc's center.
     * @param  int  $width The width of the arc.
     * @param  int  $height The height of the arc.
     * @param  int  $start The start of the arc in degrees.
     * @param  int  $end The end of the arc in degrees.
     * @param  string|array  $color The arc color.
     * @param  int|string  $thickness Line thickness in pixels or 'filled' (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function arc(int $x, int $y, int $width, int $height, int $start, int $end, string|array $color, int|string $thickness = 1): static
    {
        // Allocate the color
        $tempColor = $this->allocateColor($color);
        imagesetthickness($this->image, 1);

        // Draw an arc
        if ($thickness === 'filled') {
            imagefilledarc($this->image, $x, $y, $width, $height, $start, $end, $tempColor, IMG_ARC_PIE);
        } elseif ($thickness === 1) {
            imagearc($this->image, $x, $y, $width, $height, $start, $end, $tempColor);
        } else {
            // New temp image
            $tempImage = new SimpleImage();
            $tempImage->fromNew($this->getWidth(), $this->getHeight());

            // Draw a large ellipse filled with $color (+$thickness pixels)
            $tempColor = $tempImage->allocateColor($color);
            imagefilledarc($tempImage->image, $x, $y, $width + $thickness, $height + $thickness, $start, $end, $tempColor, IMG_ARC_PIE);

            // Draw a smaller ellipse filled with red|blue (-$thickness pixels)
            $tempColor = (self::normalizeColor($color)['red'] == 255) ? 'blue' : 'red';
            $tempColor = $tempImage->allocateColor($tempColor);
            imagefilledarc($tempImage->image, $x, $y, $width - $thickness, $height - $thickness, $start, $end, $tempColor, IMG_ARC_PIE);

            // Replace the color of the smaller ellipse with 'transparent'
            $tempImage->excludeInsideColor($x, $y, $color);

            // Apply the temp image
            $this->overlay($tempImage);
        }

        return $this;
    }

    /**
     * Draws a border around the image.
     *
     * @param  string|array  $color The border color.
     * @param  int  $thickness The thickness of the border (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function border(string|array $color, int $thickness = 1): static
    {
        $x1 = -1;
        $y1 = 0;
        $x2 = $this->getWidth();
        $y2 = $this->getHeight() - 1;

        $color = $this->allocateColor($color);
        imagesetthickness($this->image, $thickness * 2);
        imagerectangle($this->image, $x1, $y1, $x2, $y2, $color);

        return $this;
    }

    /**
     * Draws a single pixel dot.
     *
     * @param  int  $x The x coordinate of the dot.
     * @param  int  $y The y coordinate of the dot.
     * @param  string|array  $color The dot color.
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function dot(int $x, int $y, string|array $color): static
    {
        $color = $this->allocateColor($color);
        imagesetpixel($this->image, $x, $y, $color);

        return $this;
    }

    /**
     * Draws an ellipse.
     *
     * @param  int  $x The x coordinate of the center.
     * @param  int  $y The y coordinate of the center.
     * @param  int  $width The ellipse width.
     * @param  int  $height The ellipse height.
     * @param  string|array  $color The ellipse color.
     * @param  string|int|array  $thickness Line thickness in pixels or 'filled' (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function ellipse(int $x, int $y, int $width, int $height, string|array $color, string|int|array $thickness = 1): static
    {
        // Allocate the color
        $tempColor = $this->allocateColor($color);
        imagesetthickness($this->image, 1);

        // Draw an ellipse
        if ($thickness == 'filled') {
            imagefilledellipse($this->image, $x, $y, $width, $height, $tempColor);
        } elseif ($thickness === 1) {
            imageellipse($this->image, $x, $y, $width, $height, $tempColor);
        } else {
            // New temp image
            $tempImage = new SimpleImage();
            $tempImage->fromNew($this->getWidth(), $this->getHeight());

            // Draw a large ellipse filled with $color (+$thickness pixels)
            $tempColor = $tempImage->allocateColor($color);
            imagefilledellipse($tempImage->image, $x, $y, $width + $thickness, $height + $thickness, $tempColor);

            // Draw a smaller ellipse filled with red|blue (-$thickness pixels)
            $tempColor = (self::normalizeColor($color)['red'] == 255) ? 'blue' : 'red';
            $tempColor = $tempImage->allocateColor($tempColor);
            imagefilledellipse($tempImage->image, $x, $y, $width - $thickness, $height - $thickness, $tempColor);

            // Replace the color of the smaller ellipse with 'transparent'
            $tempImage->excludeInsideColor($x, $y, $color);

            // Apply the temp image
            $this->overlay($tempImage);
        }

        return $this;
    }

    /**
     * Fills the image with a solid color.
     *
     * @param  string|array  $color The fill color.
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function fill(string|array $color): static
    {
        // Draw a filled rectangle over the entire image
        $this->rectangle(0, 0, $this->getWidth(), $this->getHeight(), 'white', 'filled');

        // Now flood it with the appropriate color
        $color = $this->allocateColor($color);
        imagefill($this->image, 0, 0, $color);

        return $this;
    }

    /**
     * Draws a line.
     *
     * @param  int  $x1 The x coordinate for the first point.
     * @param  int  $y1 The y coordinate for the first point.
     * @param  int  $x2 The x coordinate for the second point.
     * @param  int  $y2 The y coordinate for the second point.
     * @param  string|array  $color The line color.
     * @param  int  $thickness The line thickness (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function line(int $x1, int $y1, int $x2, int $y2, string|array $color, int $thickness = 1): static
    {
        // Allocate the color
        $color = $this->allocateColor($color);

        // Draw a line
        imagesetthickness($this->image, $thickness);
        imageline($this->image, $x1, $y1, $x2, $y2, $color);

        return $this;
    }

    /**
     * Draws a polygon.
     *
     * @param  array  $vertices
     *    The polygon's vertices in an array of x/y arrays.
     *    Example:
     *        [
     *            ['x' => x1, 'y' => y1],
     *            ['x' => x2, 'y' => y2],
     *            ['x' => xN, 'y' => yN]
     *        ]
     * @param  string|array  $color The polygon color.
     * @param  string|int|array  $thickness Line thickness in pixels or 'filled' (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function polygon(array $vertices, string|array $color, string|int|array $thickness = 1): static
    {
        // Allocate the color
        $color = $this->allocateColor($color);

        // Convert [['x' => x1, 'y' => x1], ['x' => x1, 'y' => y2], ...] to [x1, y1, x2, y2, ...]
        $points = [];
        foreach ($vertices as $vals) {
            $points[] = $vals['x'];
            $points[] = $vals['y'];
        }

        // Draw a polygon
        if ($thickness == 'filled') {
            imagesetthickness($this->image, 1);
            imagefilledpolygon($this->image, $points, count($vertices), $color);
        } else {
            imagesetthickness($this->image, $thickness);
            imagepolygon($this->image, $points, count($vertices), $color);
        }

        return $this;
    }

    /**
     * Draws a rectangle.
     *
     * @param  int  $x1 The upper left x coordinate.
     * @param  int  $y1 The upper left y coordinate.
     * @param  int  $x2 The bottom right x coordinate.
     * @param  int  $y2 The bottom right y coordinate.
     * @param  string|array  $color The rectangle color.
     * @param  string|int|array  $thickness Line thickness in pixels or 'filled' (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function rectangle(int $x1, int $y1, int $x2, int $y2, string|array $color, string|int|array $thickness = 1): static
    {
        // Allocate the color
        $color = $this->allocateColor($color);

        // Draw a rectangle
        if ($thickness == 'filled') {
            imagesetthickness($this->image, 1);
            imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $color);
        } else {
            imagesetthickness($this->image, $thickness);
            imagerectangle($this->image, $x1, $y1, $x2, $y2, $color);
        }

        return $this;
    }

    /**
     * Draws a rounded rectangle.
     *
     * @param  int  $x1 The upper left x coordinate.
     * @param  int  $y1 The upper left y coordinate.
     * @param  int  $x2 The bottom right x coordinate.
     * @param  int  $y2 The bottom right y coordinate.
     * @param  int  $radius The border radius in pixels.
     * @param  string|array  $color The rectangle color.
     * @param  string|int|array  $thickness Line thickness in pixels or 'filled' (default 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function roundedRectangle(int $x1, int $y1, int $x2, int $y2, int $radius, string|array $color, string|int|array $thickness = 1): static
    {
        if ($thickness == 'filled') {
            // Draw the filled rectangle without edges
            $this->rectangle($x1 + $radius + 1, $y1, $x2 - $radius - 1, $y2, $color, 'filled');
            $this->rectangle($x1, $y1 + $radius + 1, $x1 + $radius, $y2 - $radius - 1, $color, 'filled');
            $this->rectangle($x2 - $radius, $y1 + $radius + 1, $x2, $y2 - $radius - 1, $color, 'filled');

            // Fill in the edges with arcs
            $this->arc($x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color, 'filled');
            $this->arc($x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color, 'filled');
            $this->arc($x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color, 'filled');
            $this->arc($x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 360, 90, $color, 'filled');
        } else {
            $offset = $thickness / 2;
            $x1 -= $offset;
            $x2 += $offset;
            $y1 -= $offset;
            $y2 += $offset;
            $radius = self::keepWithin($radius, 0, min(($x2 - $x1) / 2, ($y2 - $y1) / 2) - 1);
            $radius = (int) floor($radius);
            $thickness = self::keepWithin($thickness, 1, min(($x2 - $x1) / 2, ($y2 - $y1) / 2));

            // New temp image
            $tempImage = new SimpleImage();
            $tempImage->fromNew($this->getWidth(), $this->getHeight());

            // Draw a large rectangle filled with $color
            $tempImage->roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, 'filled');

            // Draw a smaller rectangle filled with red|blue (-$thickness pixels on each side)
            $tempColor = (self::normalizeColor($color)['red'] == 255) ? 'blue' : 'red';
            $radius = $radius - $thickness;
            $radius = self::keepWithin($radius, 0, $radius);
            $tempImage->roundedRectangle(
                $x1 + $thickness,
                $y1 + $thickness,
                $x2 - $thickness,
                $y2 - $thickness,
                $radius,
                $tempColor,
                'filled'
            );

            // Replace the color of the smaller rectangle with 'transparent'
            $tempImage->excludeInsideColor(($x2 + $x1) / 2, ($y2 + $y1) / 2, $color);

            // Apply the temp image
            $this->overlay($tempImage);
        }

        return $this;
    }

    /**
     * Exclude inside color.
     * Used for roundedRectangle(), ellipse() and arc()
     *
     * @param  int  $x certer x of rectangle.
     * @param  int  $y certer y of rectangle.
     * @param  string|array  $borderColor The color of border.
     *
     * @throws Exception
     */
    private function excludeInsideColor(int $x, int $y, string|array $borderColor): static
    {
        $borderColor = $this->allocateColor($borderColor);
        $transparent = $this->allocateColor('transparent');
        imagefilltoborder($this->image, $x, $y, $borderColor, $transparent);

        return $this;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Filters
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Applies the blur filter.
     *
     * @param  string  $type The blur algorithm to use: 'selective', 'gaussian' (default 'gaussian').
     * @param  int  $passes The number of time to apply the filter, enhancing the effect (default 1).
     * @return SimpleImage
     */
    public function blur(string $type = 'selective', int $passes = 1): static
    {
        $filter = $type === 'gaussian' ? IMG_FILTER_GAUSSIAN_BLUR : IMG_FILTER_SELECTIVE_BLUR;

        for ($i = 0; $i < $passes; $i++) {
            imagefilter($this->image, $filter);
        }

        return $this;
    }

    /**
     * Applies the brightness filter to brighten the image.
     *
     * @param  int  $percentage Percentage to brighten the image (0 - 100).
     * @return SimpleImage
     */
    public function brighten(int $percentage): static
    {
        $percentage = self::keepWithin(255 * $percentage / 100, 0, 255);

        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $percentage);

        return $this;
    }

    /**
     * Applies the colorize filter.
     *
     * @param  string|array  $color The filter color.
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function colorize(string|array $color): static
    {
        $color = self::normalizeColor($color);

        imagefilter(
            $this->image,
            IMG_FILTER_COLORIZE,
            $color['red'],
            $color['green'],
            $color['blue'],
            127 - ($color['alpha'] * 127)
        );

        return $this;
    }

    /**
     * Applies the contrast filter.
     *
     * @param  int  $percentage Percentage to adjust (-100 - 100).
     * @return SimpleImage
     */
    public function contrast(int $percentage): static
    {
        imagefilter($this->image, IMG_FILTER_CONTRAST, self::keepWithin($percentage, -100, 100));

        return $this;
    }

    /**
     * Applies the brightness filter to darken the image.
     *
     * @param  int  $percentage Percentage to darken the image (0 - 100).
     * @return SimpleImage
     */
    public function darken(int $percentage): static
    {
        $percentage = self::keepWithin(255 * $percentage / 100, 0, 255);

        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, -$percentage);

        return $this;
    }

    /**
     * Applies the desaturate (grayscale) filter.
     *
     * @return SimpleImage
     */
    public function desaturate(): static
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);

        return $this;
    }

    /**
     * Applies the edge detect filter.
     *
     * @return SimpleImage
     */
    public function edgeDetect(): static
    {
        imagefilter($this->image, IMG_FILTER_EDGEDETECT);

        return $this;
    }

    /**
     * Applies the emboss filter.
     *
     * @return SimpleImage
     */
    public function emboss(): static
    {
        imagefilter($this->image, IMG_FILTER_EMBOSS);

        return $this;
    }

    /**
     * Inverts the image's colors.
     *
     * @return SimpleImage
     */
    public function invert(): static
    {
        imagefilter($this->image, IMG_FILTER_NEGATE);

        return $this;
    }

    /**
     * Changes the image's opacity level.
     *
     * @param  float  $opacity The desired opacity level (0 - 1).
     * @return SimpleImage
     *
     * @throws Exception
     */
    public function opacity(float $opacity): static
    {
        // Create a transparent image
        $newImage = new SimpleImage();
        $newImage->fromNew($this->getWidth(), $this->getHeight());

        // Copy the current image (with opacity) onto the transparent image
        self::imageCopyMergeAlpha(
            $newImage->image,
            $this->image,
            0,
            0,
            0,
            0,
            $this->getWidth(),
            $this->getHeight(),
            (int) round(self::keepWithin($opacity, 0, 1) * 100)
        );

        return $this;
    }

    /**
     * Applies the pixelate filter.
     *
     * @param  int  $size The size of the blocks in pixels (default 10).
     * @return SimpleImage
     */
    public function pixelate(int $size = 10): static
    {
        imagefilter($this->image, IMG_FILTER_PIXELATE, $size, true);

        return $this;
    }

    /**
     * Simulates a sepia effect by desaturating the image and applying a sepia tone.
     *
     * @return SimpleImage
     */
    public function sepia(): static
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        imagefilter($this->image, IMG_FILTER_COLORIZE, 70, 35, 0);

        return $this;
    }

    /**
     * Sharpens the image.
     *
     * @param  int  $amount Sharpening amount (default 50).
     * @return SimpleImage
     */
    public function sharpen(int $amount = 50): static
    {
        // Normalize amount
        $amount = max(1, min(100, $amount)) / 100;

        $sharpen = [
            [-1, -1, -1],
            [-1,  8 / $amount, -1],
            [-1, -1, -1],
        ];
        $divisor = array_sum(array_map('array_sum', $sharpen));

        imageconvolution($this->image, $sharpen, $divisor, 0);

        return $this;
    }

    /**
     * Applies the mean remove filter to produce a sketch effect.
     *
     * @return SimpleImage
     */
    public function sketch(): static
    {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);

        return $this;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Color utilities
    //////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Converts a "friendly color" into a color identifier for use with GD's image functions.
     *
     * @param  string|array  $color The color to allocate.
     *
     * @throws Exception
     */
    protected function allocateColor(string|array $color): int
    {
        $color = self::normalizeColor($color);

        // Was this color already allocated?
        $index = imagecolorexactalpha(
            $this->image,
            $color['red'],
            $color['green'],
            $color['blue'],
            (int) (127 - ($color['alpha'] * 127))
        );
        if ($index > -1) {
            // Yes, return this color index
            return $index;
        }

        // Allocate a new color index
        return imagecolorallocatealpha(
            $this->image,
            $color['red'],
            $color['green'],
            $color['blue'],
            127 - ($color['alpha'] * 127)
        );
    }

    /**
     * Adjusts a color by increasing/decreasing red/green/blue/alpha values independently.
     *
     * @param  string|array  $color The color to adjust.
     * @param  int  $red Red adjustment (-255 - 255).
     * @param  int  $green Green adjustment (-255 - 255).
     * @param  int  $blue Blue adjustment (-255 - 255).
     * @param  int  $alpha Alpha adjustment (-1 - 1).
     * @return int[] An RGBA color array.
     *
     * @throws Exception
     */
    public static function adjustColor(string|array $color, int $red, int $green, int $blue, int $alpha): array
    {
        // Normalize to RGBA
        $color = self::normalizeColor($color);

        // Adjust each channel
        return self::normalizeColor([
            'red' => $color['red'] + $red,
            'green' => $color['green'] + $green,
            'blue' => $color['blue'] + $blue,
            'alpha' => $color['alpha'] + $alpha,
        ]);
    }

    /**
     * Darkens a color.
     *
     * @param  string|array  $color The color to darken.
     * @param  int  $amount Amount to darken (0 - 255).
     * @return int[] An RGBA color array.
     *
     * @throws Exception
     */
    public static function darkenColor(string|array $color, int $amount): array
    {
        return self::adjustColor($color, -$amount, -$amount, -$amount, 0);
    }

    /**
     * Extracts colors from an image like a human would do.™ This method requires the third-party
     * library \League\ColorExtractor. If you're using Composer, it will be installed for you
     * automatically.
     *
     * @param  int  $count The max number of colors to extract (default 5).
     * @param  string|array|null  $backgroundColor
     *    By default any pixel with alpha value greater than zero will
     *    be discarded. This is because transparent colors are not perceived as is. For example, fully
     *    transparent black would be seen white on a white background. So if you want to take
     *    transparency into account, you have to specify a default background color.
     * @return int[] An array of RGBA colors arrays.
     *
     * @throws Exception Thrown if library \League\ColorExtractor is missing.
     */
    public function extractColors(int $count = 5, string|array $backgroundColor = null): array
    {
        // Check for required library
        if (! class_exists('\\' . ColorExtractor::class)) {
            throw new Exception(
                'Required library \League\ColorExtractor is missing.',
                self::ERR_LIB_NOT_LOADED
            );
        }

        // Convert background color to an integer value
        if ($backgroundColor) {
            $backgroundColor = self::normalizeColor($backgroundColor);
            $backgroundColor = Color::fromRgbToInt([
                'r' => $backgroundColor['red'],
                'g' => $backgroundColor['green'],
                'b' => $backgroundColor['blue'],
            ]);
        }

        // Extract colors from the image
        $palette = Palette::fromGD($this->image, $backgroundColor);
        $extractor = new ColorExtractor($palette);
        $colors = $extractor->extract($count);

        // Convert colors to an RGBA color array
        foreach ($colors as $key => $value) {
            $colors[$key] = self::normalizeColor(Color::fromIntToHex($value));
        }

        return $colors;
    }

    /**
     * Gets the RGBA value of a single pixel.
     *
     * @param  int  $x The horizontal position of the pixel.
     * @param  int  $y The vertical position of the pixel.
     * @return bool|int[] An RGBA color array or false if the x/y position is off the canvas.
     */
    public function getColorAt(int $x, int $y): array|bool
    {
        // Coordinates must be on the canvas
        if ($x < 0 || $x > $this->getWidth() || $y < 0 || $y > $this->getHeight()) {
            return false;
        }

        // Get the color of this pixel and convert it to RGBA
        $color = imagecolorat($this->image, $x, $y);
        $rgba = imagecolorsforindex($this->image, $color);
        $rgba['alpha'] = 127 - ($color >> 24) & 0xFF;

        return $rgba;
    }

    /**
     * Lightens a color.
     *
     * @param  string|array  $color The color to lighten.
     * @param  int  $amount Amount to lighten (0 - 255).
     * @return int[] An RGBA color array.
     *
     * @throws Exception
     */
    public static function lightenColor(string|array $color, int $amount): array
    {
        return self::adjustColor($color, $amount, $amount, $amount, 0);
    }

    /**
     * Normalizes a hex or array color value to a well-formatted RGBA array.
     *
     * @param  string|array  $color
     *    A CSS color name, hex string, or an array [red, green, blue, alpha].
     *    You can pipe alpha transparency through hex strings and color names. For example:
     *        #fff|0.50 <-- 50% white
     *        red|0.25 <-- 25% red
     * @return array [red, green, blue, alpha].
     *
     * @throws Exception Thrown if color value is invalid.
     */
    public static function normalizeColor(string|array $color): array
    {
        // 140 CSS color names and hex values
        $cssColors = [
            'aliceblue' => '#f0f8ff',
            'antiquewhite' => '#faebd7',
            'aqua' => '#00ffff',
            'aquamarine' => '#7fffd4',
            'azure' => '#f0ffff',
            'beige' => '#f5f5dc',
            'bisque' => '#ffe4c4',
            'black' => '#000000',
            'blanchedalmond' => '#ffebcd',
            'blue' => '#0000ff',
            'blueviolet' => '#8a2be2',
            'brown' => '#a52a2a',
            'burlywood' => '#deb887',
            'cadetblue' => '#5f9ea0',
            'chartreuse' => '#7fff00',
            'chocolate' => '#d2691e',
            'coral' => '#ff7f50',
            'cornflowerblue' => '#6495ed',
            'cornsilk' => '#fff8dc',
            'crimson' => '#dc143c',
            'cyan' => '#00ffff',
            'darkblue' => '#00008b',
            'darkcyan' => '#008b8b',
            'darkgoldenrod' => '#b8860b',
            'darkgray' => '#a9a9a9',
            'darkgrey' => '#a9a9a9',
            'darkgreen' => '#006400',
            'darkkhaki' => '#bdb76b',
            'darkmagenta' => '#8b008b',
            'darkolivegreen' => '#556b2f',
            'darkorange' => '#ff8c00',
            'darkorchid' => '#9932cc',
            'darkred' => '#8b0000',
            'darksalmon' => '#e9967a',
            'darkseagreen' => '#8fbc8f',
            'darkslateblue' => '#483d8b',
            'darkslategray' => '#2f4f4f',
            'darkslategrey' => '#2f4f4f',
            'darkturquoise' => '#00ced1',
            'darkviolet' => '#9400d3',
            'deeppink' => '#ff1493',
            'deepskyblue' => '#00bfff',
            'dimgray' => '#696969',
            'dimgrey' => '#696969',
            'dodgerblue' => '#1e90ff',
            'firebrick' => '#b22222',
            'floralwhite' => '#fffaf0',
            'forestgreen' => '#228b22',
            'fuchsia' => '#ff00ff',
            'gainsboro' => '#dcdcdc',
            'ghostwhite' => '#f8f8ff',
            'gold' => '#ffd700',
            'goldenrod' => '#daa520',
            'gray' => '#808080',
            'grey' => '#808080',
            'green' => '#008000',
            'greenyellow' => '#adff2f',
            'honeydew' => '#f0fff0',
            'hotpink' => '#ff69b4',
            'indianred ' => '#cd5c5c',
            'indigo ' => '#4b0082',
            'ivory' => '#fffff0',
            'khaki' => '#f0e68c',
            'lavender' => '#e6e6fa',
            'lavenderblush' => '#fff0f5',
            'lawngreen' => '#7cfc00',
            'lemonchiffon' => '#fffacd',
            'lightblue' => '#add8e6',
            'lightcoral' => '#f08080',
            'lightcyan' => '#e0ffff',
            'lightgoldenrodyellow' => '#fafad2',
            'lightgray' => '#d3d3d3',
            'lightgrey' => '#d3d3d3',
            'lightgreen' => '#90ee90',
            'lightpink' => '#ffb6c1',
            'lightsalmon' => '#ffa07a',
            'lightseagreen' => '#20b2aa',
            'lightskyblue' => '#87cefa',
            'lightslategray' => '#778899',
            'lightslategrey' => '#778899',
            'lightsteelblue' => '#b0c4de',
            'lightyellow' => '#ffffe0',
            'lime' => '#00ff00',
            'limegreen' => '#32cd32',
            'linen' => '#faf0e6',
            'magenta' => '#ff00ff',
            'maroon' => '#800000',
            'mediumaquamarine' => '#66cdaa',
            'mediumblue' => '#0000cd',
            'mediumorchid' => '#ba55d3',
            'mediumpurple' => '#9370db',
            'mediumseagreen' => '#3cb371',
            'mediumslateblue' => '#7b68ee',
            'mediumspringgreen' => '#00fa9a',
            'mediumturquoise' => '#48d1cc',
            'mediumvioletred' => '#c71585',
            'midnightblue' => '#191970',
            'mintcream' => '#f5fffa',
            'mistyrose' => '#ffe4e1',
            'moccasin' => '#ffe4b5',
            'navajowhite' => '#ffdead',
            'navy' => '#000080',
            'oldlace' => '#fdf5e6',
            'olive' => '#808000',
            'olivedrab' => '#6b8e23',
            'orange' => '#ffa500',
            'orangered' => '#ff4500',
            'orchid' => '#da70d6',
            'palegoldenrod' => '#eee8aa',
            'palegreen' => '#98fb98',
            'paleturquoise' => '#afeeee',
            'palevioletred' => '#db7093',
            'papayawhip' => '#ffefd5',
            'peachpuff' => '#ffdab9',
            'peru' => '#cd853f',
            'pink' => '#ffc0cb',
            'plum' => '#dda0dd',
            'powderblue' => '#b0e0e6',
            'purple' => '#800080',
            'rebeccapurple' => '#663399',
            'red' => '#ff0000',
            'rosybrown' => '#bc8f8f',
            'royalblue' => '#4169e1',
            'saddlebrown' => '#8b4513',
            'salmon' => '#fa8072',
            'sandybrown' => '#f4a460',
            'seagreen' => '#2e8b57',
            'seashell' => '#fff5ee',
            'sienna' => '#a0522d',
            'silver' => '#c0c0c0',
            'skyblue' => '#87ceeb',
            'slateblue' => '#6a5acd',
            'slategray' => '#708090',
            'slategrey' => '#708090',
            'snow' => '#fffafa',
            'springgreen' => '#00ff7f',
            'steelblue' => '#4682b4',
            'tan' => '#d2b48c',
            'teal' => '#008080',
            'thistle' => '#d8bfd8',
            'tomato' => '#ff6347',
            'turquoise' => '#40e0d0',
            'violet' => '#ee82ee',
            'wheat' => '#f5deb3',
            'white' => '#ffffff',
            'whitesmoke' => '#f5f5f5',
            'yellow' => '#ffff00',
            'yellowgreen' => '#9acd32',
        ];

        // Parse alpha from '#fff|.5' and 'white|.5'
        if (is_string($color) && strstr($color, '|')) {
            $color = explode('|', $color);
            $alpha = (float) $color[1];
            $color = trim($color[0]);
        } else {
            $alpha = 1;
        }

        // Translate CSS color names to hex values
        if (is_string($color) && array_key_exists(strtolower($color), $cssColors)) {
            $color = $cssColors[strtolower($color)];
        }

        // Translate transparent keyword to a transparent color
        if ($color === 'transparent') {
            $color = ['red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0];
        }

        // Convert hex values to RGBA
        if (is_string($color)) {
            // Remove #
            $hex = strval(preg_replace('/^#/', '', $color));

            // Support short and standard hex codes
            if (strlen($hex) === 3 || strlen($hex) === 4) {
                [$red, $green, $blue] = [
                    $hex[0] . $hex[0],
                    $hex[1] . $hex[1],
                    $hex[2] . $hex[2],
                ];
                if (strlen($hex) === 4) {
                    $alpha = hexdec($hex[3]) / 255;
                }
            } elseif (strlen($hex) === 6 || strlen($hex) === 8) {
                [$red, $green, $blue] = [
                    $hex[0] . $hex[1],
                    $hex[2] . $hex[3],
                    $hex[4] . $hex[5],
                ];
                if (strlen($hex) === 8) {
                    $alpha = hexdec($hex[6] . $hex[7]) / 255;
                }
            } else {
                throw new Exception("Invalid color value: $color", self::ERR_INVALID_COLOR);
            }

            // Turn color into an array
            $color = [
                'red' => hexdec($red),
                'green' => hexdec($green),
                'blue' => hexdec($blue),
                'alpha' => $alpha,
            ];
        }

        // Enforce color value ranges
        if (is_array($color)) {
            // RGB default to 0
            $color['red'] ??= 0;
            $color['green'] ??= 0;
            $color['blue'] ??= 0;

            // Alpha defaults to 1
            $color['alpha'] ??= 1;

            return [
                'red' => (int) self::keepWithin((int) $color['red'], 0, 255),
                'green' => (int) self::keepWithin((int) $color['green'], 0, 255),
                'blue' => (int) self::keepWithin((int) $color['blue'], 0, 255),
                'alpha' => self::keepWithin($color['alpha'], 0, 1),
            ];
        }

        throw new Exception("Invalid color value: $color", self::ERR_INVALID_COLOR);
    }
}
