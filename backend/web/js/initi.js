const brunches = {bsh:"Республика Башкортостан", chv:'Филиал в Чувашской Республике', kir:'Кировский филиал', mel:'Филиал в Республике Марий Эл', mrd:'Филиал в Республике Мордовия', nzg:'Нижегородский филиал', orb:'Оренбургский филиал', pnz:'Пензенский филиал', smr:'Самарский филиал', srt:'Саратовский филиал', tts:'Филиал в Республике Татарстан', udm:'Филиал в Удмуртской Республике', uln:'Ульяновский филиал'};

const text = '1. Начало\n2. Кр.3(п.13.4.2)\n3. #time#\n4. \n5. #address#\n6. СПД. #dev# FTTB\n7. ШПД #ports#/#clients#\n8. #argus#\n9. ';

$(document).ready(function() {
    jQuery('#w0-kvdate').on('change', function(){
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/initi.php');
            return false; 
    });
    jQuery('#brunch').on('change', function(){
            $('#node').find('option').remove();
            $("#node").append(`<option selected="selected">%%</option>`);
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/initi.php');
            return false; 
    });
    jQuery('#node').on('change', function(){
            sendAjaxForm('result_form', 'ajax_form', '/sms2/send/initi.php');
            return false; 
    });

    sendAjaxForm('result_form', 'ajax_form', '/sms2/send/initi.php');
});
$(document).on('change', 'input[id="all"]', function() {
    $('input[id^="row"]').not(this).prop('checked', this.checked);
    createText();
});

$(document).on('change', 'input[id^="row"]', function() {
    createText();
});

function createText() {
    var brunch = '', ip = '', time = '', argus = '', address = '';
    var dev = 0, person = 0, legal = 0;
    $('input[id^="row"]:checked').each(function () {
        brunch = $(this).closest('td').siblings().eq(0).html();
        ip += $(this).closest('td').siblings().eq(2).html() + ', ';
        time = $(this).closest('td').siblings().eq(3).html();
        a = $(this).closest('td').siblings().eq(4).find('a').html();
        if(a) argus += a + ', ';
        dev += Number($(this).closest('td').siblings().eq(5).html()) + 1;
        person += Number($(this).closest('td').siblings().eq(6).html());
        legal += Number($(this).closest('td').siblings().eq(7).html());
        a = $(this).closest('td').siblings().eq(9).html();
        if(a) address += a + '; ';
    });
    $('#text').val(text);
    $('#text').val($('#text').val().replace(/#time#/, time.slice(0, -3)));
    address = brunches[brunch] + '; ' + address.slice(0, -2);
    $('#text').val($('#text').val().replace(/#address#/, address));
    $('#text').val($('#text').val().replace(/#argus#/, argus.slice(0, -2)));
    if(dev > 4) p6 = 'Недоступно ' + dev + ' коммутаторов';
    else p6 = 'Недоступно ' + dev + ' коммутатора';
    if(dev == 1) p6 = 'Недоступен коммутатор';

    $('#text').val($('#text').val().replace(/#dev#/, p6));
    ports = dev * 24;
    $('#text').val($('#text').val().replace(/#ports#/, ports));
    clients = person + legal;
    $('#text').val($('#text').val().replace(/#clients#/, clients));
}

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

function addnodes(...nodes) {
    $('#node').find('option').remove();
    $("#node").append(`<option>%%</option>`);
    nodes.forEach(function(entry) {
        $('#node')
            .append($("<option></option>")
            .attr("value", entry)
            .text(entry)); 
    });
}
