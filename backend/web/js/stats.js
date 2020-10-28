$(document).ready(function() {
    $('#w0-kvdate').on('change', function(){
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/stats.php');
            return false; 
        }
    );
    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/stats.php');
});

function sendAjaxForm(result_form, ajax_form, url) {
    $("#loading").css("display","block");
    $.ajax({
        url:     url, 
        type:     "POST",
        dataType: "html",
        data: jQuery("#" + ajax_form).serialize(),
        success: function(response) {
            $("#result").html(response);
            $("#loading").css("display","none");
        },
        error: function(response) {
            $("#result").html("Ошибка");
        }
    });
}
