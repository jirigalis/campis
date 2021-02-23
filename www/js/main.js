$(document).ready(function () {
    $('.flash').click(function () {
        $('div.flash').hide('slow');
    });

    // add parser through the tablesorter addParser method
    $.tablesorter.addParser({
        // set a unique id
        id: 'category',
        is: function (s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function (s) {
            // format your data for normalization
            return s
                .replace(/Starší chlapci/, 5)
                .replace(/Starší dívky/, 4)
                .replace(/Mladší chlapci/, 3)
                .replace(/Mladší dívky/, 2)
                .replace(/Nejmladší chlapci/, 1)
                .replace(/Nejmladší dívky/, 0);
        },
        // set type, either numeric or text
        type: 'numeric',
    });

    $('table.tablesorter').tablesorter({
        headers: {
            4: {
                sorter: 'category',
            },
        },
    });
});

function hideColumn(col) {
    $('.' + col).toggle('fast');
    $('#' + col).prop('checked', !$('#' + col).prop('checked'));
}

function printMode(aaa) {
    hideColumn('rc');
    hideColumn('adress');
    hideColumn('age');
    hideColumn('category');
    hideColumn('team');
    $('#printMode').prop('checked', !$('#printMode').prop('checked'));
    $('th.name').toggle('fast');
}

function printTable() {
    //$()
}
