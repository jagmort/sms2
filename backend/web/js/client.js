$(document).ready(function() {
    $('#result').on('click', '.sms', function() {
        $('<form method="get" action="/sms2/backend/web/index.php"><input type="hidden" name="r" value="site/history"><input type="hidden" name="argus" value="' + $(this).attr('data-argus') + '" /></form>').appendTo('body').submit();
    });   
    $('#branch').on('change', function(){
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/client.php');
        return false; 
    });
    $('#level').on('change', function(){
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/client.php');
        return false; 
    });
    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/client.php');
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
