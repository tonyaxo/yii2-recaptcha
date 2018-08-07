Yii2 Google reCaptcha
=====================
Yii2 Google reCAPTCHA version 2.0 implementation.

[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-authclient/downloads.png)](https://packagist.org/packages/tonyaxo/yii2-recaptcha)

Overview
--------

ReCaptcha API version 2.0 [docs](https://developers.google.com/recaptcha/intro).

### Features

* All reCAPTCHA API 2.0 features;
* Multiple support; 
* Ajax (Pjax) support;
* jQuery not require;

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist tonyaxo/yii2-recaptcha "~1.0"
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
'components' => [
    'recaptcha' => [
        'class' => 'recaptcha\ReCaptchaComponent',
        'siteKey' => 'site_key',
        'secretKey' => 'key_secret',
    ],
];
```

You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
method, for example like this:

```php
<?= $form->field($model, 'reCaptcha')->widget(ReCaptcha::class, [
    'id' => 'sign-up-captcha',
    'render' => ReCaptcha::RENDER_EXPLICIT,
])->label(false) ?>
```