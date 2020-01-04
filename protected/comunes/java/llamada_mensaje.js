/*
 * En este archivo se encuentra las llamadas al mensaje hecho en AJAX para
 * interacción servidor-cliente web.
 */

		function muestra_alert(msg)
		{ alert(msg);}

		function muestra_mensaje(idcomp)
		{
            document.getElementById(idcomp).show();
        }
		function oculta_mensaje(idcomp)
		{
            document.getElementById(idcomp).hide();
        }
