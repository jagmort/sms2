var maxlen = 600;
var timeout = 5000;

// Submit form
function sendAjaxForm(result_form, ajax_form, url) {
    $.ajax({
        url:     url, 
        type:     "POST",
        dataType: "html",
        data: $("#" + ajax_form).serialize(),
        success: function(response) {
            $("#result").html(response);
        },
        complete: function(response) {
            $("#btn").prop('disabled', false);
        },
        error: function(response) {
            $("#result").html("Ошибка");
        }
    });
}

// Clear all contacts' checkboxes
function clearCheckboxes() {
    var all_checkboxes = $(':checkbox');
    all_checkboxes.prop('checked', false);
    all_checkboxes.prop('indeterminate', false);
    all_checkboxes.data('checked', 0);
    var all_options = $('option');
    all_options.prop('selected', false);
    $("#phones").empty();
}

// Fill phones textarea with contacts' checkboxes
var program = [];
function scanCheckboxes() {
    program = [];
    $("#phones").empty();
    $('input:checkbox').each(function () {
        var el = $(this);
        switch(el.data('checked')) {
            case 2:
                program.push(el.val());
                $("#phones").append(el.val() + "; ");
                break;
            case 1:
                program.push(el.val());
                $("#phones").append(el.val() + "-; ");
                break;
            default:
        }
    });
}

$(document).ready(function() {

    // List selection
    $('select').each(function () {
        $(this).on('change', function() {
            var all_checkboxes = $(':checkbox');
            all_checkboxes.prop('checked', false);
            all_checkboxes.prop('indeterminate', false);
            all_checkboxes.data('checked', 0);
            var str = this.value;
            var arr = str.split(',');
            arr.forEach(function(item, i, arr) {
                if(item.substr(-1, 1) != '-') {
                    $("#phone" + item).prop('checked', true);
                    $("#phone" + item).data('checked', 2);
                }
                else {
                    $("#phone" + item.slice(0, -1)).prop('indeterminate', true);
                    $("#phone" + item.slice(0, -1)).data('checked', 1);
                }
            });
            scanCheckboxes();
            var alert = $("option:selected", this).attr('data-alert');
            if(alert != '') {
                $('#alert>.msg').html(alert);
                $('#alert')[0].showModal();
            }
       });
    })

    // Clear button
    $("#clr").click(
        function(){
            clearCheckboxes();
        }
    );

    // Send button
    $("#btn").click(
        function(){
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/send.php');
            $("#btn").prop('disabled', true);
            setTimeout(function(){
                $("#result").html("");
            }, timeout);
            clearCheckboxes();
            return false; 
        }
    );

    // Change contact checkbox status
    $('input:checkbox').each(function () {
        var el = $(this);
        el.on('change', function() {
            switch(el.data('checked')) {
                case 2:
                    el.data('checked', 1);
                    el.prop('indeterminate',true);
                    el.prop('checked', false);
                    break;
                case 1:
                    el.data('checked', 0);
                    el.prop('indeterminate', false);
                    el.prop('checked', false);
                    break;
                default:
                    el.data('checked', 2);
                    el.prop('indeterminate', false);
                    el.prop('checked', true);
            }
            scanCheckboxes();
        });
    });

    // Get queue
    setInterval(function() {
        $("#queue").load("/sms2/send/queue.php", function() {
        });
    }, timeout);

    // Switch tabs
    $('ul.tabs li').click(function(){
        if(this.className.indexOf('current') < 0) {
            var tab_id = $(this).attr('data-tab');

            $('ul.tabs li').removeClass('current');
            $('.tab-content').removeClass('current');
            $('.tab-content').removeClass('current');
            $('.list').removeClass('current');

            $(this).addClass('current');
            $("#" + tab_id).addClass('current');
            $("#list-" + tab_id).addClass('current');

            $(".current > div").removeClass("detailed");
            $(".details").hide();
        }
    });

    // Chars to go
    $("#text").keyup(function(){
        $('#count').text(maxlen - $(this).val().length);
    });

    // Show contact details
    $("abbr").click(function(){
        $(".current > div").removeClass("detailed");
        $(".details").hide();
        $(this).parent().children(".details").slideToggle("fast");
        $(this).parent().addClass("detailed");

    });

    // Edit contact dialog
    $(".details > span").click(function(){
        var id = $(this).attr('data-id');
        var tab = $(this).attr('data-tab');
        var identity = $('#identity').val();
        $.ajax({
            type: 'POST',
            url: '/sms2/send/contact.php',
            data: 'id=' + id + '&authkey=' + identity + '&tab=' + tab,
            success: function(data){
                $('#edit').html(data);
                $('#edit')[0].showModal();
            }
        });
    });

    // Add contact dialog
    $(".addcontact").click(function(){
        var tab = $(this).attr('data-tab');
        var identity = $('#identity').val();
        $.ajax({
            type: 'POST',
            url: '/sms2/send/addcontact.php',
            data: 'authkey=' + identity + '&tab=' + tab,
            success: function(data){
                $('#add').html(data);
                $('#add')[0].showModal();
            }
        });
    });
});

$(window).on("beforeunload", function() {
    if ($("#btn").prop('disabled') == true) {
        return "Идёт отправка SMS";
    }
})
