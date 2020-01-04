<?php

class integrantes_conceptos extends TPage
{
   public function cargar_listado2()
    {
        $resultado_drop = obtener_seleccion_drop($this->drop_conceptos);
        $cod_concepto = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $tipo_nomina = usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        if ($tipo_nomina =='')//si no ha seleccionado el tipo de nómina
            {
            $sql="select p.cedula, p.nombres, p.apellidos,i.id, itn.tipo_nomina from nomina.integrantes i
            inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            where i.cedula not in (select cedula from nomina.integrantes_conceptos where cod_concepto='$cod_concepto') and i.status='1' and i.cod_organizacion='$cod_org' order by itn.tipo_nomina";
            }


        else
            {
            $sql="SELECT i.id,itn.tipo_nomina, p.cedula, p.nombres,p.apellidos
            from nomina.integrantes i inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE i.cedula not in(select cedula from nomina.integrantes_conceptos where cod_concepto='$cod_concepto') and i.status='1' and itn.tipo_nomina='$tipo_nomina' order by itn.tipo_nomina";
            }

        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

   public function cargar_listado()
    {
        $resultado_drop = obtener_seleccion_drop($this->drop_conceptos);
        $cod_concepto = $resultado_drop[1]; // el segundo valor que corresponde con el codigo        
        $tipo_nomina =usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado


        if ($tipo_nomina =='')//si no ha seleccionado el tipo de nómina
            {
            $sql="SELECT ic.id,itn.tipo_nomina, p.cedula, p.nombres,p.apellidos,c.cod
            FROM nomina.conceptos c
            inner join nomina.integrantes_conceptos ic on c.cod=ic.cod_concepto
            inner join nomina.integrantes i on i.cedula=ic.cedula
            inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE ic.cod_concepto ='$cod_concepto' and i.cod_organizacion='$cod_org' and i.status='1' order by itn.tipo_nomina";

            }
        else
            {
            $sql="SELECT ic.id,itn.tipo_nomina, p.cedula, p.nombres,p.apellidos,c.cod
            FROM nomina.conceptos c
            inner join nomina.integrantes_conceptos ic on c.cod=ic.cod_concepto
            inner join nomina.integrantes i on i.cedula=ic.cedula
            inner join organizacion.personas p on p.cedula=i.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE ic.cod_concepto ='$cod_concepto' and i.cod_organizacion='$cod_org' and i.status='1' and itn.tipo_nomina='$tipo_nomina' order by i.cedula,itn.tipo_nomina";
            }
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

    public function cargar_conceptos()
    {
        $cod_organizacion=usuario_actual('cod_organizacion');
        $sql="select cod,descripcion from nomina.conceptos where (cod_organizacion='$cod_organizacion') order by descripcion";
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
                $this->drop_conceptos->DataSource=$this->cargar_conceptos();
                $this->drop_conceptos->dataBind();
               }
          else
               $this->Response->redirect($this->Service->constructUrl('nomina.set_nomina',array('redir'=>'nomina.integrantes.integrantes_conceptos')));

          }
	}

/* esta función se encarga de implementar el evento on_intemchange del dropdown
 * de los grupos, en la cual se actualiza el listado de los modulos que el grupo
 * tiene disponible y el listado de los que no tiene disponibles.
 */
    public function actualiza_listado($param)
    {
		$this->DataGrid_tienen->DataSource=$this->cargar_listado();
		$this->DataGrid_tienen->dataBind();

        $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();
    }

	public function agregar($sender,$param)
	{
       $resultado_drop = obtener_seleccion_drop($this->drop_conceptos);
        $cod_concepto = $resultado_drop[1]; // el segundo valor que corresponde con el codigo

		$cedula=$this->DataGrid_no_tienen->DataKeys[$param->Item->ItemIndex];

        $sql="insert into nomina.integrantes_conceptos(cedula,cod_concepto) values ('$cedula','$cod_concepto')";
        $resultado=modificar_data($sql,$this);

        /* Se incluye el rastro en el archivo de bitácora */
       /* $descripcion_log = "Concedido permiso al grupo ".$codigo_grupo." para acceder al m&oacute;dulo ".$codigo_modulo;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);*/

		$this->DataGrid_tienen->DataSource=$this->cargar_listado();
		$this->DataGrid_tienen->dataBind();

        $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();
	}

    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
           /* $item->cedula->TextBox->ReadOnly="True";
            $item->anos->TextBox->Columns=8;*/

        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar->Button->Attributes->onclick='if(!confirm(\'esta Seguro?\')) return false;';
        }
    }

    public function cancelItem($sender,$param)
    {
        $this->DataGrid_tienen->EditItemIndex=-1;
        $cod_org=usuario_actual('cod_organizacion');
        $resultado_drop = obtener_seleccion_drop($this->drop_conceptos);
        $cod_concepto = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        //Se actualiza el grid de los modulos a los que SI tiene la constante seleccionada
        $sql="SELECT ic.id,itn.tipo_nomina, ic.monto, p.cedula, p.nombres,p.apellidos,c.cod
        FROM nomina.conceptos c
        inner join nomina.integrantes_conceptos ic on c.cod=ic.cod_conceptos
        inner join nomina.integrantes i on i.cedula=ic.cedula
        inner join organizacion.personas p on p.cedula=i.cedula
        inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
        WHERE ic.cod_conceptos ='$cod_concepto' and i.cod_organizacion='$cod_org' and i.status='1' ";
        $this->DataGrid_tienen->DataSource=cargar_data($sql,$this);
        $this->DataGrid_tienen->dataBind();
    }


    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid_tienen->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM nomina.integrantes_conceptos WHERE id = '$id'";
        $resultado=modificar_data($sql,$this);
        $this->DataGrid_tienen->EditItemIndex=-1;

		$this->DataGrid_tienen->DataSource=$this->cargar_listado();
		$this->DataGrid_tienen->dataBind();

        $this->DataGrid_no_tienen->DataSource=$this->cargar_listado2();
		$this->DataGrid_no_tienen->dataBind();
    }

        public function changePage($sender,$param)
	{
       	/*	$this->DataGrid_tiene->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid_tiene->DataSource=$this->cargar();
            $this->DataGrid_tiene->dataBind();*/
	}
}

?>
