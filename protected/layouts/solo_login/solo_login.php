<?php
class solo_login extends TTemplateControl
{
    public function onLoad($param)
    {
//        comprueba_sesion($this);
//       $pagina=$this->Service->determineRequestedPagePath();
//        $sesion=new THttpSession;
//        $sesion->open();
//        $this->lbl_usuario_top->Text=$sesion['login'];
//        $sesion->close();
//       var_dump($pagina);
    }
    public function logout_clicked($sender,$param)
    {
        logout_usuario($sender);
    }


}
?>
