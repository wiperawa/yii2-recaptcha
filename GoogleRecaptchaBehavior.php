<?php
namespace wiperawa\recaptcha;

use Yii;
use ReCaptcha\ReCaptcha;
use yii\base\Behavior;
use yii\validators\Validator;


class GoogleRecaptchaBehavior extends Behavior {

    public $token;

    public $google_recaptcha_secret_key;

    public $recaptcha_enabled = true;

    public $default_recaptcha_action = 'form_submit';

    public $score_threshold = 0.5;

    public $error_msg = 'Google reCAPTCHA Validation failed!';

    /**
     * Attach reCAPTCHA validator for $token field if this is s POST and recaptcha enabled by model behavior configuration
     * @param \yii\base\Component $owner
     * @return bool|void
     */
    public function attach($owner){

        /** @var $owner yii\base\Model */

        if (Yii::$app->request->isPost && $this->recaptcha_enabled) {

            $owner->validators[] = Validator::createValidator('required',$owner,'token');

            $owner->validators[] = Validator::createValidator(function ($attr) use ($owner)  {
                if ($this->recaptcha_enabled) {
                    $remote_ip = Yii::$app->request->remoteIP;
                    $recaptcha = new ReCaptcha($this->google_recaptcha_secret_key);
                    $resp = $recaptcha
                        ->setExpectedAction($this->default_recaptcha_action)
                        ->setScoreThreshold($this->score_threshold)
                        ->verify($this->$attr, $remote_ip);
                    if (!$resp->isSuccess()) {
                        $owner->addError($attr, $this->error_msg);
                        return false;
                    }
                }
                return true;
            },$owner,'token');
        }
        return true;
    }

}