<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

</html>
<com:THead Title="Proyecto SIMON --- INTRANET">

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
<!-- TemplateBeginEditable name="head" --><!-- TemplateEndEditable -->
</com:THead>

<body>
<com:TForm ID="form">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="2%"><img width="139" height="133" src=<%~ 01_01.png %> /></td>
    <td width="94%"><img width="100%" height="133" src=<%~ 01_02.png %>  /></td>
    <td width="2%"><img width="525" height="133" src=<%~ 01_03.png %>  /></td>
    <td width="2%"><img width="224" height="133" src=<%~ 01_04.png %>  /></td>
  </tr>
  <tr>
    <td width="2%"><img width="139" height="31" src=<%~ 02_01.png %>  /></td>
    <td colspan="3" valign="top" background=<%~ 02_02.png %> >      <div align="right">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="vinculos"><div align="left">Usuario: <com:TLabel ID="lbl_usuario_top" Text="SIN SESI&Oacute;N INICIADA"/> </div>

                                                </td>
             

            <td><div align="right"><span class="vinculos"><a href="index.php" class="vinculos">Inicio</a>&nbsp;&nbsp;&nbsp;&nbsp;

                        &nbsp;&nbsp;</span></div></td>
          </tr>
        </table>
        </div>
  </tr>
  <tr>
    <td valign="top">
        <div id="menu_iz">
                 <com:TContentPlaceHolder ID="menu_iz" />
			</div>

            </td>
    <td colspan="3" valign="top">
			<div id="cuerpo">
                
                    <com:TContentPlaceHolder ID="cuerpo" />
                
			</div>
                </td>
  </tr>
</table>
</com:TForm>
</body>
</html>
