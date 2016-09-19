var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function randomString() {
    //var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
    var string_length = 6;
    var randomstring = '';
    for (var i=0; i<string_length; i++) {
        var rnum = Math.floor(Math.random() * keyStr.length);
        randomstring += keyStr.substring(rnum,rnum+1);
    }
    //document.randform.randomfield.value = randomstring;
    return randomstring;
}

// for encode and decode

function encode64(input) {
    input = escape(input);
    var output = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;

    do {
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);

        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;

        if (isNaN(chr2)) {
            enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
            enc4 = 64;
        }

        output = output +
            keyStr.charAt(enc1) +
            keyStr.charAt(enc2) +
            keyStr.charAt(enc3) +
            keyStr.charAt(enc4);
        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";
    } while (i < input.length);

    return output;
}

function decode64(input) {
    var output = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;

    // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
    var base64test = /[^A-Za-z0-9\+\/\=]/g;
    if (base64test.exec(input)) {
        alert("There were invalid base64 characters in the input text.\n" +
            "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
            "Expect errors in decoding.");
    }
    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

    do {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }

        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";

    } while (i < input.length);

    return unescape(output);
}


$(function() {

    $('a[rel=tooltip]').tooltip();
    /*$("[data-toggle='tooltip']").tooltip();*/

        //Apply twitter bootstrap alike style to select element
    $('.select2').select2({
        'width':'element',
        'placeholder' : 'Select'
    });

    $('.select2-container').css('width', '100%');


});

/**
 * Create a confirm modal
 * We want to send an HTTP DELETE request
 *
 * @usage  <a href="posts/2" data-method="delete"
 *         	data-modal-text="Are you sure you want to delete"
 *         >
 *
 *
 * @author Steve Montambeault
 * @link   http://stevemo.ca
 *
 */
$(function() {
	var laravel =
	{
		initialize: function()
		{
		    this.methodLinks = $('a[data-method]');
		    this.registerEvents();
		},

		registerEvents: function()
		{
		    this.methodLinks.on('click', this.handleMethod);
		},

		handleMethod: function(e)
		{
		    e.preventDefault();
		    var link = $(this);

		    var httpMethod = link.data('method').toUpperCase();
		    var allowedMethods = ['PUT', 'DELETE'];
		    var extraMsg = link.data('modal-text');


		    var msg  = '<i class="icon-warning-sign modal-icon"></i>&nbsp;Are you sure you want to&nbsp;' + extraMsg;
            var codeRandom = encode64(randomString());
            var txt = '<input autofocus type="text" id="txt" name="txt" class="col-md-6" value='+codeRandom+' />';
            //txt.val('hello');
		    // If the data-method attribute is not PUT or DELETE,
		    // then we don't know what to do. Just ignore.

		    if ( $.inArray(httpMethod, allowedMethods) === - 1 )
		    {
		        return;
		    }

		    bootbox.dialog(msg + txt,
		    [
		    	{
		    		"label": "OK",
		    		"class": "btn-danger",
		    		"callback": function()
		    		{
                        if($('#txt').val() != decode64(codeRandom)){  return;}
		    			var form =
					        $('<form>', {
					            'method': 'POST',
					            'action': link.attr('href')
					        });

					    var hiddenInput =
					        $('<input>', {
					            'name': '_method',
					            'type': 'hidden',
					            'value': link.data('method')
					        });

					    form.append(hiddenInput).appendTo('body').submit();
		    		}
		    	},
		    	{
		    		"label": "Cancel",
		    		"class": "btn-default"
		    	}
		    ],
		    {
		    	"header": "Please Confirm"

		    });
		}
	};

	laravel.initialize();

})();

