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
    }, 60000);
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
            TableExport(document.getElementsByTagName("table"), {
                headers: true,                      // (Boolean), display table headers (th or td elements) in the <thead>, (default: true)
                footers: false,                      // (Boolean), display table footers (th or td elements) in the <tfoot>, (default: false)
                formats: ["xlsx", "csv"],    // (String[]), filetype(s) for the export, (default: ['xlsx', 'csv', 'txt'])
                filename: "History",                     // (id, String), filename for the downloaded file, (default: 'id')
                bootstrap: true,                   // (Boolean), style buttons using bootstrap, (default: true)
                exportButtons: true,                // (Boolean), automatically generate the built-in export buttons for each of the specified formats (default: true)
                position: "top",                 // (top, bottom), position of the caption element relative to table, (default: 'bottom')
                ignoreRows: null,                   // (Number, Number[]), row indices to exclude from the exported file(s) (default: null)
                ignoreCols: null,                   // (Number, Number[]), column indices to exclude from the exported file(s) (default: null)
                trimWhitespace: false,               // (Boolean), remove all leading/trailing newlines, spaces, and tabs from cell text in the exported file(s) (default: false)
                RTL: false,                         // (Boolean), set direction of the worksheet to right-to-left (default: false)
                sheetname: "History"                     // (id, String), sheet name for the exported spreadsheet, (default: 'id')
              });
        },
        error: function(response) {
            $("#result").html("Ошибка");
        }
    });
}
