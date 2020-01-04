<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Se encarga de las gestiones de login y logout del usuario en el
 *              sistema, si existen sesiones creadas las destruye y autentica
 *              la usuario con su login y su clave.
 *****************************************************  FIN DE INFO
 *
 *
 * La aplicacion del layout solo_login a la pagina intranet.login obedece a que
 * debido al redireccionamiento que existe en la funcion "comprueba_sesion" contenida
 * en comunes.autenticacion, si se usa el mismo template de intranet, se ocasiona
 * un error de loop infinito en la llamada a la misma pagina.
*/
class login extends TPage {

    public function btn_login_click($sender,$param)
    {
     if($this->Page->IsValid)  // Todas las validaciones se cumplieron
        {


            if (login_usuario($this->txt_login->Text,$this->txt_password->Text, $sender) == null)
            {
                 $this->Response->redirect($this->Service->constructUrl('intranet.login'));
            }
            else
            {
                /* Se incluye el rastro en el archivo de bitácora */
                $descripcion_log = "Entrada autorizada al sistema.";
                inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'L',$descripcion_log,"",$sender);
                $this->Response->redirect($this->Service->constructUrl('Home'));
            }
        }
    }

/* Comprueba que el nombre de usuario y la clave sean la correcta*/
    public function valida_usuario($sender,$param)
    {
        if(!(validar($this->txt_login->Text,$this->txt_password->Text)))
            {
                $param->IsValid=false;  // Notifica al validador que la validación falló.
            }
        else
            {
                $param->IsValid=true; // Notifica al validador que la validación funcionó
            }

    }

}

?>