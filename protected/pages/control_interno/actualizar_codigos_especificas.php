<?php


/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class actualizar_codigos_especificas extends TPage {

	public function onTextChanged()
	{
		foreach($this->page->findControlsByType('TActiveTextBox') as $control)
		{
			if($control instanceof TControl)
			{
				$ids[]=$control->ID;
			}
		}
		var_dump($this->$sender->ID);
		if (strlen($this->txt_t->Text)==$this->txt_t->MaxLength)
		{
			$page=$this->Application->getservice('page')->RequestedPage;
			$page->CallbackClient->callClientFunction("Prado.Element.focus",array($this->txt_ano->getClientID()));
		}
	}
}
?>