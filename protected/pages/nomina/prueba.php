<?php
class prueba extends TPage
{
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {

            }
        }
function mostrar()
  {
$this->Response->redirect($this->Service->constructUrl('sin_plantilla.excel'));
  }

    public function procesar($serder,$param)
    {
       

    }
}


?>
