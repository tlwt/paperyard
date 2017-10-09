$(function() {
    $("body").on('click', '.clickable-row', function () {
        window.location = $(this).attr('data-url');
    });
});