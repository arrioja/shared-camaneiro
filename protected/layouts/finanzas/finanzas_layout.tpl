<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<com:THead Title="Proyecto SIMON --- SISTEMA FINANCIERO">

<style type="text/css">
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-image: url(<%~ 03_01.png %>);
	background-repeat: repeat-y;
}
</style>
</com:THead>
<script type="text/javascript" src="protected/comunes/java/llamada_mensaje.js"></script>
<body class="flyout">
<com:TForm ID="form">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr height="133">
    <td width="2%"><img width="139" height="133" src=<%~ 01_01.png %> /></td>
    <td width="94%"><img width="100%" height="133" src=<%~ 01_02.png %>  /></td>
    <td width="2%"><img width="525" height="133" src=<%~ 01_03.png %>  /></td>
    <td width="2%"><img width="224" height="133" src=<%~ 01_04.png %>  /></td>
  </tr>
  <tr>
    <td width="2%"><img width="139" height="23" src=<%~ 02_01.png %>  /></td>
    <td colspan="3" valign="top" background-repeat="no-repeat" background=<%~ 02_02.png %> >      <div align="right">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="vinculos"><div align="left">Usuario: <com:TLabel ID="lbl_usuario_top" Text="SIN SESI&Oacute;N INICIADA"/> </div>

                                                </td>
            <td><div align="right"><span class="vinculos"><a href="index.php" class="vinculos">Inicio</a>&nbsp;&nbsp;/&nbsp;&nbsp;
             <com:TLinkButton Text="Salir"
                OnClick="logout_clicked"
                Visible="True"
                CausesValidation="false" />
                        &nbsp;&nbsp;</span></div></td>
          </tr>
        </table>
        </div>
  </tr>
  <tr>
    <td valign="top" class="flyout">
                <com:TSafeHtml>
                    <com:TLiteral ID="menu" Text="" />
                </com:TSafeHtml>
            </td>
    <td colspan="3" valign="top">
			<div id="cuerpo">

                    <com:TContentPlaceHolder ID="cuerpo" />

			</div>
                </td>
  </tr>
  <tr >
    <td >&nbsp;</td>
    <td colspan="3" valign="middle" height="2%"><h5 align="center"><com:TLabel ID="lbl_organizacion" Text="" />
    </h5></td>
  </tr>
</table>
</com:TForm>
</body>
</html>