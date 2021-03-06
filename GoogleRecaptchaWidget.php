<?php

namespace wiperawa\recaptcha;

use Yii;
use yii\web\View;
use Yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;
use yii\base\InvalidConfigException;


class GoogleRecaptchaWidget extends InputWidget
{

    /**
     * Google Recaptcha Script URL
     */
    const GOOGLE_RECAPTCHA_SCRIPT_URL = "https://www.google.com/recaptcha/api.js?render=";

    /**
     *Google Recaptha Sitekey
     */
    public $siteKey = '';

    /**
     * Token input classname
     */
    public $class = 'w_google_recaptcha_widget';

    /**
     * @var string
     * Expected Action to be send to Google, used to validate response in recaptchaBehavior.
     * use the same action that you specified in recaptcha model behavior
     */
    public $expectedAction = 'form_sumit';

    /**
     * @inheritDoc
     */
    public function init()
    {

        parent::init();

        if (!$this->siteKey) {
            throw new InvalidConfigException('Setup correct Google Recaptcha Sitekey!');
        }
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        parent::run();

        $input = $this->getInput();

        $this->registerAssets();

        if ($this->field) {
            $this->field->template = "{input}{error}";
        }
        echo $input;
    }

    /**
     * Registering Google Recaptcha JS script with sitekey and our form submitting js hook
     */
    protected function registerAssets()
    {
        $this->getView()->registerJsFile(self::GOOGLE_RECAPTCHA_SCRIPT_URL . $this->siteKey, ['position' => View::POS_END, 'async' => true, 'defer' => true]);

        $js = <<<JS
            
            function w_run_recaptcha(action, callback) {
                grecaptcha.ready(() => {
                        grecaptcha.execute("{$this->siteKey}", {action: action}).then(function (token) {
                            callback(token);
                    });
                });
            }
            
            let expected_action = "{$this->expectedAction}";
            let token_input = $(".{$this->class}");
            let _form = $(".{$this->class}").closest('form');
            if (_form) {
                $(document).on('beforeSubmit',_form, function(evt){
                    if (!token_input.val()) {
                        evt.preventDefault();
                        w_run_recaptcha(expected_action,(token) => {
                            token_input.val(token);
                            _form.submit();
                        });
                        return false;
                    }
                })
            }
        JS;
        $this->getView()->registerJs($js,View::POS_READY);
    }


    /**
     * Preparing hidden field for token depending on how widget used (as a field widget method or directly)
     * @return string
     */

    protected function getInput()
    {

        $this->options = ArrayHelper::merge(
            $this->options, ['class' => $this->class]
        );
	
        if ($this->hasModel()) {
            //$this->addErrorClassIfNeeded();
            $field = $this->field->hiddenInput($this->options);
        } else {
            $field = Html::hiddenInput($this->name, '', $this->options);
        }
        return $field;
    }

}
