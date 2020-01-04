<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class devolver extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->fecha1->text=date("d/m/Y");
        }
        $id=$this->Request['id'];
        $cost=$this->Request['costo'];
        $regre_canti=$this->Request['regre'];
        $idvinculo=$this->Request['idvinculo'];
        //$idcons=$this->Request['miid'];
        //$this->t0->text=$idcons;
        //$sql="select * from bienes.consumibles_entregados where(id='$id')";
        $sql="select * from bienes.consumibles_entregados where(id='$id' and costo='$cost' and idvinculo='$idvinculo')";
        $resultado=cargar_data($sql, $this);
        $dir_entrega=$resultado[0]['direccion_entrega'];        
        $sql2="select nombre_completo from organizacion.direcciones where(codigo='$dir_entrega')";
        $resultado2=cargar_data($sql2, $this);
        $nomb_dir=$resultado2[0]['nombre_completo'];
        $this->t1->text=$nomb_dir;
        //llena el listbox con los valores desde 1 hasta el maximo disponible
        $valores=array();        
        for($i = 1; $i <= $regre_canti; $i++)
        {
            $valores[$i]=$i;
        }
        $this->ddl1->DataSource=$valores;
        $this->ddl1->DataBind();        
    }
    public function hacer_devolucion($sender, $param)
    {
        $fecha_devo=cambiaf_a_mysql($this->fecha1->text);
        echo" id ".$id=$this->Request['id'];
        echo" costo ".$cost=$this->Request['costo'];
        echo" regre ".$regre=$this->Request['regre'];
        echo" idvinculo ".$idvinculo=$this->Request['idvinculo'];
        //*echo" miid ".$miid=$this->t0->text;
        echo" dev_can ".$devolver_cantidad=$this->ddl1->text;
        $sql2="select * from bienes.consumibles_entregados where(id='$id' and costo='$cost')";
        $resultado2=cargar_data($sql2, $this);
        echo" idviejo ".$idviejo=$resultado2[0]['idvinculo'];
        echo" cant_cons_entr ".$cant_cons_entr=$resultado2[0]['cantidad'];
        echo" descrip ".$desc=$resultado2[0]['descripcion'];
        echo" dir_ent ".$dir_entrega=$resultado2[0]['direccion_entrega'];
        $sql2a="select nombre_completo from organizacion.direcciones where(codigo='$dir_entrega')";
        $resultado2a=cargar_data($sql2a, $sender);
        echo" nom_con ".$nom_com=$resultado2a[0]['nombre_completo'];
        //*$idabuscar=$resultado[0]['idvinculo'];
        $sql3="select * from bienes.consumibles where(idvinculo='$idviejo' and costo='$cost')";
        $resultado3=cargar_data($sql3, $this);
        echo" nuevo_actual ".$nuevo_actual=$resultado3[0]['actual']+$devolver_cantidad;
        $sql4="update bienes.consumibles set actual='$nuevo_actual' where(idvinculo='$idviejo' and costo='$cost')";
        $resultado4=modificar_data($sql4, $this);
        $sql5="select sum(actual) from bienes.consumibles where(idvinculo='$idviejo')";
        $resultado5=cargar_data($sql5, $this);
        echo" nuevo_total ".$nuevo_total=$resultado5[0]['sum(actual)'];
        $sql6="update bienes.consumibles_corto set total='$nuevo_total' where(idvinculo='$idviejo')";
        $resultado6=modificar_data($sql6, $this);
        echo " cant_cons_entr ".$cant_cons_entr=$cant_cons_entr-$devolver_cantidad;
        if($cant_cons_entr==0)
        {            
            $sql7="delete from bienes.consumibles_entregados where(id='$id' and idvinculo='$idviejo' and costo='$cost')";
            $resultado7=modificar_data($sql7, $this);
        }
        else
        {            
            $sql8="update bienes.consumibles_entregados set cantidad='$cant_cons_entr', fecha_devo='$fecha_devo' where(id='$id' and idvinculo='$idviejo' and costo='$cost')";
            $resultado8=modificar_data($sql8, $this);
        }
        //* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "El usuario ".$login." ha devuelto ".$devolver_cantidad." ".$desc." de la dirección de ".$nom_com;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.devoluciones'));
    }
}
?>