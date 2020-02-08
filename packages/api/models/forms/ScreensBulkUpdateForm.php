<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\validators\HexValidator;
use presentator\api\models\User;
use presentator\api\models\Screen;

/**
 * Bulk screens settings update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensBulkUpdateForm extends ApiForm
{
    /**
     * @var integer
     */
    public $prototypeId;

    /**
     * @var string
     */
    public $alignment;

    /**
     * @var string
     */
    public $background;

    /**
     * @var float
     */
    public $fixedHeader;

    /**
     * @var float
     */
    public $fixedFooter;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param User  $user
     * @param array [$config]
     */
    public function __construct(User $user, array $config = [])
    {
        $this->setUser($user);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['alignment']   = Yii::t('app', 'Alignment');
        $labels['background']  = Yii::t('app', 'Background');
        $labels['fixedHeader'] = Yii::t('app', 'Fixed header');
        $labels['fixedFooter'] = Yii::t('app', 'Fixed footer');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['prototypeId', 'required'];
        $rules[] = ['prototypeId', 'validateUserPrototypeId'];
        $rules[] = ['alignment', 'in', 'range' => array_values(Screen::ALIGNMENT)];
        $rules[] = ['background', HexValidator::class];
        $rules[] = [['fixedHeader', 'fixedFooter'], 'number', 'min' => 0];

        return $rules;
    }

    /**
     * Checks if the form user is the owner of the specified prototype ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserPrototypeId($attribute, $params)
    {
        $prototype = $this->getUser()->findPrototypeById((int) $this->{$attribute});

        if (!$prototype) {
            $this->addError($attribute, Yii::t('app', 'Invalid prototype ID.'));
        }
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Persists model and updates all prototype screens transactional.
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $prototype   = $this->getUser()->findPrototypeById((int) $this->prototypeId);
        $transaction = User::getDb()->beginTransaction();

        try {
            $result = true;

            foreach ($prototype->getScreens()->each() as $screen) {
                $screen->alignment   = $this->alignment ?: $screen->alignment;
                $screen->background  = $this->background ?: $screen->background;
                $screen->fixedHeader = $this->fixedHeader !== null ? (float) $this->fixedHeader : $screen->fixedHeader;
                $screen->fixedFooter = $this->fixedFooter !== null ? (float) $this->fixedFooter : $screen->fixedFooter;

                $result = $result && $screen->save();
            }

            if ($result) {
                $transaction->commit();

                return $result;
            }
        } catch (\Exception | \Throwable $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage());
        }

        return false;
    }
}
