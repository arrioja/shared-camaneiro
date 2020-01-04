<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Implementa la inclusión de mensajes predeterminados en el sistema.
 *****************************************************  FIN DE INFO
*/

class incluir_mensaje extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
          }
    }

    
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $titulo = $this->txt_titulo->Text;
            $imagen = $this->txt_imagen->Text;
            $mensaje = $this->txt_mensaje->Text;
            $codigo = proximo_numero("intranet.mensajes","codigo",null,$this);
            // la función "rellena" de tantos caracteres a la izquierda como se le diga.
            $codigo = rellena($codigo,5,"0");

            // se inserta en la base de datos
            $sql = "insert into intranet.mensajes
                    (codigo, imagen, titulo, mensaje)
                    values ('$codigo','$imagen','$titulo','$mensaje')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertado mensaje ".$codigo.": ".$titulo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            // para asegurarme de autorecargar la pagina hago un llamado a ella misma.
            $this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }



}


?>
