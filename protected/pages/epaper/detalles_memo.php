<?php
require('/var/www/tcpdf/tcpdf.php');
class detalles_memo extends TPage
{    
	public function onLoad($param)
	{
        parent::onLoad($sender, $param);
        if(!$this->IsPostBack)
          {              
              $cadena=$this->Request['nummemo'];//el numero del memo lo guardo en una variable              
              list($siglas, $correlativo, $ano)=explode('-', $cadena);//divido la cadena en las subcadenas siglas/correlativo/ano              
              $this->t1->text=$cadena;
              $sql1="select * from organizacion.memoranda where(siglas='$siglas' and correlativo='$correlativo' and ano='$ano')";//busca datos en la tabla memoranda
              $resultado=cargar_data($sql1, $this);
              $sql2="select * from organizacion.adjuntos where(siglas='$siglas' and correlativo='$correlativo' and ano='$ano')";//busca datos en la tabla adjuntos
              $resultado2=cargar_data($sql2, $this);
              $this->dg1->DataSource=$resultado2;
              $this->dg1->dataBind();
              $longitud2=count($resultado2);//obtengo la longitud de resultado2
              for($a=0; $a<$longitud2; $a++)
              {
                  $this->t6->text=$this->t6->text.$resultado2[$a]['nom_adjunto'].' ';//copio el nombre de los adjunto en un texto oculto(t6) para poderlos pasar al pdf
              }              
              $longitud=count($resultado);//obtengo la longitus del array resultado
              $this->t2->text=$resultado[0]['fecha'];
              $this->t3->text=$resultado[0]['asunto'];
              $this->t4->text=ucwords(strtolower($resultado[0]['destinatario']));
              $ini_desti=explode(" ", $this->t4->text);
              $longitud_desti=count($ini_desti);
              for($y=0; $y<=$longitud_desti; $y++)
              {
                  $this->t4a->text=$this->t4a->text.$ini_desti[$y][0];//escribo las iniciales del destinatario en un cuadro de texto oculto
              }              
              $this->t5->text=ucwords(strtolower($resultado[0]['remitente']));
              $ini_remit=explode(" ", $this->t5->text);
              $longitud_remit=count($ini_remit);
              for($z=0; $z<=$longitud_remit; $z++)
              {
                  $this->t5a->text=$this->t5a->text.$ini_remit[$z][0];//ecribo las iniciales del remitente en un cadro de texto oculto
              }              
              $this->html1->text=$resultado[0]['memo'];
          }
    }
    public function renombrar($sender, $param)
    {
        $nomcod=$sender->CommandParameter;
        list($nombre, $codigo)=explode('-',$nomcod);        
        $tipo=filetype('attach/'.$codigo);
        $nuevo_nombre=str_replace(" ","_",$nombre);
        header('Content-Disposition: attachment; filename='.$nuevo_nombre);
        readfile('attach/'.$codigo);        
    }
    public function imprimir($sender, $param)
    {        
        $pdf=new TCPDF();        
        $pdf->AddPage();        
        $pdf->Cell(0, 0, 'Republica Bolivariana de Venezuela', 0, 1, 'C');
        $pdf->Cell(0, 0, 'Camara del Municipio Maneiro', 0, 1, 'C');
        $pdf->Cell(0, 0, 'Memorando', 0, 1, 'C');
        $pdf->Ln();
        $pdf->SetFont('helvetica', 'B', '12');
        $pdf->MultiCell(0, 0, 'NRO        :'.$this->Request['nummemo'], 0, 'L', 0, 1, 10, 40);
        $pdf->Cell(0, 0, 'FECHA    :'.$this->t2->text, 0, 1, 'L');
        $pdf->Cell(0, 0, 'DE           :'.$this->t5->text, 0, 1, 'L');
        $pdf->Cell(0, 0, 'PARA      :'.$this->t4->text, 0, 1, 'L');
        $pdf->Cell(0, 0, 'ASUNTO :'.$this->t3->text, 0, 1, 'L');
        $pdf->SetFont('', '', '');
        $pdf->Ln();
        $pdf->Ln();
        $html=$this->html1->text;
        $pdf->writeHTML($html, true, 0, true, 0);
        $pdf->MultiCell(0, 0, $this->t5a->text.'/'.$this->t4a->text, 0, 'L', 0, 1, 10, 260);
        $pdf->Cell(0, 0, 'Adjuntos: '.$this->t6->text, 0, 1, 'L');
        $pdf->Output("Detalles.pdf",'D');
        $pdf->Output("Detalles.pdf",'D');
    }
}
?>
