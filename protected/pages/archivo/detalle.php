<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class detalle extends TPage
{
    function onload($param)
    {
         parent::onLoad($param);
         if(!$this->IsPostBack)
         {
             $prefijo="attach/archivo/";
             $carpeta=$this->Request['codigo'];
             $arreglo[]=array();
             $i=0;
             $ruta=$prefijo.$carpeta."/";
             $dir=dir($ruta);
             while ($elemento = $dir->read())
             {
                 if ( ($elemento != '.') and ($elemento != '..'))
                 {
                     $arreglo[$i]=$prefijo.$carpeta."/".$elemento;
                     $i=$i+1;
                 }
             }
             $dir->close();             
             reset($arreglo);             
             $alfinal=array_shift($arreglo);             
             array_push($arreglo, $alfinal);             
             $i=0;
             $y=count($arreglo);             
             while($i<$y)
             {
                 $this->t9->text=$this->t9->text.$arreglo[$i]."*";
                 $i=$i+1;
             }
             $this->t10->text=0;
             $this->t11->text=$y-1;
             $this->img1->ImageUrl=current($arreglo);
         }         
    }
    function ver_listado($sender, $param)
    {
        $this->Response->Redirect( $this->Service->constructUrl('archivo.ver_archivo'));
    }
    function ant($sender, $param)
    {        
        $elementos=explode("*", $this->t9->text);
        $a=$this->t10->text;
        if($a<=0)
        {
            $this->b1->enabled="false";
            $this->b2->enabled="true";
        }
        else
        {
            $this->img1->ImageUrl=$elementos[$a-1];
            $this->t10->text=$a-1;
        }
    }
    function sig($sender, $param)
    {        
        $elementos=explode("*", $this->t9->text);        
        $b=$this->t10->text;
        if($b>=$this->t11->text)
        {
            $this->b1->enabled="true";
            $this->b2->enabled="false";
        }
        else
        {
            $this->img1->ImageUrl=$elementos[$b+1];
            $this->t10->text=$b+1;
        }
    }
}
?>
