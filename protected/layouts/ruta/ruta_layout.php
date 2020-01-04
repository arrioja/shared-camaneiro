<?php
class ruta_layout extends TTemplateControl
{
    /* función que dispara la comprobación inicial de que el usuario se encuentre
     * logeado en el sistema antes de cargar.
     */

    public function onLoad($param)
    {
        comprueba_sesion($this);
        $menu_elaborado = elabora_menu($this);
        $this->menu->Text=$menu_elaborado;
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
