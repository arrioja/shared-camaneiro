<?php
/**
 * TRequiredFieldValidator class file
 *
 * @author Lourival Júnior <junior.ufpa@gmail.com>
 */

Prado::using('System.Web.UI.WebControls.TBaseValidator');

/**
 * TRequiredFieldValidator class
 *
 * RequiredMaskedFieldValidator check if the associated input control value matches with its mask.
 * The input control fails validation if its value does not match the mask property.
 *
 */
class RequiredMaskedFieldValidator extends TBaseValidator
{

	protected $ControlMask;
	protected $Fillspace;

	/*
	* Publish the client script version of this validator
	*/
	protected function addAttributesToRender($writer)
	{
		parent::addAttributesToRender($writer);
		$control = $this->getValidationTarget();
		$JSFile=$this->publishAsset('RequiredMaskedFieldValidator.js');
		$csm = $this->getPage()->getClientScript();
		if(!$csm->isScriptFileRegistered('RequiredMaskedFieldValidator'))
			$csm->registerScriptFile('RequiredMaskedFieldValidator', $JSFile);			
	}
	
	/*
	* Set up some important variables
	*/
	public function onInit($param)
	{
		parent::onInit($param);
		$control = $this->getValidationTarget();
		$this->ControlMask = $control->getMask();
		$this->Fillspace = $control->getFillspace();
	}
	
	/**
	 * This method overrides the parent's implementation.
	 * The validation succeeds if the input component value matches its mask property using regular expresision.
	 *
	 * @return boolean whether the validation succeeds
	 */
	protected function evaluateIsValid()
	{
		$control = $this->getValidationTarget();
		if($control instanceof MaskedTextBox){			
			return $this->validateControl($control);
		}else{
			throw new TException('controltovalidate_invalid');
		}
	}	

	/*
	* Performs the validation. 
	*/
	private function validateControl($control)
	{
		$value=$this->getValidationValue($control);
		$regex = $this->placeHoldersToRegex($this->ControlMask);
		return ereg($regex,$value);
	}
	
	/*
	* Parse the Mask property of the input component to a Regular Expression
	*/
	private function placeHoldersToRegex($mask){
		$parsedMask = '';
		for($i=0;$i<strlen($mask);$i++){		
			$parsedMask .= $this->getRegex( $mask{$i} );
		}
		return $parsedMask;
	}
	
	/*
	* Get the regex to the current character
	*/
	private function getRegex($chr){
		switch($chr) {
			case '!'://Only characters
				$chr = '[a-zA-Z]';
			break;
			case '#'://Only numbers
				$chr = '[0-9]';
			break;
			case '?'://Numbers and characters
				$chr = '[a-zA-Z0-9]';			
			break;
			case '*'://Anything
				$chr = '[*]';
			break;
			default://The default char of the mask.
				$chr = "[".$chr."]";
		}
		return $chr;
	}

	/**
	 * Returns an array of javascript validator options.
	 * @return array javascript validator options.
	 */
	protected function getClientScriptOptions()
	{
		$options = parent::getClientScriptOptions();
		$options['ControlMask']=$this->ControlMask;		
		$options['Fillspace']=$this->Fillspace;
		return $options;
	}
	
	/**
	 * Gets the name of the javascript class responsible for performing validation for this control.
	 * This method overrides the parent implementation.
	 * @return string the javascript class name
	 */
	protected function getClientClassName()
	{
		return 'RequiredMaskedFieldValidator';
	}

	/**
	 * @return string the initial value of the associated input control. Defaults to empty string.
	 * If the associated input control does not change from this initial value
	 * upon postback, the validation fails.
	 */
	public function getInitialValue()
	{
		return $this->getViewState('InitialValue','');
	}

	/**
	 * @param string the initial value of the associated input control.
	 * If the associated input control does not change from this initial value
	 * upon postback, the validation fails.
	 */
	public function setInitialValue($value)
	{
		$this->setViewState('InitialValue',TPropertyValue::ensureString($value),'');
	}
	
}

?>