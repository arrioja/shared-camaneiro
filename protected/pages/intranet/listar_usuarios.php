<?php
class listar_usuarios extends TPage
{
    public function onLoad($param)
    {
    parent::onLoad($param);
    if(!$this->IsPostBack)
      {
        $sql="select u.cedula, u.login, u.email, CONCAT(p.nombres,' ',p.apellidos) as nomb
 						FROM intranet.usuarios as u, organizacion.personas as p
						WHERE (u.cedula=p.cedula)
						ORDER BY nomb";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
      }
    }

    public function changePage($sender,$param)
	{
        $sql="select u.cedula, u.login, u.email, CONCAT(p.nombres,' ',p.apellidos) as nomb
 						FROM intranet.usuarios as u, organizacion.personas as p
						WHERE (u.cedula=p.cedula)
						ORDER BY nomb";
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'P&aacute;gina: ');
	}


}

?>
