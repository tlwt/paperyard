(function($) {

    const body = $('body');

    body.on('click', '#confirm-all-outbox', function () {

        //const items = $('#outbox-files li');
        //items.find('button i').removeClass('fa-check').addClass('fa-spin').addClass('fa-spinner');

    });

    body.on('click', '[data-toggle="lightbox"]', function (event) {
        // block li
        event.stopPropagation();
        // href execution
        event.preventDefault();
        // show
        $(this).ekkoLightbox();
    });

    /*
    $.fn.conformView = function() {

        const body = $('body');
        let outbox_items = [];
        let inbox_items = [];

        body.on('click', '#save-document', function () {
            // current form
            const form = $('#document-form');
            // get all the inputs into an array
            const inputs = form.find('input');

            // get an associative array of the values
            const values = {};
            inputs.each(function () {
                values[this.name] = $(this).val();
            });

            // make the request
            $.ajax({
                type: "PATCH",
                url: form.attr('action'),
                dataType: "json",
                data: values,
                success: function (data) {
                    // convert as dataType somehow does not work
                    const result = $.parseJSON(data);
                    // display message for every error
                    for (const key in result) {
                        if (result.hasOwnProperty(key)) {
                            $.notify({
                                message: result[key]
                            }, {
                                type: (result[key] === 'Success' ? 'success' : 'danger'),
                                z_index: 1231,
                            });
                        }
                    }
                },
                complete: updateLists
            });
        });


        body.on('click', '#outbox-files li .actions button', function (event) {
            // block li
            event.stopPropagation();

            const item = $(this).parents('li:first')
            item.css('background-color', 'rgba(76, 189, 116, 0.65)');
            item.animate({
                height: 0
            }, 500, function () {
                $(this).remove();
            });
        });

        body.on('click', '#confirm-all-outbox', function () {

            const items = $('#outbox-files li');
            items.css('background-color', 'rgba(76, 189, 116, 0.65)');
            items.animate({
                height: 0
            }, 500, function () {
                $(this).remove();
            });

        });

        body.on('click', '#outbox-files li, #inbox-files li', function () {
            // show
            $('#document-edit-modal').modal('toggle', $(this));
        });

        body.on('click', '#inbox-files li', function () {
            // show
            $('#document-edit-modal').modal('toggle', $(this));
        });

        $('#document-edit-modal').on('show.bs.modal', function (event) {
            // datepicker modal calls this too
            if (event.relatedTarget === undefined) {
                return;
            }

            // clicked on list item and modal self
            const list_item = $(event.relatedTarget)
            const modal  = $(this);

            // outbox or inbox
            const list_type = $(list_item).parent().attr('id')
            const item = (()=> {
                switch (list_type) {
                    case 'outbox-files':
                        return outbox_items[list_item.attr('data-index')];
                        break;
                    case 'inbox-files':
                        return inbox_items[list_item.attr('data-index')];
                        break;
                    default:
                        event.preventDefault();
                }
            })();

            // fill
            modal.find('#document-subject').val(item['subject']);
            modal.find('#document-company').val(item['company']);
            modal.find('#document-tags').val(item['tags']);
            modal.find('#document-recipient').val(item['recipient']);
            modal.find('#document-price').val(item['price']);
            modal.find('#document-date').val(item['date']);
            modal.find('#document-pages').text(item['pages']);
            modal.find('#document-size').text(item['size']);
            modal.find('form').attr('action', '/latest/' + item['identifier']);
        });

        const loadOutboxItems = function() {
            $.get( "/latest/outbox", function( data ) {
                // convert
                outbox_items = JSON.parse(data);
                // dom
                displayOutboxItems();
            });
        };

        const displayOutboxItems = function() {
            // init
            const list = $('#outbox-files');
            // clear but template
            list.find('li[class!="hidden"]').remove();

            // "random" list item as template
            const element_template = list.find('li').first().clone().removeClass('hidden');

            // count
            $('#outbox-count').text(outbox_items.length);

            outbox_items.forEach(function(item, index) {
                // setup
                const element = element_template.clone();
                const preview_link = element.find('a');
                const gallery_id ='gallery-' + item['hash'];

                // information
                element.attr('data-index', index);
                element.find('.desc .title').text(item['subject']);
                element.find('.desc small').text(item['company'] + ' - ' + item['recipient']);
                element.find('.value div').text(item['oldFilename']);
                element.find('.value strong').text(item['date']);

                // preview
                preview_link.attr('href', getThumbnailLink(item['identifier']));
                preview_link.attr('data-gallery', gallery_id);

                // add pages to preview if multipage
                if (item['pages'] > 1) {
                    for (i = 1; i < item['pages']; i++) {
                        let preview_gallery = element.find('div[data-toggle="lightbox"]').clone();
                        preview_gallery.attr('data-gallery', gallery_id);
                        preview_gallery.attr('data-remote', getThumbnailLink(item['identifier'], i));
                        preview_link.after(preview_gallery);
                    }
                } else {
                    preview_link.next().remove();
                }

                // dom
                list.append(element);
            });
        };

        const loadInboxItems = function() {
            $.get( "/latest/inbox", function( data ) {
                // convert
                inbox_items = JSON.parse(data);
                // dom
                displayInboxItems();
            });
        };

        const displayInboxItems = function() {
            // init
            const list = $('#inbox-files');
            // clear but template
            list.find('li[class!="hidden"]').remove();

            // "random" list item as template
            const element_template = list.find('li').first().clone().removeClass('hidden');

            // count
            $('#inbox-count').text(inbox_items.length);

            inbox_items.forEach(function(item, index) {
                // setup
                const element = element_template.clone();
                const preview_link = element.find('a');
                const gallery_id ='gallery-' + item['hash'];

                // information
                element.attr('data-index', index);
                element.find('.desc .title').text(item['oldFilename']);
                element.find('.desc small').text([item['subject'], item['company'], item['recipient']].join(' - '));
                element.find('.value div').text(item['date']);

                // preview
                preview_link.attr('href', getThumbnailLink(item['identifier']));
                preview_link.attr('data-gallery', gallery_id);

                // add pages to preview if multipage
                if (item['pages'] > 1) {
                    for (i = 1; i < item['pages']; i++) {
                        let preview_gallery = element.find('div[data-toggle="lightbox"]').clone();
                        preview_gallery.attr('data-gallery', gallery_id);
                        preview_gallery.attr('data-remote', getThumbnailLink(item['identifier'], i));
                        preview_link.after(preview_gallery);
                    }
                } else {
                    preview_link.next().remove();
                }

                // dom
                list.append(element);
            });
        };

        const updateLists = function() {
            loadOutboxItems();
            loadInboxItems();
        };

        const getThumbnailLink = function(identifier, page = 0, resolution = 200) {
            return '/thumbnail/' + identifier + '/' + page + '/' + resolution;
        }

        updateLists();

    }();
    */

}(jQuery));
