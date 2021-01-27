$(document).ready(function() {
    $(document).on('click', '.take', function() {
        if (confirm($(this).attr("title") + '?')) {
            $("#branch").attr("value", $(this).attr("bid"));
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/duty.php');
        }
    });
    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/duty.php');
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
