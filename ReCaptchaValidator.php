<?php

namespace recaptcha;

use Yii;
use yii\validators\Validator;

/**
 * @author Sergey Bogatyrev <sergey@bogatyrev.me>
 * @since 2.0
 */
class ReCaptchaValidator extends Validator
{
    public $enableClientValidation = false;
    /**
     * @var boolean whether to skip this validator if the input is empty.
     */
    public $skipOnEmpty = false;
    /**
     * @var bool whether or not send user IP address.
     */
    public $remoteIp;
    /**
     * @var \recaptcha\ReCaptchaComponent|string $_component
     */
    public $_component = 'recaptcha';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $component = $this->getComponent();
        if ($this->remoteIp === null) {
            $this->remoteIp = $component->remoteIp;
        }
    }

    /**
     * Set component property.
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->_component = Yii::$app->get($component);
    }

    /**
     * Get ReCaptchaComponent opject.
     * @return \recaptcha\ReCaptchaComponent|null|object|string
     */
    public function getComponent() {
        if (!is_object($this->_component)) {
            return $this->_component = Yii::$app->get($this->_component);
        }
        return $this->_component;
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $component = $this->getComponent();
        $ip = $this->remoteIp ? Yii::$app->request->userIP : null;
        return $component->verify($value, $ip) ? null : [$this->message, []];
    }
}
