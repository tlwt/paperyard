$(function() {
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