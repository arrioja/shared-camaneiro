<?php
class menu extends TPage
{
    public function onLoad($param)
    {

        $menu_elaborado = elabora_menu($this);
        $this->menu->Text=$menu_elaborado;
    }
}
?>
