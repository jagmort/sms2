$(document).ready(function() {
    $('#result').on('click', '.id', function() {
        $('<form method="get" action="/sms2/backend/web/index.php"><input type="hidden" name="r" value="site/index"><input type="hidden" name="uid" value="' + $(this).text() + '" /></form>').appendTo('body').submit();
    });   
    $('#w0-kvdate').on('change', function() {
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/history.php');
        return false; 
    });
    setInterval(function() {
        if($("#refresh input").prop("checked")) {
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/history.php');
        }
    }, 60000);
    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/history.php');
});

function sendAjaxForm(result_form, ajax_form, url) {
    $("#export").attr("href", '/sms2/send/history-csv.php?' + $("#" + ajax_form).serialize());
    $("#loading").css("display","block");
    $.ajax({
        url:     url, 
        type:     "POST",
        dataType: "html",
        data: $("#" + ajax_form).serialize(),
        success: function(response) {
            $("#result").html(response);
            $("#loading").css("display","none");
        },
        error: function(response) {
            $("#result").html("Ошибка");
        }
    });
}
