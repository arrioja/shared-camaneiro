<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	
 * Descripción: Muestra la forma para incluir la foto de la perosna para el marcado.
 *****************************************************  FIN DE INFO
*/

class incluir_persona_foto extends TPage
{

public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
           //llena el listbox con las cedulas

            $sql="  SELECT cedula,concat(nombres,', ', apellidos,' /V',cedula) as nombre
                    FROM organizacion.personas order by nombre asc";
            $resultado=cargar_data($sql, $this);
            $this->drop_persona->DataSource=$resultado;
            $this->drop_persona->dataBind();

        }
    }


         public function fileUploaded($sender,$param)
    {
        if($sender->HasFile)
        {
            $nombreclean=htmlspecialchars($sender->FileName);
            $hh=date("H")+8;
            $hora = date("d-m-Y $hh:i:s");
            $nuevodirectorio=$_SERVER['DOCUMENT_ROOT']."/cene/imagenes/fotos/$hora.$nombreclean";
            $filename=$sender->FileName;
            $filetemp=$sender->localName;
            $ubicacion="cene/imagenes/fotos/".$hora.$nombreclean;
            $nombre_bd=$hora.$nombreclean;

           if(copy($filetemp, $nuevodirectorio))
            {
                $this->Result->Text="Subida con Exito!";
                $sql="UPDATE organizacion.personas set foto='$nombre_bd' WHERE cedula='".$this->drop_persona->Selectedvalue."'";
                $resultado=modificar_data($sql,$sender);
                chmod($nuevodirectorio, 0777);


            }else{
            $this->Result->Text="Error!";

            }


        }

    }

  
}

?>
