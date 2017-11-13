$(function() {

    if(jQuery().dataTable) {
        // support for filesize ordering
        // Author: Allan Jardine (modified)
        jQuery.fn.dataTable.ext.type.order['column-type-file-size-pre'] = function(data) {
            var matches = data.match( /^(\d+(?:\.\d+)?)\s*([a-z]+)/i );
            var multipliers = {b:  1, bytes: 1, kb: 1000, kib: 1024, mb: 1000000, mib: 1048576, gb: 1000000000, gib: 1073741824};

            if (matches) {
                var multiplier = multipliers[matches[2].toLowerCase()];
                return parseFloat( matches[1] ) * multiplier;
            } else {
                return -1;
            };
        };

        // support for german date format
        jQuery.fn.dataTable.ext.type.order['column-type-date-pre'] = function(data) {
            var matches = data.match( /^([0-3][0-9])\.([0-1]\d)\.((19|20)\d{2})$/i );

            if (matches) {
                return (new Date(matches[2]+'.'+matches[1]+'.'+matches[3])).getTime() / 1000;
            } else {
                return -1;
            };
        };

        // support for status columns
        jQuery.fn.dataTable.ext.type.order['column-type-status-pre'] = function(data) {
            return $(data).find('input').prop('checked') ? 1 : 0;
        };

        // general setup
        $('.searchable').DataTable( {
            "info": false,
            columnDefs: [
                { type: 'column-type-file-size', targets: 'column-type-file-size' },
                { type: 'column-type-date', targets: 'column-type-date' },
                { type: 'column-type-status', targets: 'column-type-status' },
                { "searchable": false, "targets": 'column-type-file-size' },
                { "searchable": false, "targets": 'column-type-status' }
            ]
        });
    }

    // give the forms our look
    $('div.dataTables_filter input').addClass('form-control');
    $('div.dataTables_wrapper select').addClass('form-control d-inline').css('width', 'auto');

    // remove searchbox label
    $('div.dataTables_filter label').contents().filter(function(){
        return (this.nodeType == 3);
    }).remove();

    $("body").on('click', '.clickable', function () {
        window.location = $(this).attr('data-url');
    });

    if(jQuery().ekkoLightbox) {
        $(document).on('click', '[data-toggle="lightbox"]', function (event) {
            event.preventDefault();
            $(this).ekkoLightbox();
        });
    }

    $("body").on('click', '.change-lang', function () {
        $.ajax({
            url: '/setlang',
            type: 'POST',
            data: {
                'code': $(this).attr('data-new-lang')
            },
            dataType:'text',
            success: function() {
                location.reload();
            }
        });
    });
});