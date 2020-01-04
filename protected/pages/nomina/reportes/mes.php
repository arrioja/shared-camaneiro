<?php
class mes extends TPage
{



    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.reportes.reportes'));
    }

public function onLoad($param)
	{
 parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{


        }


   }
  function export($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.reportes.reportes'));
    }
}


?>
