<?php

namespace recaptcha;

use recaptcha\ReCaptchaComponent;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;


/**
 * ReCaptcha generates a reCaptcha widget.
 *
 * To use ReCaptcha, you must set configure [[\recaptcha\ReCaptchaComponent]]. The following example
 * shows how to use it:
 *
 * ```php
 * 'recaptcha' => [
 *      'class' => 'recaptcha\ReCaptchaComponent',
 *      'siteKey' => 'site_key',
 *      'secretKey' => 'key_secret',
 * ],
 * ```
 *
 * You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'reCaptcha')->widget(ReCaptcha::className(), [
 *      'id' => 'sign-up-captcha',
 *      'render' => ReCaptcha::RENDER_EXPLICIT,
 * ])->label(false) ?>
 * ```
 *
 * @author Sergey Bogatyrev <sergey@bogatyrev.me>
 * @since 2.0
 */
class ReCaptcha extends InputWidget
{
    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';

    const SIZE_COMPACT = 'compact';
    const SIZE_NORMAL = 'normal';

    const TYPE_AUDIO  = 'audio';
    const TYPE_IMAGE = 'image';

    const RENDER_EXPLICIT = 'explicit';
    const RENDER_ONLOAD = 'onload';
    /**
     * default reCaptcha onload callback name.
     */
    const CALLBACK_DEFAULT = 'reCaptchaCallback';

    /**
     * @var string default reCaptcha component.
     */
    public $reCaptchaComponent = 'recaptcha';
    /**
     * @var string cient js variable name.
     */
    public $clientJsContainer = 'reCaptcha';
    /**
     * @var string the widget language.
     * @see https://developers.google.com/recaptcha/docs/language
     */
    public $language;
    /**
     * @var string Whether to render the widget explicitly.
     * Defaults to onload, which will render the widget in the first g-recaptcha tag it finds.
     * See also [[RENDER_EXPLICIT]] [[RENDER_ONLOAD]].
     */
    public $render;
    /**
     * @var string Your sitekey.
     */
    public $siteKey;
    /**
     * @var string The type of CAPTCHA to serve.
     */
    public $type;
    /**
     * @var string The color theme of the widget.
     */
    public $theme;
    /**
     * @var string The size of the widget.
     */
    public $size = self::SIZE_NORMAL;
    /**
     * @var array|string|JsExpression the reCaptcha verify callback implementation.
     * @see https://developers.google.com/recaptcha/docs/display#render_param
     */
    public $callback;
    /**
     * @var array|string|JsExpression the reCaptcha callback function to be executed when the recaptcha response expires
     * and the user needs to solve a new CAPTCHA..
     * @see https://developers.google.com/recaptcha/docs/display#render_param
     */
    public $expiredCallback;
    /**
     * @var string reCaptcha onload callback name.
     */
    public $onloadCallbackName = self::CALLBACK_DEFAULT;
    /**
     * @var array the reCaptcha plugin options.
     * @see https://developers.google.com/recaptcha/docs/display#render_param
     */
    public $clientOptions = [];
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'form-control'];

    /** @var \recaptcha\ReCaptchaComponent $_component */
    private $_component;


    /**
     * Initializes the widget.
     *
     * @throws InvalidConfigException if the "mask" property is not set.
     */
    public function init()
    {
        parent::init();

        if (empty($this->reCaptchaComponent)) {
            throw new InvalidConfigException("'reCaptchaComponent' property must be set.");
        }
        $this->_component = Yii::$app->get($this->reCaptchaComponent);

        if ($this->siteKey === null) {
            $this->siteKey = $this->_component->siteKey;
        }
        if ($this->language === null) {
            $this->language = Yii::$app->language;
        }
        if ($this->render === null) {
            $this->render = self::RENDER_ONLOAD;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $id = $this->options['id'];

        $userCallback = ($this->callback !== null) ? new JsExpression("({$this->callback}(response));") : '';
        $userExpiredCallback = ($this->expiredCallback !== null) ? new JsExpression("({$this->expiredCallback}(response));") : '';

        $this->callback = new JsExpression(
            //language=JavaScript
            "function (response){document.getElementById('$id').value = response; $userCallback}"
        );
        $this->expiredCallback = new JsExpression(
            //language=JavaScript
            "function (response){document.getElementById('$id').value = ''; $userExpiredCallback}"
        );

        $this->registerClientScript();
        echo Html::tag('div', '', ['id' => $this->getId()]);
        if ($this->hasModel()) {
            echo Html::activeHiddenInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::hiddenInput($this->name, $this->value, $this->options);
        }
    }

    /**
     * Initializes client options
     */
    protected function initClientOptions()
    {
        $options = $this->clientOptions;
        foreach ($options as $key => $value) {
            if (!$value instanceof JsExpression && in_array($key, ['callback', 'expired-callback'], true)) {
                $options[$key] = new JsExpression($value);
            }
        }

        if ($this->siteKey !== null) {
            $options['sitekey'] = $this->siteKey;
        }
        if ($this->theme !== null) {
            $options['theme'] = $this->theme;
        }
        if ($this->type !== null) {
            $options['type'] = $this->type;
        }
        if ($this->size !== null) {
            $options['size'] = $this->size;
        }
        if ($this->callback !== null) {
            $options['callback'] = $this->callback;
        }
        if ($this->expiredCallback !== null) {
            $options['expired-callback'] = $this->expiredCallback;
        }
        $this->clientOptions = $options;
    }

    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        $jsContainer = $this->clientJsContainer;

        $this->initClientOptions();

        $params = Json::htmlEncode($this->clientOptions);
        $id = $this->getId();
        $onloadCallback = $this->onloadCallbackName;

        $js = new JsExpression((Yii::$app->request->isAjax)
            //language=JavaScript
            ? "(function(){grecaptcha.render('$id', $params);})()"
            //language=JavaScript
            : "(function(){
                    if ((typeof window.$jsContainer !== 'object') || !(window.$jsContainer instanceof Array)) {
                        window.$jsContainer = new Array();
                    }
                    window.$jsContainer.push({'id': '$id', 'params': $params});
                })();"
        );
        $view->registerJs($js, $view::POS_END);

        if (!Yii::$app->request->isAjax) {

            $onloadJs = new JsExpression(
                //language=JavaScript
                "var $onloadCallback = function(){for (var i = 0; i < window.$jsContainer.length; i++) {grecaptcha.render(window.{$jsContainer}[i].id, window.{$jsContainer}[i].params);}}"
            );
            $view->registerJs($onloadJs, $view::POS_HEAD, 'onload-callback');
        }

        $jsFileParams = $this->getApiParams();
        $apiUrl = ReCaptchaComponent::API_URL;
        if (!empty($jsFileParams)) {
            $apiUrl .= '?' . $jsFileParams;
        }

        $view->registerJsFile($apiUrl,
            ['position' => $view::POS_END, 'async' => true, 'defer' => true], ReCaptchaComponent::API_FILE_KEY
        );
    }

    /**
     * @return string the URL-encoded string.
     */
    public function getApiParams()
    {
        $result = [
            'hl' => Yii::$app->language,
            'render' => $this->render,
        ];
        if ($this->callback !== null) {
            $result['onload'] = $this->onloadCallbackName;
        }
        return http_build_query($result, null, "&", \PHP_QUERY_RFC3986);
    }
}
