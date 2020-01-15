<?php
namespace wiperawa\recaptcha;

use Yii;
use yii\base\InputWidget;
use yii\base\InvalidConfigException;
use Yii\helpers\Html;


class GoogleRecaptchaWidget extends InputWidget {


/**
 *Google Recaptha Sitekey
 */
public $sitekey = '';

const GOOGLE_RECAPTCHA_SCRIPT_URL = "https://www.google.com/recaptcha/api.js?render=";

public function init(){
	
	parent::init();
	
	if (!$this->sitekey) {
	    throw new InvalidConfigException('Setup correct Google Recaptcha Sitekey!');
	}
	
	public function run()
	{
	    parent::run();

	    $input = $this->getInput();
	    
	    $this->registerAssets();
	    
	    echo $input;
	}

	protected function registerAssets() {
	    $this-registerJsFile(self::GOOGLE_RECAPTCHA_SCRIPT_URL.$this->sitekey,[['position' => $this::POS_END, 'async'=>true, 'defer'=>true]]);
	    
	}
	
	protected function getInput(){
	
	    if ($this->hasModel()) {
		$field = Html::activeHiddenInput($this->model,$this->attribute,$this->options);
	    } else {
		$field = Html::hiddenInput($this->name,'',$this->options);
	    }
	}
}


}
