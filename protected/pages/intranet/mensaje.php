<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un mensaje predeterminado, es realmente util cuando hay
 *              que mostrar el mismo mensaje varias veces en paginas diferentes
 *              del sitio.
 *****************************************************  FIN DE INFO.
*/
class mensaje extends TPage {

/* Se capturan los valores que vienen como parámetros
 * desde la página que hace la llamada.
 */
	public function onLoad($param)
	{
        if (!empty($this->Request['codigo']))
        {
            $codigo = $this->Request['codigo'];
        }
        if (!empty($this->Request['adic']))
        {
            $adicional = $this->Request['adic'];
        }
        else
        {
            $adicional = "";
        }

        $sql="select * from intranet.mensajes where codigo='$codigo'";
        $mensaje=cargar_data($sql,$this);

        $this->lbl_titulo->Text=$mensaje[0]['titulo'];
        $this->lbl_mensaje->Text=$mensaje[0]['mensaje'].$adicional;
        $this->imagen->ImageUrl = "imagenes/botones/".$mensaje[0]['imagen'];
    }

    public function btn_click($param)
    {
      //  $this->lbl_org->Text = $this->Page->getPagePath();
      //  $this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00002')));
      $this->Response->redirect($this->Service->constructUrl('Home'));
    }
}
?>
