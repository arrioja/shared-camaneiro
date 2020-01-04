/**
Copyright (c) 2008 Angelo Camargo (uacaman at gmail.com) 

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

Original Code: http://forum.imasters.uol.com.br/index.php?showtopic=267475&hl=script+sobre+m%E1scaras
**/


/*
* Class to manipulate the caret and selection from one element
*/		 
var XCaret = Class.create();
Object.extend(XCaret.prototype, 
{
    /*
    * Class constructor
    * @param obj - id of element 
    */		 
    initialize: function(obj) 
	{
        this.obj = $(obj);
    },
	
    /*
    * Get current position of the carret 
    * If there is a selection, the start of the selection is used as position
    * @return int
    */		
   GetPosition: function() 
	{
        var pos;
        
        if(this.obj.createTextRange) 
		{
            var range;
            range = document.selection.createRange().duplicate();
            range.moveEnd("character", this.obj.value.length);
            
            if(!range.text)
			{
                pos = this.obj.value.length;
			}
            else
			{
                pos = this.obj.value.lastIndexOf(range.text);
			}
	    } 
		else 
		{
            pos = this.obj.selectionStart;
        }
        
        return pos;
    },
	
    /*
    * Set the carret to a position
    * @param pos - postion of the carret
    */		
   SetPosition: function(pos) 
	{
        if(this.obj.createTextRange) 
		{
            var range = this.obj.createTextRange();
            range.collapse(true);
            range.moveStart("character", pos);
            range.moveEnd("character", 0);
            range.select();
        } 
		else 
		{
            this.obj.setSelectionRange(pos, pos);
        }
    },

    /*
    * Get the current selected range
    * @return object - object.left and object.right, the start and end of the selection
    */			
	GetSelection: function() 
	{
        var left, right;
        
        if(this.obj.createTextRange) 
		{
            var range;
            range = document.selection.createRange().duplicate();
            range.moveEnd("character", this.obj.value.length);
            
            if(!range.text)
			{
                left = this.obj.value.length;
			}
            else
			{
                left = this.obj.value.lastIndexOf(range.text);
			}
            range = document.selection.createRange().duplicate();
            range.moveStart("character", -this.obj.value.length);
            right = range.text.length;
        } 
		else 
		{
            left = this.obj.selectionStart;
            right = this.obj.selectionEnd;
        }
        
        return {left: left, right: right};
    },
	
   /*
    * Select the text 
    * @param left - start of the selection
    * @param right - end of the selection
    */		
   SetSelection: function(left, right) 
	{
        if(this.obj.createTextRange) 
		{
            var range = this.obj.createTextRange();
            range.moveStart("character", left);
            range.moveEnd("character", right);
            range.select();
        } 
		else 
		{
            this.obj.setSelectionRange(left, right);
        }
    }	
});

/*
* Extends the prototype object to add the CTRL, ALT and SHIFT keys
*/	
Object.extend(Event, 
{
    KEY_SHIFT:    16,
    KEY_CTRL:     17,
    KEY_ALT:      18
});

/*
* Class to create the maked input
*/	
var MaskedTextBox = Class.create();
MaskedTextBox.ranges = 
{
    numeric: [48, 57],
    padnum: [96, 105],
    characteres: [65, 90],
    all: [0, 255]
};

/*
* Check if a range is valid
* @param n - value
* @param range - array, index zero, the lower limit, index 1 the higher limit. 
* @return boloean
*/	
MaskedTextBox.inRange = function(n, range) 
{
    return n >= range[0] && n <= range[1];
};

/*
* Returns the valid range for the reserved char of the mask
* @param char - 
* @return MaskedTextBox.ranges
*/	
MaskedTextBox.validRange = function(char) 
{
    switch(char) {
        case '!':
            return [MaskedTextBox.ranges.characteres];
        case '#':
            return [MaskedTextBox.ranges.numeric];
        case '?':
            return [MaskedTextBox.ranges.characteres, MaskedTextBox.ranges.numeric];
        case '*':
            return [MaskedTextBox.ranges.all];
    }
    
    return null;
};

/*
* Check if a char is part of the a reserved char for the mask
* @param char - c
* @return bolean
*/	
MaskedTextBox.isMaskChar = function(char)
{
    return MaskedTextBox.validRange(char) != null;
};


Object.extend(MaskedTextBox.prototype, 
{
    /*
    * Class constructor
    * @param obj - id of the object to mask
    * @param mask - # acceptonly numbers, ? - letters and number, ! only letters, * accept anything
    * @param spacer - if the spacer is especified, it is used as a filler for empty spaces, default is '_'          
    */	
    initialize: function(obj) 
	{
	    obj.Mask === undefined ? this.mask = '' : this.mask = obj.Mask;
	    
        this.ctrl    = false;
        this.alt     = false;
        this.shift   = false;
        this.locked  = false;

        this.obj = $(obj.ID);
        this.spacer = obj.fillSpace || '_';
		this.dValue = this.DefaultValue();
		this.oCaret = new XCaret(obj.ID);
        
        this.DoFormat(this,false);

        this.obj.onkeypress     = this.KeyDown.bindAsEventListener(this);
        this.obj.onkeyup        = this.KeyUp.bindAsEventListener(this);
		this.obj.onkeydown      = this.KeyPress.bindAsEventListener(this);
		this.obj.onfocus        = this.DoFocus.bindAsEventListener(this);
		this.obj.oncontextmenu  = Event.stop.bindAsEventListener(this);
    },
    
    KeyDown : function(evt)
    {
        var e = evt || event;
        var code = e.keyCode || e.which || e.charCode;
        if(this.locked == true)
        {
            this.locked  = false;
            Event.stop(e);
        }   
    },
    
    KeyUp : function(evt)
    {
        var e = evt || event;
        var code = e.keyCode || e.which || e.charCode;
        if(this.ctrl == true)
        {
            this.DoFormat.defer(this,true);
        }
        this.ctrl    = false;
        this.alt     = false;
        this.shift   = false;

        if(this.locked == true)
        {
            this.locked  = false;
            Event.stop(e);
        }        
    }, 

  	
    KeyPress: function(evt) 
	{
	    var e = evt || event;
        var code = e.keyCode || e.which || e.charCode;

        if(String.fromCharCode(code) == 'V')
        {
            if(this.oCaret.GetPosition() == 0)
            {
                this.obj.value = '';
                this.DoFormat.defer(this,false);
            }
        }
        
        switch(code) 
		{
            case Event.KEY_LEFT:
            case Event.KEY_RIGHT:
            case Event.KEY_HOME:
            case Event.KEY_END:
            case Event.KEY_TAB:
            case Event.KEY_RETURN:
            case Event.KEY_SHIFT:
            case Event.KEY_ALT:
                break;
            case Event.KEY_BACKSPACE:
                Event.stop(e);
                this.DoBackSpace(e);
                this.locked  = true;
                break;
            case Event.KEY_DELETE:
                Event.stop(e);
                this.DoDelete();
                this.locked  = true;
                break;

            case Event.KEY_CTRL:
                this.ctrl    = true;
                break;
            default:
                this.DoType(e,code);
        }	
    },

    DoDelete : function()
    {
        var sel = this.oCaret.GetSelection()
        var pos = this.oCaret.GetPosition();
        var value = this.obj.value;
        
        if(sel.left != sel.right)
        {
            var left    = value.substr(0, sel.left);
            var right   = value.substr(sel.right, value.length - 1);
            var middle    = this.dValue.substr(sel.left, sel.right - sel.left);
            this.obj.value = left + middle + right;
            this.oCaret.SetPosition(pos);
        }
        else
        {
		    if(pos >= value.length)
		    {
		        return;
		    }
		    else
		    {
		        var char  = value.charAt(pos);
                var mChar = this.mask.charAt(pos);
		        if(char != mChar)
		        {
    		        if(MaskedTextBox.isMaskChar(mChar) == true)
		            {
		                char = this.spacer;
		            }
		            else
		            {
	                    char = mChar;
                    }
                }		
                var left    = value.substr(0, pos);
                var right   = value.substr(pos+1, value.length - 1);
                this.obj.value = left + char + right;
                this.oCaret.SetPosition(pos+1);
            }   
        }
    },
    
    DoBackSpace :function() 
	{
		var pos = this.oCaret.GetPosition() - 1;
		if(pos < 0)
		{
		    return;
		}
		else
		{
		    var value = this.obj.value;
            var mChar = this.mask.charAt(pos);
            var char  = value.charAt(pos);
		    if(char != mChar)
		    {
    		    if(MaskedTextBox.isMaskChar(mChar) == true)
		        {
		            char = this.spacer;
		        }
		        else
		        {
                    char = mChar;
                }
            }		
            
            var left    = value.substr(0, pos);
            var right   = value.substr(pos + 1, value.length - 1);
            this.obj.value = left + char + right;
		    this.oCaret.SetPosition(pos);
	    }
    },
    
    Next: function()
    {
        var result = -1;
        var value = this.obj.value; 
        var pos = this.oCaret.GetPosition();
        var size = value.length;
        
        for(var x=pos;x < size; x++)
        {
            var char  = value.charAt(x);
            var mChar = this.mask.charAt(x);  
            
            if(mChar != char)
            {
                result = x;
                break;
            }
        }
        return result;

    },
    
    DoType: function(e,code)
    {
        if(this.ctrl == true || this.alt || this.shift )
        {
            return;
        }
        
        if((code >= 41 && code <= 122) || code == 32 || code > 186)
        {
            this.locked  = true;
            Event.stop(e);
            if(MaskedTextBox.inRange(code, MaskedTextBox.ranges.padnum))
            {
                code -= 48;
            }        
            
            var pos = this.Next()
            var char = String.fromCharCode(code);
            var value = this.obj.value;
            var mChar = this.mask.charAt(pos); 
            if(pos < 0)
            {
                return;
            }
            
	        if(char != mChar)
	        {
		        if(MaskedTextBox.isMaskChar(mChar) == false)
	            {
	                char = mChar;
	            }
	            else
	            {
                    var ranges = MaskedTextBox.validRange(mChar);
                    var valid = false;
                    
                    for(var i = 0; i < ranges.length; i++) 
                    {
	                    var code = char.charCodeAt(0);
	                    if(MaskedTextBox.inRange(code, MaskedTextBox.ranges.padnum))
	                    {
    	                    code -= 48;
	                    }
            			
                        if(MaskedTextBox.inRange(code, ranges[i])) 
	                    {
                            valid = true;
                            break;
                        }
                    }
                    if(valid == false) 
                    {
	                    return;
                    }
                }
            }		
            var left    = value.substr(0, pos);
            var right   = value.substr(pos + 1, value.length - 1);
            this.obj.value = left + char + right;
         
            this.oCaret.SetPosition(pos+1);
        }    
    },
		
	DoFormatNext: function(value,pos,spacer)
	{
	    var result = -1;
        var size = value.length;
        for(var x=pos;x < size; x++)
        {
            var char  = value.charAt(x);
            if(char == spacer)
            {
                result = x;
                break;
            }
        }
        return result;
	},
	
	DoFormatAddChar : function(char,mask,value, pos, spacer)
	{
        var mChar = mask.charAt(pos);
		if(char != mChar)
		{
    		if(MaskedTextBox.isMaskChar(mChar) == false)
		    {
		        char = mChar;
		    }
		    else
		    {
                var ranges = MaskedTextBox.validRange(mChar);
                var valid = false;
                
                for(var i = 0; i < ranges.length; i++) 
	            {
		            var code = char.charCodeAt(0);
                    if(MaskedTextBox.inRange(code, ranges[i])) 
		            {
                        valid = true;
                        break;
                    }
                }
                if(valid == false) 
	            {
		            char = spacer;
	            }
            }
        }		
        var left    = value.substr(0, pos);
        var right   = value.substr(pos + 1, value.length - 1);
        value = left + char + right;
        return value;
	},
	
	DoFormat: function(t,move)
	{
        var caret = null;
        
        if(move == true)
        {   
            caret     = t.oCaret.GetPosition();
        }
        
		var value 	= t.obj.value;
		var nValue 	= t.DefaultValue();
		var mask	= t.mask;
		
		var size = value.length;
		var pos = 0
		for(var x = 0; x < size; x++)
		{
			nValue = t.DoFormatAddChar(value.charAt(x),mask,nValue, pos, t.spacer); 
			pos = t.DoFormatNext(nValue,pos,t.spacer);
			if(pos < 0)
			    break;
			//pos++;
			
		}

		t.obj.value = nValue;
		if(caret != null)
		{
            t.oCaret.SetPosition(caret);		
        }
	},

	DoFocus: function()
	{
        var pos = this.obj.value.indexOf(this.spacer);
		if(pos >= 0)
		{
			this.oCaret.SetPosition(pos);
		}
	},
	
	DefaultValue: function()
	{
            var str = '';
		    var size = this.mask.length
		    for(var i = 0; i < size; i++) 
		    {
                var chr = this.mask.charAt(i);
                str += MaskedTextBox.isMaskChar(chr) ? this.spacer : chr;
            }
            return str;
    }	    	
});

	