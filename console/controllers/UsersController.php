<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use common\models\User;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersController extends Controller
{
    /**
     * @inheritdoc
     */
    public $color = true;

    /**
     * Sets Super User access rights to a single User model.
     *
     * @example
     * ```bash
     * php yii users/super test@presentator.io
     * ```
     *
     * @param  string $email Registered user email address.
     * @return integer
     */
    public function actionSuper($email)
    {
        $user = User::findOne(['email'  => $email]);

        if (!$user) {
            $this->stdout('User with email ' . $email . ' doesn\'t exist.', Console::BG_RED);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_ERROR;
        }

        if ($user->type == User::TYPE_SUPER) {
            $this->stdout('User ' . $user->email . ' has already Super User access rights.', Console::FG_YELLOW);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_NORMAL;
        }

        $user->type = User::TYPE_SUPER;
        if ($user->save()) {
            $this->stdout('Successfully provided Super User access rights to ' . $user->email . '.', Console::FG_GREEN);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_NORMAL;
        }

        $this->stdout('Unable to provide Super User access rights to ' . $user->email . '.', Console::BG_RED);
        $this->stdout(PHP_EOL);

        return self::EXIT_CODE_ERROR;
    }

    /**
     * Sets Regular User access rights to a single User model.
     *
     * @example
     * ```bash
     * php yii users/regular test@presentator.io
     * ```
     *
     * @param  string $email Registered user email address.
     * @return integer
     */
    public function actionRegular($email)
    {
        $user = User::findOne(['email'  => $email]);

        if (!$user) {
            $this->stdout('User with email ' . $email . ' doesn\'t exist.', Console::BG_RED);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_ERROR;
        }

        if ($user->type == User::TYPE_REGULAR) {
            $this->stdout('User ' . $user->email . ' has already Regular User access rights.', Console::FG_YELLOW);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_NORMAL;
        }

        $user->type = User::TYPE_REGULAR;
        if ($user->save()) {
            $this->stdout('Successfully provided Regular User access rights to ' . $user->email . '.', Console::FG_GREEN);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_NORMAL;
        }

        $this->stdout('Unable to provide Regular User access rights to ' . $user->email . '.', Console::BG_RED);
        $this->stdout(PHP_EOL);

        return self::EXIT_CODE_ERROR;
    }
}
