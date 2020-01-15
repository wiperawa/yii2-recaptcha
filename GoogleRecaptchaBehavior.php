<?php
namespace wiperawa\recaptcha;

use Yii;
use ReCaptcha\ReCaptcha;
use yii\base\Behavior;
use yii\validators\Validator;
use yii\base\Model;


class GoogleRecaptchaBehavior extends Behavior {

    public $recaptcha_token;

    public $google_recaptcha_secret_key;

    public $recaptcha_enabled = true;

    public $default_recaptcha_action = 'form_submit';

    public $score_threshold = 0.5;

    public $error_msg = 'Google reCAPTCHA Validation failed';


    /**
     * Attach reCAPTCHA validator for $token field if this is s POST and recaptcha enabled by model behavior configuration
     * @param \yii\base\Component $owner
     * @return bool|void
     */
    public function attach($owner){

        parent::attach($owner);
        /** @var $owner yii\base\Model */

        if (Yii::$app->request->isPost && $this->recaptcha_enabled) {
            $validators = $owner->getValidators();
            $validators->append(Validator::createValidator('required',$owner,'recaptcha_token'));

            $validators->append(
            Validator::createValidator(function ($attr) use ($owner)  {
                if ($this->recaptcha_enabled) {
                    $remote_ip = Yii::$app->request->remoteIP;
                    $recaptcha = new ReCaptcha($this->google_recaptcha_secret_key);
                    $resp = $recaptcha
                        ->setExpectedAction($this->default_recaptcha_action)
                        ->setScoreThreshold($this->score_threshold)
                        ->verify($this->$attr, $remote_ip);
                    if (!$resp->isSuccess()) {
                        $owner->addError($attr, $this->error_msg.': '.$resp->getErrorCodes()[0]);
                        return false;
                    }
                }
                return true;
            },$owner,'recaptcha_token')
            );

        }

        return true;
    }

}