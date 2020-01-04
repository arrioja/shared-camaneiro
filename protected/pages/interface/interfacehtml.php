<?php
class interfacehtml extends TPage
{
    var $numero;
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->numero=rand(0000,9999);
              $sql="select correlativo from organizacion.adjuntos where(correlativo='$this->numero')";//lo busco en la db
              $resultado=cargar_data($sql, $this);
               while($numero==$resultado[0]['correlativo'])
            {
                $numero=rand(0000,9999);
                $sql="select correlativo from organizacion.adjuntos where(correlativo='$numero')";//lo busco en la db
                $resultado=cargar_data($sql, $this);
            }
          }
    }

    public function cargar($sender, $param)
    {
        if($sender->HasFile)
       	{

            //$numero= ESTE TIENE QUE SER EL ALEATORIO UNICO PARA EL MEMO
            /*$numero=rand(0000,9999);//genero un aleatorio
            $sql="select correlativo from organizacion.adjuntos where(correlativo='$numero')";//lo busco en la db
            $resultado=cargar_data($sql, $sender);
            while($numero==$resultado[0]['correlativo'])
            {
                $numero=rand(0000,9999);
                $sql="select correlativo from organizacion.adjuntos where(correlativo='$numero')";//lo busco en la db
                $resultado=cargar_data($sql, $sender);
            }*/

            $ruta="attach/";
            $codigo=rand(0000000,99999999);
            while(file_exists($ruta.$codigo))
            {
                $codigo=rand(00000000,99999999);
            }
            if(file_exists($ruta.$codigo))
            {
                echo "error";
            }
            else
            {
                $sql="insert into organizacion.adjuntos (direccion, siglas, correlativo, ano, nom_adjunto)
                      values('$direccion', '$siglas', '$this->numero', '$ano', '$sender->FileName')";
                $resultado=modificar_data($sql, $sender);
                $this->t1->text=$this->t1->text.$codigo."-".$sender->FileName."\n";
                $sql="update organizacion.adjuntos set cod_adjunto='$codigo' where(correlativo='$numero')";
                $resultado=modificar_data($sql, $sender);
                $sender->saveAs($ruta.$codigo);
            }
       	}	
    }
}
?>