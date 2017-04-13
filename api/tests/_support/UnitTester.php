<?php
namespace api\tests;

use common\components\helpers\CFileHelper;
use common\components\web\CUploadedFile;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

   /**
    * Define custom actions here
    */

    /**
     * Helper to create CUploadedFile instance from file path.
     * @param  string $path
     * @return CUploadedFile
     */
    public function getUploadedFileInstance($path)
    {
        return new CUploadedFile([
            'name'     => basename($path),
            'tempName' => $path,
            'type'     => CFileHelper::getMimeType($path),
            'size'     => filesize($path),
            'error'    => UPLOAD_ERR_OK,
        ]);
    }
}
