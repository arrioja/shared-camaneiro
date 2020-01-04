<?php
/**
 * Class MaskedTextBox.
 *
 * MaskedTextBox Component. Requires a Mask pattern to apply in the TextBox.
 * The Mask Could be like this: 
 * 
 * <com:MaskedTextBox ID="MyTextBox" Mask='(###) ####-####' />
 * 
 * The '#' is a placeholder. '#' Indicates a place where only Numbers will be
 * accept. This component accepts the '!' for letters only, '?' for alphanumerics and '*' for anything
 * The fillspace parameter will set a character to representates the placeholders to user
 * 
 * Examples:
 * <com:MaskedTextBox ID="MyTextBox" Mask=''##/##/####'' />
 * <com:MaskedTextBox ID="MyTextBox" Mask=''##:##:##'' />
 * <com:MaskedTextBox ID="MyTextBox" Mask=''####????'' />
 * 
 * And so on :)
 *
 * @author Lourival Jnior <junior.ufpa@gmail.com>
 * @version $Revision: $  $Date: $
 */
class ActiveMaskedTextBox extends TActiveTextBox
{

	protected function addAttributesToRender($writer)
	{
		parent::addAttributesToRender($writer);
		if($this->getTextMode()===TTextBoxMode::SingleLine)
		{
			if($this->mask !== "") 
			{
				$page=$this->getPage();
				$isEnabled=$this->getEnabled(true);
				if($isEnabled && $page->getClientSupportsJavaScript() && $this->getEnableClientScript())
				{
					$JSFile=$this->publishAsset('MaskedTextBox.js');
					$writer->addAttribute('id',$this->getClientID());
					$csm = $page->getClientScript();
					if(!$csm->isScriptFileRegistered('MaskedTextBox'))
						$csm->registerScriptFile('MaskedTextBox', $JSFile);			
					$this->getPage()->getClientScript()->registerPostBackControl('MaskedTextBox',$this->getPostBackOptions());
				}
				if(strlen($this->getMask())>0){
					$writer->addAttribute('maxlength',strlen($this->getMask()));
				}
			}
		}				  
	}
	
	/*
	* Gets the data without the mask.
	*/
	function getUnmaskedData()
	{
		$mask =  $this->getMask();
		$maskedData = $this->getText();
		$unmaskedData = '';
		
		if($mask !== "") 
		{
			for($i=0; $i < strlen($mask); $i++)
			{
				if($this->isPlaceHolder( $mask{$i} ) && $this->isFilled( $maskedData{$i} ))
				{				
					$unmaskedData .= $maskedData{$i};				
				}
			}
		}
		else
		{
			$unmaskedData = $maskedData;
		}
		return $unmaskedData;
	}
	
	/*
	* Checks for a placeholder
	*/
	protected function isPlaceHolder($chr){
		switch($chr){
			case '#':
			case '!':
			case '?':
			case '*':
				return true;
			break;
			default:
				return false;
		}
	}
	
	protected function isFilled($chr){
		if($this->getFillspace() != $chr)
			return true;
		else
			return false;
	}
	
	/**
	 * Gets the post back options for this textbox.
	 * @return array
	 */
	protected function getPostBackOptions()
	{
		$options['ID'] = $this->getClientID();
		$options['Mask'] = $this->getMask();
		$options['fillSpace'] = $this->getFillspace();
		return $options;
	}
 
	/**
	 * @return string  Defaults to ''.
	 */
	public function getMask()
	{
		return $this->getViewState('Mask','');
	}
 
	/**
	 * @param string 
	 */
	public function setMask($value)
	{
		if($value == "")
		{
			$this->text = $this->getUnmaskedData();	
		}
		$this->setViewState('Mask',TPropertyValue::ensureString($value),'');
	}
	
	/**
	 * @return string  Defaults to '_'.
	 */
	public function getFillspace()
	{
		return $this->getViewState('Fillspace','_');
	}
 
	/**
	 * @param string 
	 */
	public function setFillspace($value)
	{
		$this->setViewState('Fillspace',TPropertyValue::ensureString($value),'_');
	}
	
	/**
	 * @return boolean  Defaults to true.
	 */
	public function getEnableClientScript()
	{
		return $this->getViewState('EnableClientScript',true);
	}
 
	/**
	 * @param boolean 
	 */
	public function setEnableClientScript($value)
	{
		$this->setViewState('EnableClientScript',TPropertyValue::ensureBoolean($value),true);
	}	
	
}
?>