Yii2 Google reCaptcha
=====================
Yii2 Google reCaptcha implementation

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist tonyaxo/yii2-recaptcha "*"
```

or add

```
"tonyaxo/yii2-recaptcha": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
'recaptcha' => [
    'class' => 'recaptcha\ReCaptchaComponent',
    'siteKey' => 'site_key',
    'secretKey' => 'key_secret',
],
```

You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
method, for example like this:

```php
<?= $form->field($model, 'reCaptcha')->widget(ReCaptcha::className(), [
    'id' => 'sign-up-captcha',
    'render' => ReCaptcha::RENDER_EXPLICIT,
])->label(false) ?>
```