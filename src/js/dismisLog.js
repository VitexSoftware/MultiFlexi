function dismisLog(logRowID, button) {
    var dater = new Date();
    $.ajax({
        url: "ajax2db.php?class=SpojeNet%5CSystem%5CDashboarder",
        type: "POST",
        data: {
            "data": {
                [ "row_" + logRowID ]: {
                    "resolved": dater.toISOString().split('T')[0]
                }
            },
            "action": "edit"
        }
    }).done(function (response) { //
        $("#row_" + logRowID).css("filter","brightness(85%)");
        $(button).parent().text('✓').css('color','green').css('font-size','xx-large').css('padding','1px');
    });
}
