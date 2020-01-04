<?php
class intranet_layout extends TTemplateControl
{
    /* función que dispara la comprobación inicial de que el usuario se encuentre
     * logeado en el sistema antes de cargar.
     */
    public function onLoad($param)
    {
        comprueba_sesion($this);
        $this->menu->Text = elabora_menu($this);
        $this->lbl_organizacion->Text = usuario_actual('rif_organizacion')." - ".usuario_actual('nombre_organizacion');
    }
    /* función que dispara el procedimiento de deslogeo del usuario contenida
     * en logout_usuario
     */
    public function logout_clicked($sender,$param)
    {
        logout_usuario($sender);
    }
}
?>
