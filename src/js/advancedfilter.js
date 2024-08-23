var restoreFiltering = function () {
    $(".tablefilter").each(function (index) {
        var columnType = $(this).attr("data-type");
        if (columnType == "date") {
            columnProcessed = $(this).attr("data-column");
            end = $(this).attr("data-end");
        } else {
            columnProcessed = $(this).attr("name");
        }
        $(this).val(localStorage.getItem(columnProcessed));
        console.log(columnProcessed + " restored " + localStorage.getItem(columnProcessed));
    });
}

function getIndexByName(colName, aoColumns) {
    for (column of aoColumns) {
        if (column.data == colName) {
            return indexIwant = column.idx;
        }
    }
}

function updateFiltering(input, columnName, gridId) {
    var dataTable = $('#' + gridId).DataTable();
    if (dataTable.context.length) {
        var aoColumns = dataTable.context[0].aoColumns;
    } else {
        console.log(dataTable);
        return;
    }

    var colIndex = getIndexByName(columnName, aoColumns);
    dataTable.column(colIndex).search($(input).val());

    dataTable.draw();

    if ($(input).val().length == 0) {
        unsetFilterLabel(columnName);
        $(input).removeClass('tablefilter');
        $(dataTable.column(colIndex).nodes()).removeClass('filtered');
        aoColumns[colIndex]['sClass'] = '';
    } else {
        setFilterLabel(columnName);
        $(input).addClass('tablefilter');
        $(dataTable.column(colIndex).nodes()).addClass('filtered');
        aoColumns[colIndex]['sClass'] = 'filtered';
    }

}

/**
 * 
 * @param {type} columnName
 * @return {undefined}
 */
function setFilterLabel(columnName) {
    var column = $("[name='" + columnName + "']");
    var columnType = "";
    if (column.length == 0) {
        var columnOd = $("[name='" + columnName + "-od']");
        var columnDo = $("[name='" + columnName + "-do']");
        if (columnOd.length && columnDo.length) {
            var odValue = $(columnOd).val();
            var doValue = $(columnDo).val();

            if (odValue.length) {
                if (doValue.length) {
                    columnType = "date-oddo";
                } else {
                    columnType = "date-od";
                }
            } else {
                columnType = "date-do";
            }
        }
    } else {
        columnType = $(column).attr("data-type");
        var valueToFound = $(column).val();
    }
    var label = "";
    localStorage.setItem(columnName, valueToFound);
    switch (columnType) {
        case 'bool':
            if ((valueToFound == 1) || (valueToFound == "on")) {
                label = '<i class="fas fa-toggle-off"></i>';
            } else {
                label = '<i class="fas fa-toggle-on"></i>';
            }
            break;
        case 'date-od':
            label = formatDate(new Date(odValue)) + " ... ";
            break;
        case 'date-do':
            label = " ... " + formatDate(new Date(doValue));
            break;
        case 'date-oddo':
            label = formatDate(new Date(odValue)) + " - " + formatDate(new Date(doValue));
            break;
        case 'pillbox-id':
            var labelParts = [];
            for (valueId of valueToFound.split(",")) {
                var labelPart = column.closest('div').find("[data-value='" + valueId + "']").text();
                labelParts.push(labelPart);
            }
            label = labelParts.join(",");
            break;
        default:
            label = valueToFound;
            break;
    }
    $("span[data-labelfor='" + columnName + "']").empty().append(" <strong>" + label + "</strong>");
    $("span[data-labelfor='" + columnName + "']").parent().addClass('filtering');
}

/**
 * 
 * @param {type} columnName
 * @return {undefined}
 */
function unsetFilterLabel(columnName) {
    $("span[data-labelfor='" + columnName + "']").empty().append(" ");
    $("span[data-labelfor='" + columnName + "']").parent().removeClass('filtering');
}


/**
 * 
 * @return {Array|getFilterColumns.columnsToCheck}
 */
function getFilterColumns() {
    var columnsToCheck = new Array();
    var columnProcessed = "";
    $(".tablefilter").each(function (index) {
        var end = "";
        var columnType = $(this).attr("data-type");
        if (columnType == "date") {
            columnProcessed = $(this).attr("data-column");
            end = $(this).attr("data-end");
        } else {
            columnProcessed = $(this).attr("name");
        }
        var valueToFound = $(this).val();
        columnsToCheck.push({name: columnProcessed, value: valueToFound, type: columnType, end: end});
//        console.log($(this).attr("name") + " " + $(this).val() + " " + $(this).attr("data-type"));
    });
    return columnsToCheck;
}

/**
 * 
 * @param {type} columns
 * @param {type} column
 * 
 * @return {String}
 */
function dateRange(columns, column) {
    var range = "";
    for (filterColumn of columns) {
        if ((filterColumn.name == column) && (filterColumn.type == "date")) {
            range += filterColumn.end;
        }
    }
    return range;
}

function getOdTime(columns, column) {
    for (filterColumn of columns) {
        if ((filterColumn.name == column) && (filterColumn.type == "date") && (filterColumn.end == "od")) {
            var odTime = new Date(filterColumn.value);
        }
    }
    return odTime;
}

function getDoTime(columns, column) {
    for (filterColumn of columns) {
        if ((filterColumn.name == column) && (filterColumn.type == "date") && (filterColumn.end == "do")) {
            var doTime = new Date(filterColumn.value);
            doTime.setTime(doTime.getTime() /*+ 86400000*/);
        }
    }
    return doTime;
}

function formatDate(date) {
    var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;
    return day + ". " + month + ". " + year;
}

function highlightColumn(filterColumn) {
    console.log(filterColumn);
}

