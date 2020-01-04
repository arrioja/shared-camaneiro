<?php

class integrantes_constantes extends TPage
{
    var $suma;//variable global para mostrar el total en el footer del grid
    		function createMultiple($link, $array) {
                        $item = $link->Parent->Data;
                        $return = array();
                        foreach($array as $key) {
                              $return[] = $item[$key];
                         }
                         return implode(",", $return);
                }
  public function cargar_listado2()
    {
        $resultado_drop = obtener_seleccion_drop($this->drop_constantes);
        $cod_constante = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $tipo_nomina = usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        if ($tipo_nomina =='')//si no ha seleccionado el tipo de nómina
            {
            $sql="select p.cedula, p.nombres, p.apellidos,i.id, itn.tipo_nomina from nomina.integrantes i
            inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            where i.cedula not in (select cedula from nomina.integrantes_constantes where cod_constantes='$cod_constante' order by i.id asc) and i.status='1' and i.cod_organizacion='$cod_org' order by itn.tipo_nomina";
            }
        else
            {
            $sql="SELECT i.id,itn.tipo_nomina, p.cedula, p.nombres,p.apellidos
            from nomina.integrantes i inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE i.cedula not in(select cedula from nomina.integrantes_constantes where cod_constantes='$cod_constante') and i.status='1' and itn.tipo_nomina='$tipo_nomina' order by itn.tipo_nomina";
            }       
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

   public function cargar_listado()
    {
        $resultado_drop = obtener_seleccion_drop($this->drop_constantes);
        $cod_constante = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $tipo_nomina = usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        if ($tipo_nomina =='')//si no ha seleccionado el tipo de nómina
            {
            $sql="SELECT ic.id as id,itn.tipo_nomina,ic.monto, p.cedula, p.nombres,p.apellidos,c.cod,c.global
            FROM nomina.constantes c
            inner join nomina.integrantes_constantes ic on c.cod=ic.cod_constantes
            inner join nomina.integrantes i on i.cedula=ic.cedula
            inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE ic.cod_constantes ='$cod_constante' and i.cod_organizacion='$cod_org' and i.status='1' order by itn.tipo_nomina";

            }
        else
            {
            $sql="SELECT ic.id as id,itn.tipo_nomina, ic.monto, p.cedula, p.nombres,p.apellidos,c.cod,c.global
            FROM nomina.constantes c
            inner join nomina.integrantes_constantes ic on c.cod=ic.cod_constantes
            inner join nomina.integrantes i on i.cedula=ic.cedula
            inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE ic.cod_constantes ='$cod_constante' and i.cod_organizacion='$cod_org' and i.status='1' and itn.tipo_nomina='$tipo_nomina' order by i.cedula,itn.tipo_nomina";
            }
        $resultado=cargar_data($sql,$this);
        $this->suma=count($resultado);
        return $resultado;
    }

    public function cargar_constantes()
    {
        $cod_organizacion=usuario_actual('cod_organizacion');
        $sql="select cod,descripcion from nomina.constantes where (cod_organizacion='$cod_organizacion') order by descripcion";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

    public function cargar_tipo_nomina()
    {
         $cod_organizacion=usuario_actual('cod_organizacion');
         $sql="select * from nomina.tipo_nomina where cod_organizacion='$cod_organizacion'";
         $resultado=cargar_data($sql,$this);
         return $resultado;
    }


	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
          if (usuario_actual('tipo_nomina')!="")
               {
                    $this->drop_constantes->DataSource=$this->cargar_constantes();
                    $this->drop_constantes->dataBind();
               }
          else
               $this->Response->redirect($this->Service->constructUrl('nomina.set_nomina',array('redir'=>'nomina.integrantes.integrantes_constantes')));
          }
    }

/* esta función se encarga de implementar el evento on_intemchange del dropdown
 * de los grupos, en la cual se actualiza el listado de los modulos que el grupo
 * tiene disponible y el listado de los que no tiene disponibles.
 */
    public function actualiza_listado($param)
    {
		/*$this->DataGrid_tienen->DataSource=$this->cargar_listado();
		$this->DataGrid_tienen->dataBind();*/

       /* $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();*/
            $this->DataGrid_prueba->EditItemIndex=-1;
            $this->DataGrid_prueba->DataSource=$this->cargar_listado();
            $this->DataGrid_prueba->dataBind();

            $this->DataGrid_no_tienen->EditItemIndex=-1;
            $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
            $this->DataGrid_no_tienen->dataBind();

    }

	public function agregar($sender,$param)
	{
       $resultado_drop = obtener_seleccion_drop($this->drop_constantes);
        $cod_constante = $resultado_drop[1]; // el segundo valor que corresponde con el codigo

        $sql="select * from nomina.constantes where cod='$cod_constante'";
        $res=cargar_data($sql,$this);
        $monto=$res[0]["monto"];
        $global=$res[0]["global"];
		$cedula=$this->DataGrid_no_tienen->DataKeys[$param->Item->ItemIndex];

        if ($global=='1')//si es una constante global inserta en conceptos tambien
            {
            $sql="insert into nomina.integrantes_constantes(cedula,cod_constantes,monto) values ('$cedula','$cod_constante','$monto')";
            $resultado=modificar_data($sql,$this);
            $sql="insert into nomina.integrantes_conceptos(cedula,cod_concepto) values ('$cedula','$cod_constante')";
            $resultado=modificar_data($sql,$this);
            }
        else
            {$sql="insert into nomina.integrantes_constantes(cedula,cod_constantes,monto) values ('$cedula','$cod_constante','0')";
            $resultado=modificar_data($sql,$this);
            }

        /* Se incluye el rastro en el archivo de bitácora */
       /* $descripcion_log = "Concedido permiso al grupo ".$codigo_grupo." para acceder al m&oacute;dulo ".$codigo_modulo;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);*/

		$this->DataGrid_prueba->DataSource=$this->cargar_listado();
		$this->DataGrid_prueba->dataBind();

        $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();
	}

   public function editItem3($sender,$param)
    {
        $this->DataGrid_prueba->EditItemIndex=$param->Item->ItemIndex;

        //Se actualiza el grid de los modulos a los que SI tiene la constante seleccionada

            $this->DataGrid_prueba->DataSource=$this->cargar_listado();
            $this->DataGrid_prueba->dataBind();
    }
public function itemCreated3($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cedula3->TextBox->ReadOnly="True";
            $item->nombres3->TextBox->ReadOnly="True";
            $item->apellidos3->TextBox->ReadOnly="True";
        }

        if($item->getItemType() === 'Footer')
        {
            $total = new TLabel();
            $total->Text='total: '.$this->suma;
             $item->Cells[0]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[0]->Controls->insertAt(0,$total);//$item->getControls()->add(0,"hola");
        }

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar3->Button->Attributes->onclick='if(!confirm(\'esta Seguro?\')) return false;';
        }
    }


    public function cancelItem3($sender,$param)
    {
        $this->DataGrid_prueba->EditItemIndex=-1;
        //Se actualiza el grid de los modulos a los que SI tiene la constante seleccionada
            $this->DataGrid_prueba->DataSource=$this->cargar_listado();
            $this->DataGrid_prueba->dataBind();
    }


    public function deleteItem3($sender,$param)//sin revisar
    {

        $id=$this->DataGrid_prueba->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM nomina.integrantes_constantes WHERE id = '$id'";
        $resultado=modificar_data($sql,$this);
        $sql="DELETE FROM nomina.integrantes_conceptos WHERE cedula='' and cod_concepto = '$cod_concepto'";//falta borrar en conceptos
        $this->DataGrid_prueba->EditItemIndex=-1;

		$this->DataGrid_prueba->DataSource=$this->cargar_listado();
		$this->DataGrid_prueba->dataBind();

        $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();
    }

    public function eliminar($sender,$param)
    {
$cod_concepto=$this->drop_constantes->SelectedValue;
   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $id=$datos[0];
   $cedula=$datos[1];

    $sql="DELETE FROM nomina.integrantes_constantes WHERE id = '$id'";
    $resultado=modificar_data($sql,$this);
    $sql="DELETE FROM nomina.integrantes_conceptos WHERE cedula='$cedula' and cod_concepto = '$cod_concepto'";
    $resultado=modificar_data($sql,$this);
    $this->DataGrid_prueba->EditItemIndex=-1;

		$this->DataGrid_prueba->DataSource=$this->cargar_listado();
		$this->DataGrid_prueba->dataBind();

        $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();
    }

public function saveItem3($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid_prueba->DataKeys[$item->ItemIndex];

        $monto=$item->monto3->TextBox->Text;
		$monto=TPropertyValue::ensureFloat(ltrim($monto,'BsF'));
        $global=$item->global->TextBox->Text;
        $cod_constante=$item->cod->TextBox->Text;;
            if ($global=='0')//si no es global
                $sql="UPDATE nomina.integrantes_constantes set monto=$monto  where id='$id'";
            else
                $sql="UPDATE nomina.integrantes_constantes set monto=$monto where cod_constantes='$cod_constante'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid_prueba->EditItemIndex=-1;
		$this->DataGrid_prueba->DataSource=$this->cargar_listado();
		$this->DataGrid_prueba->dataBind();

    }
        public function changePage($sender,$param)
	{
       	/*	$this->DataGrid_tiene->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid_tiene->DataSource=$this->cargar();
            $this->DataGrid_tiene->dataBind();*/
	}
}

?>
