<?php
namespace wiperawa\recaptcha;

use Yii;
use yii\base\Model;
use yii\base\Behavior;
use ReCaptcha\ReCaptcha;
use yii\validators\Validator;


class GoogleRecaptchaBehavior extends Behavior {

    /*
     * Public property to be attached to owner model, used to validate response, should be present in activeForm
     */
    public $recaptcha_token;

    /*
     * Google reCAPTCHA SECRET key
     */
    public $secretKey;

    /*
     * just in case you want to turn off reCAPTCHA validation tepmorarily
     */
    public $enabled = true;

    /*
     * Expected action. Should we the same that we set in GoogleRecaptchaWidget
     */
    public $expectedAction = 'form_submit';

    /*
     * Score threshold. see reCAPTCHA docs for explanation of this
     */
    public $scoreThreshold = 0.5;

    /*
     * Error message prefix that we add in model error. suffix = recaptcha error code.
     */
    public $errorMessage = 'Google reCAPTCHA Validation failed';


    /**
     * @inheritDoc
     * @return array
     */
    public function events(){
        return [
            Model::EVENT_AFTER_VALIDATE => 'afterValidate'
        ];
    }

    /**
     * After validation complete, we need to clear recaptcha_token, in case some validation error happend and need to display form again.
     * and detach required validator, because activeForm beforeSubmit event will be triggered only after success form validation
     * @return void
     */
    public function afterValidate(){
        if ($this->owner->recaptcha_token != '') {
            $this->owner->recaptcha_token = '';
        }
        foreach ($this->owner->validators as $key=>$validator) {
            if ( ($validator instanceof \yii\validators\RequiredValidator) &&
                in_array('recaptcha_token',$validator->attributes) ) {
                $this->owner->validators->offsetUnset($key);
                break;
            }
        }
    }

    /**
     * Attach reCAPTCHA validator for $token field if this is s POST and recaptcha enabled by model behavior configuration
     * @param \yii\base\Component $owner
     * @return bool
     */
    public function attach($owner){

        parent::attach($owner);
        /** @var $owner yii\base\Model */
	
        if (Yii::$app->request->isPost && $this->enabled) {
            $validators = $owner->validators;
            $validators->append(Validator::createValidator('required',$owner,'recaptcha_token'));

            $validators->append(
            Validator::createValidator(function ($attr) use ($owner)  {
                if ($this->enabled) {

                    $remote_ip = Yii::$app->request->remoteIP;
                    $recaptcha = new ReCaptcha($this->secretKey);

                    $resp = $recaptcha
                        ->setExpectedAction($this->expectedAction)
                        ->setScoreThreshold($this->scoreThreshold)
                        ->verify($this->$attr, $remote_ip);

                    if (!$resp->isSuccess()) {
                        $owner->addError($attr, $this->errorMessage.': '.$resp->getErrorCodes()[0]);
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