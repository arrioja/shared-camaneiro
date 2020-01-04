<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ver_archivo extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $sql="select * from archivo.archivo_desc";
              $resultado=cargar_data($sql,$this);
              $this->DataGrid->DataSource=$resultado;
              $this->DataGrid->dataBind();
          }
	}
    public function ver_detalle($sender,$param)
    {
        $codigo=$sender->CommandParameter;
        $fecha2=cambiaf_a_mysql(date("d/m/Y"));
        $cedula=usuario_actual('cedula');        
        $sql3="select nombres, apellidos from organizacion.personas where(cedula='$cedula')";
        $resultado3=cargar_data($sql3, $this);
        $usuario=$resultado3[0]['nombres'].' '.$resultado3[0]['apellidos'];        
        // Se incluye el rastro en el archivo de bitácora
        $descripcion_log = "El usuario ".$login." reviso el documento con el codigo".$codigo."  del archivo muerto ";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        $sql2="insert into archivo.archivo_log
            (codigo, usuario, fecha2)
            values
            ('$codigo', '$usuario', '$fecha2')";
        $resultado2=modificar_data($sql2, $this);
        $this->Response->Redirect( $this->Service->constructUrl('archivo.detalle',array('codigo'=>$codigo)));//
        
    }
}
?>
