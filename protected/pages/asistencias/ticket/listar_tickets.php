<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Mediante esta página, El usuario puede eliminar y modificar
 *              los tickets agregados o disminuidos de otros meses
 *****************************************************  FIN DE INFO
*/

class listar_tickets extends TPage
{
    var $conteo =1;
    var $cedula_conteo = "";
    var $permisos;
    public function onLoad($param)
    {
        parent::onLoad($param);
        if ((!$this->IsPostBack) && (!$this->IsCallBack))
        {

            $cod_organizacion = usuario_actual('cod_organizacion');
 
             // funcionarios activos de asistencia
            $sql="select p.cedula,  CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
                        ORDER BY p.nombres, p.apellidos";

            $resultado=cargar_data($sql,$this);
            $this->drop_cedula->DataSource=$resultado;
            $this->drop_cedula->dataBind();

             // se toma año de los registros de entrada_salida
             $this->drop_ano->Datasource = ano_asistencia($this);
             $this->drop_ano->dataBind();

             //meses
             $arreglo=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
             $this->drop_mes->DataSource=$arreglo;
             $this->drop_mes->dataBind();
        }
    }

/* Esta función se encarga de mostrar el listado con las solicitudes de vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_tickets($sender, $param)

    {
       $cedula= $this->drop_cedula->SelectedValue;

       // se consulta los si tiene tickets para sumar
       $sql="SELECT id,cantidad,tipo,motivo FROM asistencias.tickets  WHERE  cedula='$cedula'
            AND ano='".$this->drop_ano->SelectedValue."' AND mes='".$this->drop_mes->SelectedValue."'";
       $tickets=cargar_data($sql,$sender);

       $this->DataGrid->DataSource=$tickets;
       $this->DataGrid->dataBind();
        

    }


    public function eliminar_click($sender,$param)
    {
        $id=$sender->CommandParameter;

       $sql="SELECT cantidad,tipo,motivo FROM asistencias.tickets  WHERE  id='$id' ";
       $tickets=cargar_data($sql,$sender);
        // se elimina
        $sql="DELETE FROM asistencias.tickets WHERE id='$id' ";
        $resultado=modificar_data($sql,$sender);
        //Se incluye el rastro en el archivo de bitácora
        $descripcion_log = "Se ha Eliminado tickets: Cantidad ".$tickets[0]['cantidad']." de tipo ".$tickets[0]['tipo']." de Fecha ".$this->drop_ano->SelectedValue."-".$this->drop_mes->SelectedValue.
                               " del Funcionario Cedula Nro: ".$this->drop_cedula->SelectedValue;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->consulta_tickets($sender, $param);
    }

/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $item=$param->Item;

        if ($item->tipo->Text == "SUMA")
         $item->tipo->Text="Agregar";

        if ($item->tipo->Text == "RESTA")
         $item->tipo->Text="Disminiur";   
    }

}
?>