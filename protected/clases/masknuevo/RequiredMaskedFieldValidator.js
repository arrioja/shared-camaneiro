 /**
 * TRequiredFieldValidator class file
 *
 * RequiredMaskedFieldValidator checks if all placeholders from the Masked Input is filled.
 *
 * @author Lourival Júnior <junior.ufpa@gmail.com>
 */

RequiredMaskedFieldValidator = Class.extend(Prado.WebUI.TBaseValidator,
{
	/**
	 * @return boolean true if the Masked Input value has all PlaceHolders filled .
	 */
	evaluateIsValid : function()
	{		
		var mask = this.options.ControlMask;
		var fillspace = this.options.Fillspace;
       	var a = this.getValidationValue();
		for(i=0; i<=mask.length; i++){
			if(this.isPlaceHolder( mask.charAt(i) ) && a.charAt(i) == fillspace ){
				return false;
			}
		}
        //return(a != b);
		return true;
	},
	isPlaceHolder: function(chr){
		return chr == '!' || chr == '#' || chr == '?' || chr == '*';
	}
});