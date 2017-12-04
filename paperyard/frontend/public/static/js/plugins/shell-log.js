$(function() {

    // get the newest 40 entries
    lastId = 0;
    updateShell(40,0);

    // start timer to refresh every second
    setInterval(function(){ updateShell(40,lastId); }, 2000);
    function updateShell(count, since) {
        $.ajax({
            url: '/shell/' + count + '/' + since,
            type: 'GET',
            dataType:'JSON',
            success: function(data) {
                $('#shell-connection').hide();
                data.forEach(function (t) {
                    lastId = t['id']
                    $('<span/>', {
                        'class':'shell-entry',
                        'text': t['logProgram'] + ' [' + t['created_at'] + '] ' + t['logContent'],
                    }).append("<br>").prependTo('#shell-log')
                })
            },
            error: function() {
                $('#shell-connection').show();
            }
        });

        // remove every but the first 40 items
        $('#shell-log span:gt(39)').remove()
    }
});