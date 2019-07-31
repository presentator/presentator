<?php
namespace presentator\api\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\db\Connection;
use yii\db\Query;
use presentator\api\models\User;
use presentator\api\models\UserAuth;
use presentator\api\models\UserSetting;
use presentator\api\models\Project;
use presentator\api\models\ProjectLink;
use presentator\api\models\UserProjectRel;
use presentator\api\models\Prototype;
use presentator\api\models\Screen;
use presentator\api\models\Hotspot;
use presentator\api\models\ScreenComment;
use presentator\api\models\UserScreenCommentRel;

/**
 * Manages Presentator v1 data import.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class V1ImportController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $color = true;

    /**
     * Interactive command to import Presentator v1 data into configured Presentator v2 application.
     *
     * Example usage:
     * ```bash
     * php yii v1-import/index
     * ```
     *
     * @return integer
     */
    public function actionIndex()
    {
        $this->stdout('Presentator v1 to v2 data import', Console::BG_CYAN);
        $this->stdout(PHP_EOL);
        $this->stdout(PHP_EOL);

        $uploadsDir = $this->prompt('> Presentator v1 uploads dir (supports Yii aliases):', [
            'required' => true,
            'validator' => function($input, &$error) {
                $usersDir   = FileHelper::normalizePath($input . '/users');
                $screensDir = FileHelper::normalizePath($input . '/projects');

                if (!is_dir($usersDir) || !is_dir($screensDir)) {
                    $error = 'Invalid uploads directory - must contains users and projects sub directories!';

                    return false;
                }

                return true;
            },
        ]);

        $oldDsn = $this->prompt('> Presentator v1 DB dsn string:', [
            'default' => 'mysql:host=db;dbname=presentator_test',
        ]);

        $oldUsername = $this->prompt('> Presentator v1 DB username:', [
            'default' => Yii::$app->db->username,
        ]);

        $oldPassword = $this->prompt('> Presentator v1 DB password:', [
            'default' => '__default_to_v2_db_password__',
        ]);
        if ($oldPassword === '__default_to_v2_db_password__') {
            $oldPassword = Yii::$app->db->password;
        }

        $oldDb = new Connection([
            'dsn'      => $oldDsn,
            'username' => $oldUsername,
            'password' => $oldPassword,
            'charset'  => 'utf8',
        ]);

        // test db connection
        try {
            $oldDb->open();
        } catch (\Exception | \Throwable $e) {
        }

        $this->stdout(PHP_EOL);
        if ($oldDb->isActive) {
            $this->stdout('Presentator v1 DB connection established.', Console::FG_GREEN);

            $oldDb->close();
        } else {
            $this->stdout('Unable to connect to the Presentator v1 database.', Console::BG_RED);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_ERROR;
        }

        $this->stdout(PHP_EOL . PHP_EOL);
        $this->stdout('Warning! This command will truncate all Presentator v2 existing tables and import the data from the connected Presentator v1 database.', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        if (!$this->confirm('Do you still want to continue?')) {
            return self::EXIT_CODE_NORMAL;
        }
        $this->stdout(PHP_EOL);

        // truncate data
        $this->truncateAppTables();

        // migrate v1 tables
        $this->migrateUserTable($oldDb);
        $this->migrateUserAuthTable($oldDb);
        $this->migrateUserSettingTable($oldDb);
        $this->migrateProjectTable($oldDb);
        $this->migrateProjectPreviewTable($oldDb);
        $this->migrateUserProjectRelTable($oldDb);
        $this->migrateVersionTable($oldDb);
        $this->migrateScreenTable($oldDb);
        $this->migrateScreenCommentTable($oldDb);
        $this->migrateUserScreenCommentRel($oldDb);

        // migrate v1 uploads files
        $this->migrateUserAvatars($uploadsDir . '/users');
        $this->migrateScreenFiles($uploadsDir . '/projects');

        // post import operations
        $this->resendRecentActivationEmails();

        $this->stdout(PHP_EOL);
        $this->stdout('Import completed successfully!', Console::BG_GREEN);
        $this->stdout(PHP_EOL);

        return self::EXIT_CODE_NORMAL;
    }

    /* Helpers
    --------------------------------------------------------------- */

    /**
     * @param Connection $oldDb
     */
    protected function migrateUserTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('user')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'                 => $row['id'],
                'email'              => $row['email'],
                'firstName'          => $row['firstName'],
                'lastName'           => $row['lastName'],
                'passwordHash'       => $row['passwordHash'],
                'passwordResetToken' => null,
                'authKey'            => Yii::$app->security->generateRandomString(),
                'type'               => $row['type'] == 0 ? User::TYPE['REGULAR'] : User::TYPE['SUPER'],
                'status'             => $row['status'] == 0 ? User::STATUS['INACTIVE'] : User::STATUS['ACTIVE'],
                'createdAt'          => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt'          => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(User::TableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateUserAuthTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('userAuth')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'        => $row['id'],
                'userId'    => $row['userId'],
                'source'    => $row['source'],
                'sourceId'  => $row['sourceId'],
                'createdAt' => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt' => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(UserAuth::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateUserSettingTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('userSetting')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'        => $row['id'],
                'userId'    => $row['userId'],
                'type'      => UserSetting::TYPE['BOOLEAN'],
                'name'      => $row['settingName'] === 'mentions' ? UserSetting::NOTIFY_ON_MENTION : UserSetting::NOTIFY_ON_EACH_COMMENT,
                'value'     => ($row['settingValue'] === true || $row['settingValue'] === 'true' || $row['settingValue'] === 1) ? '1' : '0',
                'createdAt' => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt' => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(UserSetting::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateProjectTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('project')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'        => $row['id'],
                'title'     => $row['title'] ?: ('Project ' . $row['id']),
                'archived'  => 0,
                'createdAt' => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt' => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(Project::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateProjectPreviewTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('projectPreview')->each(100, $oldDb) as $row) {
            $passwordHash = (new Query)->select('passwordHash')->from('project')->where(['id' => $row['projectId']])->scalar($oldDb);

            $data[] = [
                'id'             => $row['id'],
                'projectId'      => $row['projectId'],
                'slug'           => $row['slug'],
                'allowComments'  => $row['type'] == 2 ? 1 : 0,
                'allowGuideline' => 1,
                'passwordHash'   => $passwordHash ?: null,
                'createdAt'      => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt'      => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(ProjectLink::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateUserProjectRelTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('userProjectRel')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'        => $row['id'],
                'userId'    => $row['userId'],
                'projectId' => $row['projectId'],
                'createdAt' => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt' => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(UserProjectRel::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateVersionTable(Connection $oldDb)
    {
        $subtypes = [
            // Tablet
            21 => [768, 1024],
            22 => [1024, 768],
            23 => [800, 1200],
            24 => [1200, 800],
            // Mobile
            31 => [320, 480],
            32 => [480, 320],
            33 => [375, 667],
            34 => [667, 375],
            35 => [412, 732],
            36 => [732, 712],
        ];

        $data = [];
        foreach ((new Query)->from('version')->each(100, $oldDb) as $row) {
            if ($row['scaleFactor'] == 2) {
                $scaleFactor = 0.5;
            } elseif ($row['scaleFactor'] == 0) {
                $scaleFactor = 0;
            } else {
                $scaleFactor = 1;
            }

            $width  = 0.0;
            $height = 0.0;
            if ($row['type'] != 1 && isset($subtypes[(int) $row['subtype']])) {
                $width  = (float) $subtypes[(int) $row['subtype']][0];
                $height = (float) $subtypes[(int) $row['subtype']][1];
            }

            $data[] = [
                'id'          => $row['id'],
                'projectId'   => $row['projectId'],
                'title'       => $row['title'] ?: ('Prototype ' . $row['order']),
                'type'        => $row['type'] == 1 ? Prototype::TYPE['DESKTOP'] : Prototype::TYPE['MOBILE'],
                'width'       => $width,
                'height'      => $height,
                'scaleFactor' => $scaleFactor,
                'createdAt'   => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt'   => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(Prototype::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateScreenTable(Connection $oldDb)
    {
        $screensData  = [];
        $hotspotsData = [];

        $transitionsMapping = [
            'none'         => Hotspot::TRANSITION['NONE'],
            'fade'         => Hotspot::TRANSITION['FADE'],
            'slide-left'   => Hotspot::TRANSITION['SLIDE_LEFT'],
            'slide-right'  => Hotspot::TRANSITION['SLIDE_RIGHT'],
            'slide-top'    => Hotspot::TRANSITION['SLIDE_TOP'],
            'slide-bottom' => Hotspot::TRANSITION['SLIDE_BOTTOM'],
        ];

        foreach ((new Query)->from('screen')->each(100, $oldDb) as $row) {
            if ($row['alignment'] == 1) {
                $alignment = Screen::ALIGNMENT['LEFT'];
            } elseif ($row['alignment'] == 3) {
                $alignment = Screen::ALIGNMENT['RIGHT'];
            } else {
                $alignment = Screen::ALIGNMENT['CENTER'];
            }

            $screensData[] = [
                'id'          => $row['id'],
                'prototypeId' => $row['versionId'],
                'order'       => $row['order'],
                'title'       => $row['title'] ?: ('Screen ' . $row['order']),
                'alignment'   => $alignment,
                'background'  => $row['background'] ?: '#ffffff',
                'fixedHeader' => 0.0,
                'fixedFooter' => 0.0,
                'filePath'    => $row['imageUrl'], // will be changed by `self::migrateScreenFiles()`
                'createdAt'   => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt'   => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];

            $hotspots = $row['hotspots'] ? ((array) @json_decode($row['hotspots'], true)) : [];

            foreach ($hotspots as $hotspot) {
                if (empty($hotspot['link'])) {
                    continue; // skip hotspot
                }

                if (is_numeric($hotspot['link'])) {
                    $targetScreen = (new Query)->from('screen')->where(['id' => $hotspot['link']])->one($oldDb);
                    if (!$targetScreen) {
                        continue; // skip hotspot
                    }
                } else {
                    $targetScreen = null;
                }

                if ($targetScreen) {
                    $settings = [
                        Hotspot::SETTING['SCREEN']     => $targetScreen['id'],
                        Hotspot::SETTING['TRANSITION'] => (
                            isset($hotspot['transition']) && isset($transitionsMapping[$hotspot['transition']])
                                ? $transitionsMapping[$hotspot['transition']] : Hotspot::TRANSITION['NONE']
                        ),
                    ];
                } else {
                    $settings = [
                        Hotspot::SETTING['URL'] => $hotspot['link'],
                    ];
                }

                $hotspotsData[] = [
                    'screenId'          => $row['id'],
                    'hotspotTemplateId' => null,
                    'type'              => $targetScreen ? Hotspot::TYPE['SCREEN'] : Hotspot::TYPE['URL'],
                    'left'              => (float) ($hotspot['left'] ?? 0),
                    'top'               => (float) ($hotspot['top'] ?? 0),
                    'width'             => (float) ($hotspot['width'] ?? 0),
                    'height'            => (float) ($hotspot['height'] ?? 0),
                    'settings'          => json_encode($settings),
                    'createdAt'         => date('Y-m-d H:i:s'),
                    'updatedAt'         => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->batchInsert(Screen::tableName(), $screensData);
        $this->batchInsert(Hotspot::tableName(), $hotspotsData);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateScreenCommentTable(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('screenComment')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'       => $row['id'],
                'replyTo'  => $row['replyTo'] ?: null,
                'screenId' => $row['screenId'],
                'from'     => $row['from'],
                'message'  => $row['message'],
                'left'     => (float) $row['posX'],
                'top'      => (float) $row['posY'],
                'status'   => $row['status'] == 1 ? ScreenComment::STATUS['RESOLVED'] : ScreenComment::STATUS['PENDING'],
                'createdAt' => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt' => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(ScreenComment::tableName(), $data);
    }

    /**
     * @param Connection $oldDb
     */
    protected function migrateUserScreenCommentRel(Connection $oldDb)
    {
        $data = [];
        foreach ((new Query)->from('userScreenCommentRel')->each(100, $oldDb) as $row) {
            $data[] = [
                'id'              => $row['id'],
                'userId'          => $row['userId'],
                'screenCommentId' => $row['screenCommentId'],
                'isRead'          => $row['isRead'] ? 1 : 0,
                'isProcessed'     => 1,
                'createdAt'       => date('Y-m-d H:i:s', (int) $row['createdAt']),
                'updatedAt'       => date('Y-m-d H:i:s', (int) $row['updatedAt']),
            ];
        }

        $this->batchInsert(UserScreenCommentRel::tableName(), $data);
    }

    /**
     * @param string $userDir
     */
    protected function migrateUserAvatars(string $usersDir)
    {
        $usersDir = Yii::getAlias($usersDir);
        if (!file_exists($usersDir) || !is_dir($usersDir)) {
            return;
        }

        $this->stdout('Importing user avatars...' , Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        foreach (User::find()->each() as $user) {
            $oldAvatarPath = FileHelper::normalizePath($usersDir . '/' . md5($user->id) . '/avatar.jpg');
            if (!file_exists($oldAvatarPath)) {
                continue;
            }

            $newAvatarPath = $user->resolveFilePathPrefix() . '/' . basename($oldAvatarPath);

            // store in the file system
            $stream = fopen($oldAvatarPath, 'r+');
            $result = Yii::$app->fs->putStream($newAvatarPath, $stream);
            fclose($stream);

            $user->avatarFilePath = $newAvatarPath;
            $user->save();
        }
    }

    /**
     * @param string $screensDir
     */
    protected function migrateScreenFiles(string $screensDir)
    {
        $screensDir = Yii::getAlias($screensDir);
        if (!file_exists($screensDir) || !is_dir($screensDir)) {
            return;
        }

        $this->stdout('Importing screen files...' , Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        foreach (Screen::find()->with('prototype')->each() as $screen) {
            $oldScreenPath = FileHelper::normalizePath($screensDir . '/' . md5($screen->prototype->projectId) . '/' . basename($screen->filePath));
            if (!file_exists($oldScreenPath)) {
                continue;
            }

            $newScreenPath = $screen->resolveFilePathPrefix() . '/' . basename($oldScreenPath);

            // store in the file system
            $stream = fopen($oldScreenPath, 'r+');
            $result = Yii::$app->fs->putStream($newScreenPath, $stream);
            fclose($stream);

            $screen->filePath = $newScreenPath;
            $screen->save();
        }
    }

    /**
     * Truncates tables from the configured app db component.
     */
    protected function truncateAppTables()
    {
        $this->stdout('Truncating tables...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        Yii::$app->db->createCommand('
            SET FOREIGN_KEY_CHECKS = 0;
            TRUNCATE TABLE `User`;
            TRUNCATE TABLE `UserSetting`;
            TRUNCATE TABLE `UserAuth`;
            TRUNCATE TABLE `Project`;
            TRUNCATE TABLE `UserProjectRel`;
            TRUNCATE TABLE `Prototype`;
            TRUNCATE TABLE `ProjectLink`;
            TRUNCATE TABLE `ProjectLinkPrototypeRel`;
            TRUNCATE TABLE `Screen`;
            TRUNCATE TABLE `ScreenComment`;
            TRUNCATE TABLE `UserScreenCommentRel`;
            TRUNCATE TABLE `HotspotTemplate`;
            TRUNCATE TABLE `HotspotTemplateScreenRel`;
            TRUNCATE TABLE `Hotspot`;
            TRUNCATE TABLE `GuidelineSection`;
            TRUNCATE TABLE `GuidelineAsset`;
            SET FOREIGN_KEY_CHECKS = 1;
        ')->execute();
    }

    /**
     * Batch inserts `$data` into `$table`.
     *
     * @param string $table Name of the table to insert data into.
     * @param array  $data  Array list of data to insert into data.
     *                      Each item of the data need to be key-ed with the exact column name.
     */
    protected function batchInsert(string $table, array $data)
    {
        $this->stdout('Inserting ' . count($data) . ' items in ' . $table . ' table...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        if (!empty($data)) {
            $firstEntry = reset($data);

            Yii::$app->db->createCommand()
                ->batchInsert($table, array_keys($firstEntry), $data)
                ->execute();
        }
    }

    /**
     * Resends activation emails to the recent inactive users.
     *
     * @param string [$fromTime] `strtotime` suitable datatime string.
     */
    protected function resendRecentActivationEmails(string $fromTime = '-3 weeks')
    {
        $users = User::find()
            ->where(['status' => User::STATUS['INACTIVE']])
            ->andWhere(['>=', 'createdAt', date('Y-m-d H:i:s', strtotime($fromTime))])
            ->all();

        $this->stdout('Resending ' . count($users) . ' recent activation emails...' , Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        foreach ($users as $user) {
            $user->sendActivationEmail();
        }
    }
}
