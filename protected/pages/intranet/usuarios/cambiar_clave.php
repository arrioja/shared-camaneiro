<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa el cambio de clave del usuario.
     ****************************************************  FIN DE INFO
*/

class cambiar_clave extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_login->Text = usuario_actual('login');
          }
    }

    /* Se incluyen los datos del nuevo usuario en la base */
	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cedula = usuario_actual('cedula');
            $login =usuario_actual('login');
            $clave=substr(MD5($this->txt_clave->Text), 0, 200);

            /* Se guardan los datos del usuario. */

		    $sql="UPDATE intranet.usuarios set clave='$clave' where ((login = '$login') and (cedula = '$cedula')) limit 1";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "El usuario ".$login." ha cambiado su clave de acceso.";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'M',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
 	}
}

?>
