/*
*
* The owner of this script is found on http://forum.imasters.com.br/index.php?showtopic=207119, just to make * the copyright :)
*
* Last Change: 03-15-2007
*
*
*/

Object.extend(Event, {
    KEY_HOME:     36,
    KEY_END:      35
});

var MaskedTextBox = Class.create();

/*
* The keycode ranges
*/
MaskedTextBox.ranges = {
    numeric: [48, 57],
    padnum: [96, 105],
    characteres: [65, 90],
    all: [0, 255]
};

MaskedTextBox.inRange = function(n, range) {
    return n >= range[0] && n <= range[1];
};

MaskedTextBox.validRange = function(char) {
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

MaskedTextBox.isMaskChar = function(chr) {
    return MaskedTextBox.validRange(chr) != null;
};

Object.extend(MaskedTextBox.prototype, {
    initialize: function(obj) {
        this.obj = $(obj.ID);
        this.mask = obj.Mask;
        this.fillSpace = obj.fillSpace || '_';
        
         if(/MSIE/.test(navigator.userAgent)){
			this.obj.onkeydown = this.keytest.bindAsEventListener(this);
        	this.obj.onkeypress = Event.stop.bindAsEventListener(this);	
		}
		if(/Netscape|Firefox|Konqueror|Safari|KHTML/.test(navigator.userAgent)){
        	this.obj.onkeypress = this.keytest.bindAsEventListener(this);
		}
        this.obj.onkeyup = Event.stop.bindAsEventListener(this);
        this.obj.onfocus = this.doSelection.bind(this);
        this.obj.onclick = this.doSelection.bind(this);
        
        if(!this.obj.value)
            this.obj.value = this.defaultString();
    },
    
    keytest: function(evt) {
        var e = evt || event;
        var code = e.keyCode || e.which || e.charCode;
     
        switch(code) {
            case Event.KEY_BACKSPACE:
                this.doBackspace();
                break;
            case Event.KEY_DELETE:
                this.doDelete();
                break;
            case Event.KEY_LEFT:
                this.moveCursor(-1);
                break;
            case Event.KEY_RIGHT:
                this.moveCursor(1);
                break;
            case Event.KEY_HOME:
                this.setSelection(0);
                break;
            case Event.KEY_END:
                this.setSelection(this.obj.value.length - 1);
                break;
            case Event.KEY_TAB:
            case Event.KEY_RETURN:
                return true;
            default:
                this.maskTest(code);
        }
        
        Event.stop(e);
    },
    
    doBackspace: function() {
        this.moveCursor(-1);
        this.doDelete();
    },
    
    doDelete: function() {
        var pos = this.getCursor().left;
        
        var left = this.obj.value.substr(0, pos);
        var right = this.obj.value.substr(pos + 1, this.obj.value.length - 1);
        
        this.obj.value = left + this.fillSpace + right;
        this.setSelection(pos);
    },
    
    doSelection: function() {
        var pos = this.getCursor().left;
        
        if(pos == this.obj.value.length)
            pos--;
        
        if(!MaskedTextBox.isMaskChar(this.mask.charAt(pos))) {
            if(!this.moveCursor(1))
                this.moveCursor(-1);
        } else {
            this.setSelection(pos);
        }
    },
    
    moveCursor: function(step, left) {
        var pos = left || this.getCursor().left;
        
        if(step == 0)
            return false;
        
        if(pos == 0 && step < 0)
            return false;
        
        if(pos >= (this.obj.value.length - 1) && step > 0)
            return false;
        
        do {
            pos += step;
        } while(!MaskedTextBox.isMaskChar(this.mask.charAt(pos)) && pos > 0 && pos < this.obj.value.length);
        
        if(!MaskedTextBox.isMaskChar(this.mask.charAt(pos)))
            return false;
        
        this.setSelection(pos);
        return true;
    },
    
    maskTest: function(code) {
        if(MaskedTextBox.inRange(code, MaskedTextBox.ranges.padnum))
            code -= 48;
        
        var pos = this.getCursor().left;
        var chr = this.mask.charAt(pos);
        
        var ranges = MaskedTextBox.validRange(chr);
        var valid = false;
        
        for(var i = 0; i < ranges.length; i++) {
            if(MaskedTextBox.inRange(code, ranges[i])) {
                valid = true;
                break;
            }
        }
        
        if(valid) {
            var left = this.obj.value.substr(0, pos);
            var right = this.obj.value.substr(pos + 1, this.obj.value.length - 1);
            
            this.obj.value = left + String.fromCharCode(code) + right;
            
            var oldpos = pos;
            
            do {
                pos++;
            } while(!MaskedTextBox.isMaskChar(this.mask.charAt(pos)) && pos < this.obj.value.length);
            
            if(MaskedTextBox.isMaskChar(this.mask.charAt(pos)))
                this.setSelection(pos);
            else
                this.setSelection(oldpos);
        }
    },
    
    getCursor: function() {
        var left, right;
        
        if(this.obj.createTextRange) {
            var range;
            
            range = document.selection.createRange().duplicate();
            range.moveEnd("character", this.obj.value.length);
            
            if(!range.text)
                left = this.obj.value.length;
            else
                left = this.obj.value.lastIndexOf(range.text);
            
            range = document.selection.createRange().duplicate();
            range.moveStart("character", -this.obj.value.length);
            
            right = range.text.length;
        } else {
            left = this.obj.selectionStart;
            right = this.obj.selectionEnd;
        }
        
        return {left: left, right: right};
    },
    
    setSelection: function(left, rightPos) {
        var right = rightPos || left + 1;
        
        if(this.obj.createTextRange) {
            var range = this.obj.createTextRange();
            range.moveStart("character", left);
            range.moveEnd("character", right - this.obj.value.length);
            range.select();
        } else {
            this.obj.setSelectionRange(left, right);
        }
    },
    
    defaultString: function() {
        var str = '';
        
        for(var i = 0; i < this.mask.length; i++) {
            var chr = this.mask.charAt(i);
            str += MaskedTextBox.isMaskChar(chr) ? this.fillSpace : chr;
        }
        
        return str;
    }
});
