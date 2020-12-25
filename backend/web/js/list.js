$(document).ready(function() {
    $('#group').on('change', function() {
        $("#tab").val("");
        $("#list").val("");
        $("#submit").attr("value", "0");
        $("#delete").attr("value", "0");
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/list.php');
        return false; 
    });
    $('#tab').on('change', function() {
        $("#submit").attr("value", "0");
        $("#delete").attr("value", "0");
        $("#list").val("");
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/list.php');
        return false; 
    });
    $('#list').on('change', function() {
        $("#submit").attr("value", "0");
        $("#delete").attr("value", "0");
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/list.php');
        return false; 
    })
    $('#add').on('click', function() {
        $("#submit").attr("value", "1");
        $("#delete").attr("value", "0");
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/list.php');
        return false;
    })
    $('#clear').on('click', function() {
        $("textarea").val("");
        return false;
    })
    $(document).on('click', '.contact-delete', function() {
        $("#submit").attr("value", "0");
        $("#delete").attr("value", $(this).attr("cid"));
        sendAjaxForm('result_form', 'ajax_form', '/sms2/send/list.php');
        return false;
    })
    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/list.php');
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

function addgroups(...groups) {
    $('#group').find('option').remove();
    groups.forEach(function(entry) {
        $('#group')
            .append($("<option></option>")
            .attr("value", entry)
            .text(entry)); 
    });
}

function addtabs(...tabs) {
    $('#tab').find('option').remove();
    tabs.forEach(function(entry) {
        $('#tab')
            .append($("<option></option>")
            .attr("value", entry)
            .text(entry)); 
    });
}

function addlists(...lists) {
    $('#list').find('option').remove();
    lists.forEach(function(entry) {
        $('#list')
            .append($("<option></option>")
            .attr("value", entry)
            .text(entry)); 
    });
}
