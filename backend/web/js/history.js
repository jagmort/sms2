$(document).ready(function() {
    jQuery('#w0-kvdate').on('change', function(){
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/history.php');
            return false; 
        }
    );
    setInterval(function() {
        if($("#refresh input").prop("checked")) {
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/history.php');
        }
    }, 10000);
    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/history.php');
});
function sendAjaxForm(result_form, ajax_form, url) {
    jQuery.ajax({
        url:     url, 
        type:     "POST",
        dataType: "html",
        data: jQuery("#" + ajax_form).serialize(),
        success: function(response) {
            $("#result").html(response);
        },
        error: function(response) {
            $("#result").html("Ошибка");
        }
    });
}
