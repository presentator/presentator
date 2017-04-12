<?php
namespace common\components\web;

use yii\web\UploadedFile;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CUploadedFile extends UploadedFile
{
    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        if (YII_ENV === 'test') {
            if ($this->error == UPLOAD_ERR_OK && is_file($this->tempName)) {
                return copy($this->tempName, $file);
            }

            return false;
        }

        return parent::saveAs($file, $deleteTempFile);
    }
}
