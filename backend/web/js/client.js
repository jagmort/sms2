$(document).ready(function() {
    $('#result').on('click', '.sms', function() {
        console.log("click");
        $('<form method="get" action="/sms2/backend/web/index.php"><input type="hidden" name="r" value="site/history"><input type="hidden" name="argus" value="' + $(this).attr('data-argus') + '" /></form>').appendTo('body').submit();
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
