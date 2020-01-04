<?php
class prueba extends TPage
{
    public function onLoad($param)
    {
       
    }
    function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
  }

    public function procesar($serder,$param)
    {
       

    }
}


?>
