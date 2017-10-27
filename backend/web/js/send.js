var program = [];

function clearCheckboxes() {
    var all_checkboxes = jQuery(':checkbox');
    all_checkboxes.prop('checked', false);
    all_checkboxes.prop('indeterminate', false);
    all_checkboxes.data('checked', 0);
    var all_options = jQuery('option');
    all_options.prop('selected', false);
    $("#phones").empty();
}

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
    $('select').each(function () {
        $(this).on('change', function() {
            var all_checkboxes = jQuery(':checkbox');
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
       });
    })
    $("#clr").click(
        function(){
            clearCheckboxes();
        }
    );
    $("#btn").click(
        function(){
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/send.php');
            $("#btn").prop('disabled', true);
            setTimeout(function(){
                $("#result").html("");
                $("#btn").prop('disabled', false);
            }, 5000);
            clearCheckboxes();
            return false; 
        }
    );

    $('input:checkbox').each(function () {
        var el = $(this);
        //$(this).on('change', scanCheckboxes);
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

    setInterval(function() {
        $("#queue").load("/sms2/send/queue.php", function() {
        });
    }, 5000);

    $('ul.tabs li').click(function(){
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
    });

    $("#text").keyup(function(){
        $("#count").text((600 - $(this).val().length));
    });
    $("abbr").click(function(){
        $(".current > div").removeClass("detailed");
        $(".details").hide();
        $(this).parent().children(".details").slideToggle("fast");
        $(this).parent().addClass("detailed");

    });
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
