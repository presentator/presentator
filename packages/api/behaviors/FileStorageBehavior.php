<?php
namespace presentator\api\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use Intervention\Image\ImageManager;

/**
 * Generic file and image thumbs storage behavior class.
 *
 * Example usage:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'thumbsBehavior' => [
 *             'class' => FileStorageBehavior::class,
 *             'thumbs' => [
 *                 'small'            => ['width' => 100, 'height' =>100, 'quality' => 90],
 *                 'mediumAutoHeight' => ['width' => 200, 'quality' => 85, 'smartResize' => false],
 *             ],
 *             'filePathAttribute' => 'avatarFilePath',
 *         ],
 *         ...
 *     ];
 * }
 * ```
 *
 * The following methods are exposed to the owner model:
 * @method string resolveFilePathPrefix()
 * @method bool   saveFile(UploadedFile $file)
 * @method bool   deleteFile(bool $updateOwner = true)
 * @method string getUrl()
 * @method bool   supportThumbs()
 * @method string getThumbPath(string $thumbKey)
 * @method string getThumbUrl(string $thumbKey, bool $createMissing = true)
 * @method int    createThumbs(array $thumbKeys = [])
 * @method int    deleteThumbs(array $thumbKeys = [])
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class FileStorageBehavior extends Behavior
{
    /**
     * The attribute name of the model's original image file path.
     *
     * @var string
     */
    public $filePathAttribute = 'filePath';

    /**
     * @var string|\Closure
     */
    public $filePathPrefix = '';

    /**
     * List with file extensions that support thumbs.
     *
     * @var array
     */
    public $thumbExtensions = ['png', 'jpg', 'jpeg'];

    /**
     * Array list with thumb sizes, where each list item must be in the following format:
     * `'my_key' => ['width' => int, 'height' => int, 'quality' => int(0-100), 'smartResize' => bool]`.
     *
     * `width`/`height` are optional (unless `smartResize` is enabled)
     * and will be auto calculated based on the thumb method used.
     *
     * When `smartResize` is enabled `Intervation\Image\Image::fit() is used,
     * otherwise - `Intervation\Image\Image::resize()`.
     * The `width` property is required when `smartResize` is set to `true` (default value).
     *
     * `quality` defines optionally the quality of the image.
     * `quality` is only applied for JPG. The default value is 90.
     *
     * Example values:
     * ```php
     * [
     *     'small'            => ['width' => 100, 'height' =>100, 'quality' => 100],
     *     'mediumAutoHeight' => ['width' => 400, 'smartResize' => false],
     * ]
     * ```
     *
     * @var array
     */
    public $thumbs = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = parent::events();

        $events[ActiveRecord::EVENT_AFTER_DELETE] = 'afterDelete';

        return $events;
    }

    /**
     * @param  yii\base\Event $event
     */
    public function afterDelete($event)
    {
        $this->deleteFile(false);
    }

    /**
     * Returns resolved model file storage path prefix.
     *
     * @return string
     */
    public function resolveFilePathPrefix(): string
    {
        if (
            $this->filePathPrefix instanceof \Closure ||
            (is_array($this->filePathPrefix) && is_callable($this->filePathPrefix))
        ) {
            return call_user_func($this->filePathPrefix, $this->owner);
        }

        return is_string($this->filePathPrefix) ? $this->filePathPrefix : '';
    }

    /**
     * Saves the provided file in the file system and updates models avatar file path property.
     * NB! This method doesn't perform file validations and expect that
     *     to be done in the appropriate form model.
     *
     * @param  UploadedFile $file
     * @return boolean
     */
    public function saveFile(UploadedFile $file): bool
    {
        $filePathPrefix = rtrim($this->resolveFilePathPrefix(), '/');

        // determine the name of the file
        $basename = substr(Inflector::slug($file->basename, '_'), 0, 100); // max 100 chars length
        $filename = $basename . '.' . $file->extension;
        $duplicateCounter = 2;
        while (Yii::$app->fs->has($filePathPrefix . '/' . $filename)) {
            $filename = $basename . '_' . ($duplicateCounter++) . '.' . $file->extension;
        }

        try {
            // store in the file system
            $stream = fopen($file->tempName, 'r+');
            $result = Yii::$app->fs->putStream($filePathPrefix . '/' . $filename, $stream);
            fclose($stream);

            // delete previous assigned file
            if ($result && $this->owner->{$this->filePathAttribute}) {
                $this->deleteFile(false);
            }

            // store new file
            $this->owner->{$this->filePathAttribute} = $filePathPrefix . '/' . $filename;

            return $result && $this->owner->save();
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Deletes model's file (and its thumbs) from the file system.
     *
     * @param  boolean [$updateOwner] Whether to reset the owner model's filePath property on success (`true` by default).
     * @return boolean
     */
    public function deleteFile(bool $updateOwner = true): bool
    {
        try {
            $filePath = $this->owner->{$this->filePathAttribute};

            if ($filePath && Yii::$app->fs->has($filePath)) {
                $this->deleteThumbs();

                Yii::$app->fs->delete($filePath);
            }

            if ($filePath && $updateOwner) {
                $this->owner->{$this->filePathAttribute} = '';

                return $this->owner->save();
            }

            return true;
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Generates and returns absolute file url based on model's `$filePathAttribute` value.
     *
     * @return string
     */
    public function getUrl(): string
    {
        $filePath = $this->owner->{$this->filePathAttribute};

        if ($filePath) {
            return rtrim(Yii::$app->params['baseStorageUrl'], '/') . '/' . ltrim($filePath, '/');
        }

        return '';
    }

    /* Thumbs related methods (only for image files)
    --------------------------------------------------------------- */
    /**
     * Checks whether the linked file supports thumbs.
     *
     * @return boolean
     */
    public function supportThumbs(): bool
    {
        try {
            $mimeType = Yii::$app->fs->getMimetype($this->owner->{$this->filePathAttribute});

            $extensions = FileHelper::getExtensionsByMimeType($mimeType);

            foreach ($extensions as $ext) {
                if (in_array($ext, $this->thumbExtensions)) {
                    return true;
                }
            }
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Returns thumb file path based on the original one and its thumb size key.
     * Note that this method doesn't check if the thumb actually exist or not.
     *
     * @param  string $thumbKey
     * @return string
     */
    public function getThumbPath(string $thumbKey): string
    {
        if (!isset($this->thumbs[$thumbKey]) || !$this->supportThumbs()) {
            return '';
        }

        $originalFilePathInfo = pathinfo($this->owner->{$this->filePathAttribute});

        return sprintf(
            '%s/%s_%s.%s',
            $originalFilePathInfo['dirname'],
            $originalFilePathInfo['filename'],
            md5(implode($this->thumbs[$thumbKey])),
            $originalFilePathInfo['extension']
        );
    }

    /**
     * Generates and returns absolute thumb url.
     * If thumb size with the specified key is not defined
     * (or the file does not support "thumbs"), the original file url is returned.
     *
     * @param  string  $thumbKey        Name of the thumb size to return url for.
     * @param  boolean [$createMissing] Whether to create the thumb file if missing (`true` by default).
     * @return string
     */
    public function getThumbUrl(string $thumbKey, bool $createMissing = true): string
    {
        $thumbPath = $this->getThumbPath($thumbKey);
        if (!$thumbPath) {
            return $this->getUrl(); // fallback to the original file url
        }

        if ($createMissing && !Yii::$app->fs->has($thumbPath)) {
            $this->createThumbs([$thumbKey]);
        }

        return rtrim(Yii::$app->params['baseStorageUrl'], '/') . '/' . ltrim($thumbPath, '/');
    }

    /**
     * Handles thumb files creation.
     *
     * @param  array [$thumbKeys] Keys of the thumb sizes to create (all by default).
     * @return int The total number of created thumbs.
     * @throws InvalidConfigException On incorrect thumb size configuration.
     */
    public function createThumbs(array $thumbKeys = []): int
    {
        $sucessCounter = 0;

        // is not image
        if (!$this->supportThumbs()) {
            return $sucessCounter;
        }

        $fileContent = null;
        try {
            $fileContent = Yii::$app->fs->read($this->owner->{$this->filePathAttribute});
        } catch (\Exception | \Throwable $e) {
        }

        // original file is missing or is not readable
        if (!$fileContent) {
            return $sucessCounter;
        }

        // create an image manager instance with available driver
        $imageManager = new ImageManager([
            'driver' => (extension_loaded('imagick') ? 'imagick' : 'gd'),
        ]);

        // normalize thumb keys
        $thumbKeys = $thumbKeys ?: array_keys($this->thumbs);

        foreach ($thumbKeys as $thumbKey) {
            $thumbFilePath = $this->getThumbPath($thumbKey);

            if (!isset($this->thumbs[$thumbKey]) || !$thumbFilePath) {
                continue;
            }

            try {
                $image = $imageManager->make($fileContent);
            } catch (\Exception | \Throwable $e) {
                Yii::error($e->getMessage() . '(most likely unable to create an image object due to memory limitations)');
                continue;
            }

            // size settings
            $width       = $this->thumbs[$thumbKey]['width'] ?? null;
            $height      = $this->thumbs[$thumbKey]['height'] ?? null;
            $quality     = $this->thumbs[$thumbKey]['quality'] ?? 90;
            $smartResize = $this->thumbs[$thumbKey]['smartResize'] ?? true;

            // resize image
            if ($smartResize) {
                if (!$width) {
                    throw new InvalidConfigException($thumbKey . ': the width thumb property is required when smartResize is enabled.');
                }

                $image->fit($width, $height, null, 'top');
            } else {
                if (!$width && !$height) {
                    throw new InvalidConfigException($thumbKey . ': either width or height thumb property must be set.');
                }

                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            try {
                // store thumb
                Yii::$app->fs->put($thumbFilePath, $image->encode(null, $quality));

                $sucessCounter++;
            } catch (\Exception | \Throwable $e) {
                Yii::error($e->getMessage());
            }
        }

        return $sucessCounter;
    }

    /**
     * Handles thumb files deletion.
     *
     * @param  array [$thumbKeys] Keys of the thumb sizes to delete (all by default).
     * @return int The total number of deleted thumbs.
     */
    public function deleteThumbs(array $thumbKeys = []): int
    {
        if (!$this->supportThumbs()) {
            return 0;
        }

        $successCounter = 0;
        $thumbKeys      = $thumbKeys ?: array_keys($this->thumbs);

        foreach ($thumbKeys as $thumbKey) {
            $thumbFilePath = $this->getThumbPath($thumbKey);

            if ($thumbFilePath && Yii::$app->fs->has($thumbFilePath)) {
                try {
                    Yii::$app->fs->delete($thumbFilePath);

                    $successCounter++;
                } catch (\Exception | \Throwable $e) {
                    Yii::error($e->getMessage());
                }
            }
        }

        return $successCounter;
    }
}
