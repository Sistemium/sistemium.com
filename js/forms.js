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
    
    $.ajax({
        
        dataType: "json",
        url: 'auth.php',
        data: data,
        
        success: function (data) {
            
            switch (requestType) {
                
                case 0:
                    phoneField.popover('destroy');
                    phoneField.parent().find('button').hide();
                    //phoneField.closest('.control-group').addClass('success');
                    phoneField.after('<span class="help-inline">ОК, получите СМС с кодом</span>');
                    $('#smsCode-field').closest('.control-group').removeClass('hidden');
                    $('#ID-field').val(data.ID);
                break
                
                case 1:
                    smsCodeField.popover('destroy');
                    location.replace('bs/tp');
                break
                
            }
        },
        
        error: function () {
            
            var errField = requestType ? smsCodeField : phoneField,
                errText = requestType ? 'Неверный код' : 'Неверный номер'            
            ;
            
            errField.popover({
                placement: 'bottom',
                title: 'Ошибка ввода',
                content: errText
            });
            
            errField.popover('show');
        }
        
    });
    
}