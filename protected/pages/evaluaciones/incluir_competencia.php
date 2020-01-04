<?php
    class incluir_competencia extends TPage
    {
        public function onLoad($param)
        {
            $sql="select * from evaluaciones.competencias_temp";
            $resultado=cargar_data($sql, $this);
            $this->datagrid_competencia->datasource=$resultado;
            $this->datagrid_competencia->databind();
        }
        public function incluir_temp($sender, $param)
        {
            //toma el valor de los controles, los pone en variables y los guarda en la tabla temporal
            $competencia=$this->id_competencia->Text;
            $codigo=$this->id_cod->Text;
            $nivel=$this->drop_nivel->Text;
            $nombre_corto=$this->id_nombre_corto->Text;
            $sql="insert into evaluaciones.competencias_temp (cod, descripcion, nivel, nombre_corto) values('$codigo', '$competencia', '$nivel', '$nombre_corto')";
            $resultado=modificar_data($sql, $sender);
            //lee de nuevo la tabla temporal para cargar el datagrid
            $sql="select * from evaluaciones.competencias_temp";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_competencia->datasource=$resultado;
            $this->datagrid_competencia->databind();
        }
        public function eliminar($sender, $param)
        {
            //obtiene el id del registro seleccionado y lo borra de la tabla temporal
            $id=$param->Item->Data[id];
            $sql="delete from evaluaciones.competencias_temp where(id='$id')";
            $resultado=modificar_data($sql, $sender);
            //lee de nuevo la tabla temporal para cargar el datagrid
            $sql="select * from evaluaciones.competencias_temp";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_competencia->datasource=$resultado;
            $this->datagrid_competencia->databind();
        }
        public function guardar($sender, $param)
        {
            //inserta los registros de la tabla temporal en la tabla fija
            $sql="insert into evaluaciones.competencias(codigo, descripcion, nivel, nombre_corto) select evaluaciones.competencias_temp.cod, evaluaciones.competencias_temp.descripcion, evaluaciones.competencias_temp.nivel, evaluaciones.competencias_temp.nombre_corto from evaluaciones.competencias_temp";
            $resultado=modificar_data($sql, $sender);
            //vacia la tabla temporal
            $sql="truncate table evaluaciones.competencias_temp";
            $resultado=modificar_data($sql, $sender);
            //lee de nuevo la tabla temporal para cargar el datagrid
            $sql="select * from evaluaciones.competencias_temp";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_competencia->datasource=$resultado;
            $this->datagrid_competencia->databind();
        }
    }
?>