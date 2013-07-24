$(document).ready(function(){
    
    $("body").on("click", "button.btn-success", buttonClick);
    $('input').each (function(){
        $(this).attr('id', $(this).attr('name') + '-field');
    });
    
});

var buttonClick = function() {
    
    //alert($(this).text());
    
    var phoneField = $('#mobileNumber-field'),
        idVal = $('#ID-field').val(),
        smsCodeField = $('#smsCode-field'),
        data = {
            mobileNumber: phoneField.val()
        },
        requestType = 0
    ;
    
    if (idVal) {
        data.ID = idVal;
        requestType = 1;
    }
    
    if (smsCodeField.val()) {
        data.smsCode = smsCodeField.val();
    }
    
    var currentField = requestType ? smsCodeField : phoneField;
    
    var currentButton = currentField.parent().find('button');
    
    currentButton.button('loading');
    currentField.popover('destroy');
    
    $.ajax({
        
        dataType: "json",
        url: 'auth.php',
        data: data,
        
        success: function (data) {
            
            currentButton.button('reset');
            
            switch (requestType) {
                
                case 0:
                    
                    currentButton.fadeOut(400, function () {
                        var txtElem = $('<span class="help-inline">ОК, получите СМС с кодом</span>');
                        txtElem.hide();
                        currentField.after(txtElem);
                        txtElem.fadeIn();
                    });
                    
                    //phoneField.closest('.control-group').addClass('success');
                    
                    $('#smsCode-field').closest('.control-group').removeClass('hidden');
                    $('#ID-field').val(data.ID);
                    
                break
                
                case 1:
                    if (data.redirectUri) {
                        
                        currentButton.fadeOut(400, function () {
                            var txtElem = $('<span class="help-inline">ОК, ждите переадресации ...</span>');
                            txtElem.hide();
                            currentField.after(txtElem);
                            txtElem.fadeIn();
                        });
                        
                        setTimeout(function() {location.assign(data.redirectUri)}, 800);
                        
                    }
                break
                
            }
        },
        
        error: function () {
            
            var errField = currentField,
                errText = requestType ? 'Неверный код' : 'Неверный номер'            
            ;
            
            currentButton.button('reset');
            
            errField.popover({
                placement: 'bottom',
                title: 'Ошибка',
                content: errText
            });
            
            errField.popover('show');
        }
        
    });
    
}