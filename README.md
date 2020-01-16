# yii2-recaptcha
Yii2 widget and model behavior for Google Recaptcha v3.

This widget and behavior allow you to easy add  reCAPTCHA v3 (invisible reCAPTCHA) by Google into your project.

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require wiperawa/yii2-recaptcha "dev-master"
```

or add

```
"wiperawa/yii2-recaptcha": "dev-master"
```

to the ```require``` section of your `composer.json` file.

## Usage

1. First of all we need to add site in google reCAPTCHA [console](https://www.google.com/recaptcha/admin/)
and get SECRET and SITE keys.

2. Then we have to configure behavior for model we use:

```php
use wiperawa\recaptcha\GoogleRecaptchaBehavior;
...
class MyCoolModel extends ActiveRecord {
...
public function behaviors()
    {
        return [
            'googleRecaptchaBehavior' => [
                'class' =>GoogleRecaptchaBehavior::class,
                'secretKey' => 'Google reCAPTCHA Secret Key here',
                'enabled' => true, //Can omit this parameter. just in case you want to temporarily switch off recaptcha 
                'expectedAction' => 'form_submit' //google reCAPTCHA expected action. action we expect from our form.
            ],
        ];
    }
...
}
```

3. Now when we have behavior attached to our model, we need to add widget to ActiveForm :

```php
use wiperawa\recaptcha\GoogleRecaptchaWidget;
...
<?php $form = ActiveForm::begin(...) ?>

<?= $form->field($model,'recaptchaToken')->
    widget(GoogleRecaptchaWidget::class,[
        'siteKey' => 'Google reCAPTCHA SITEKEY here',
        'expectedAction' => 'form_submit',
        'class' => 'w_google_recaptcha_widget' //Classname of input. optional
    ])?>


```

That's it!
Widget will look for 'beforeSubmit' action, then receive google reCAPTCHA token, and submit form.
After form submitted, behavior attach token validation, and validate input. if error happend, validator will attach error to model $errors prop.
