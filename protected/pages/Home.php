<?php

class Home extends TPage
{
    public function onLoad($param)
    {
        $this->lbl_org->Text = usuario_actual('nombre_organizacion');
    }

    public function click($param)
    {
      //  $this->lbl_org->Text = $this->Page->getPagePath();
      // $this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00002')));
    }
    
	public function llamar($sender,$param)
	{
        $Message01 = 'Éste es un mensaje de prueba para ver que tan bien se puede ver un mensaje asi de largo, usando PHP, Prado Framework, y JavaScript en un solo archivo.';
        $this->LTB->titulo->Text = "Título desde el Servidor";
        $this->LTB->texto->Text = $Message01;
        $this->LTB->imagen->Imageurl = "imagenes/botones/prohibido.png";
        $this->LTB->redir->Text = "Home";
        $params = array('resultado');
        $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
	}
}

?>
