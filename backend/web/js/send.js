var maxlen = 4096;
var timeout = 10000;
var clicks = 0;
var search_count = 0;
var search_current = null;
var search_text = '';
var list = 0;
var active_tab = 0;

// Submit form
function sendAjaxFormFile(result_form, ajax_form, url) {
    var formData = new FormData($("#" + ajax_form)[0]);
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success:function(data) {
          $("#result").html(data); 
        }
    });
}
  
// Clear all contacts' checkboxes
function clearCheckboxes() {
    list = 0;
    var all_checkboxes = $('#tabs input:checkbox');
    all_checkboxes.prop('checked', false);
    all_checkboxes.prop('indeterminate', false);
    all_checkboxes.data('checked', 0);
    var all_options = $('option');
    all_options.prop('selected', false);
    $("#phones").empty();
    $("#file").val('');
    if($("#phones").val() != '' && $("#text").val().length >= 5) {
        $("#btn").prop('disabled', false);
    }
    else $("#btn").prop('disabled', true);
    $("#priority").html("");
    $("#priority").attr("title", "");
}

// Fill phones textarea with contacts' checkboxes
var program = [];
function scanCheckboxes() {
    program = [];
    $("#phones").empty();
    if(list > 0)
        $("#phones").append(list + ": ");
    $('#tabs input:checkbox').each(function () {
        var el = $(this);
        if(el.is(':disabled') != true) {
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
        }
    });
    if($("#phones").val() != '') {
        $("#clr").prop('disabled', false);
        if($("#text").val().length >= 5) {
            $("#btn").prop('disabled', false);
        }
        else $("#btn").prop('disabled', true);
    }
    else {
        if($("#file").val() == '') {
            $("#clr").prop('disabled', true);
        }
        $("#btn").prop('disabled', true);
    }
}

$(document).ready(function() {
    // Get current tab id
    $('.tab-link').each(function () {
        if($(this).attr('class') == 'tab-link current')
            active_tab = $(this).attr('data-tab').substring(4);
    });
    if(active_tab == 0)
        active_tab = $( ".tab-link:first" ).attr('data-tab').substring(4);

    // Add subject to SMS text
    $('#subject').each(function () {
        $(this).on('change', function() {
            var text = $("#text").val();
            var subject = $(this).children("option:selected").attr("text");
            if(text.indexOf(subject) < 0) {
                text = subject + text;
                $("#text").val(text.slice(0, 600));
            }

            // reselect chekboxes
            if(list > 0) {
                var all_checkboxes = $('#tabs input:checkbox');
                all_checkboxes.prop('checked', false);
                all_checkboxes.prop('indeterminate', false);
                all_checkboxes.data('checked', 0);
                var opt = $('option[data-list='+list+']');
                var str = opt.val(); // general contacts
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

                if($( "option:selected", this).val() == 2) { // Эскалация
                    var str = opt.attr('data-esc'); // escalation's contacts
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
                }
                scanCheckboxes();
            }
        });
    });

    // List selection
    $('.list > select').each(function () {
        $(this).on('change', function() {
            var all_checkboxes = $('#tabs input:checkbox');
            all_checkboxes.prop('checked', false);
            all_checkboxes.prop('indeterminate', false);
            all_checkboxes.data('checked', 0);
            list = $("option:selected", this).attr('data-list');
            $("#priority").html($("option:selected", this).html().substring(0, 28));
            $("#priority").attr("title", $("option:selected", this).html());
            var str = this.value; // general contacts
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

            if($( "#subject option:selected" ).val() == 2) { // Эскалация
                var str = $("option:selected", this).attr('data-esc'); // escalation's contacts
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
            }

            scanCheckboxes();
            var alert = $("option:selected", this).attr('data-alert');
            if(alert != '') {
                $('#alert>.msg').html(alert);
                $('#alert')[0].showModal();
            }
        });
    })

    // Phone number 11 symbols only
    $("#single").keyup(
        function(){
            $("#clr").prop('disabled', false);
            if($(this).val().match(/\d/g).length === 11 && ($("#text").val().length >= 5)) {
                $("#btn").prop('disabled', false);
            }
            else {
                $("#btn").prop('disabled', true);
            }
        }
    );

    // File input
    $("#file").change(
        function(){
            $("#clr").prop('disabled', false);
        }
    );

    // Priority change
    $("#priority > input[type='checkbox']").change(
        function(){
            if($(this).prop('checked')) $(this).prop('title', 'Высокий');
            else $(this).prop('title', 'Низкий');
        }
    );

    // Search button
    $("#search_btn").click(
        function(){
            if(search_text === $("#search>input").val()) {
                search_count++;
            }
            else {
                search_text = $("#search>input").val();
                search_count = 0;
            }
            var abbr = $("abbr").filter(function() {
                var reg = new RegExp(search_text, "i");
                return reg.test($(this).text());
            });
            if(abbr.length <= search_count) search_count = 0;
            $(".current > div > div").removeClass("detailed");
            $(".details").hide();
            var id = abbr.eq(search_count).closest('.tab-content').attr('id');
            $('li[data-tab="' + id + '"]').click();
            abbr.eq(search_count).siblings(".details").slideToggle("fast");
            abbr.eq(search_count).parent().addClass("detailed");
            return false;
        }
    );

    // Clear button
    $("#clr").click(
        function(){
            clearCheckboxes();
            $("#clr").prop('disabled', true);
            $("#single").val('');
        }
    );

    // Send button
    $("#btn").click(
        function(){
            if($("#text").val().length >= 5) {
                if($("#single").val() != '') {
                    sendAjaxFormFile('result_form', 'ajax_form', '/sms2/send/single.php');
                    $("#btn").prop('disabled', true);
                    setTimeout(function(){
                        $("#result").html("");
                        $("#single").val("");
                    }, timeout);
                    clearCheckboxes();
                    $("#clr").prop('disabled', true);
                }
                else {
                    if($("#phones").val() != '') {
                        sendAjaxFormFile('result_form', 'ajax_form', '/sms2/send/send.php');
                        $("#btn").prop('disabled', true);
                        setTimeout(function(){
                            $("#result").html("");
                        }, timeout);
                        clearCheckboxes();
                        $("#clr").prop('disabled', true);
                    }
                }
            }
            return false;
        }
    );

    // Change contact checkbox status
    $('#tabs input:checkbox').each(function () {
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

    // Select all dept
    $('.depthead').each(function () {
        var el1 = $(this);
        el1.on('click', function() {
            el = $(this).parent().find('input[type="checkbox"]');
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
    /*setInterval(function() {
        $("#queue").load("/sms2/send/queue.php", function() {
        });
    }, timeout);*/

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
        if (typeof signlen !== 'undefined' || signlen !== null)
            smslen = maxlen - signlen;
        else
            smslen = maxlen;
        $(this).attr('maxlength',smslen);
        $('#count').text(smslen - $(this).val().length);
        if($(this).val().length >= 5) {
            if($("#phones").val() != '') {
                $("#btn").prop('disabled', false);
            }
            else {
                if($("#single").val().length == 11) {
                    $("#btn").prop('disabled', false);
                }
                else {
                    $("#btn").prop('disabled', true);
                }
            }
        }
        else $("#btn").prop('disabled', true);
    });

    // Diff time for textarea
    $("#text").dblclick(function(){
        var text = $(this).val();
        var re1 = /3.+(\d{2})\.(\d{2})\.(\d{2,4})\s+(\d{2}:\d{2})/g;
        var re2 = /4.+(\d{2})\.(\d{2})\.(\d{2,4})\s+(\d{2}:\d{2})/g;
        var found1 = text.match(re1);
        var found2 = text.match(re2);
        if(found1 && found2) {
            var date1 = new Date(found1[0].replace(re1, '20$3-$2-$1 $4').substr(-16));
            var date2 = new Date(found2[0].replace(re2, '20$3-$2-$1 $4').substr(-16));
            var diff = (new Date(date2) - new Date(date1));
            var diffD = Math.floor(diff / 86400000); // days
            var diffH = Math.floor((diff % 86400000) / 3600000); // hours
            var diffM = Math.round(((diff % 86400000) % 3600000) / 60000); // minutes
            var diffS = '(';
            if(diffD > 0) diffS = diffS + diffD + 'д ' + diffH + 'ч ' + diffM + 'м)';
            else diffS = diffS + diffH + 'ч ' + diffM + 'м)';
            var re3 =  /(4.+\d{2}\.\d{2}\.\d{2,4}\s+\d{2}:\d{2}).*/g;
            var result = text.replace(re3, '$1 ' + diffS);
            $(this).val(result);
        }
    });

   // Show contact details
    $("abbr").click(function(e){
        var self = $(this);
        clicks++;
        if (clicks == 1) {
            setTimeout(function(){
                if(clicks == 1) {
                    if(self.parent().hasClass("detailed")) {
                        $(".current > div > div").removeClass("detailed");
                        $(".details").hide();
                    }
                    else {
                        $(".current > div > div").removeClass("detailed");
                        $(".details").hide();
                        self.siblings(".details").slideToggle("fast");
                        self.parent().addClass("detailed");
                    }
                } else {
                    if(self.siblings('input').is(":disabled"))
                        self.siblings('input').removeAttr('disabled');
                    else
                        self.siblings('input').prop('disabled', true);
                }
                clicks = 0;
            }, 300);
        }
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

$(window).bind("load", function() { 
    setTimeout(function(){
        $('li[data-tab="tab-' + active_tab + '"]').click();
    }, 0);
});
