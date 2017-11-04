// add .searchable to the table and input
// add .searchable-ignore to columns which shouldn't be searched
$(document).ready(function() {
    var $rows = $('table.searchable tbody tr');
    $('input.searchable').keyup(function() {
        var val = '(?=.*' + $.trim($(this).val()).split(/\s+/).join(')(?=.*') + ')',
            reg = RegExp(val, 'i');
        $rows.show().filter(function() {
            text = [];
            $(this).children(':not(".searchable-ignore")').each(function(index, element) { text.push($(element).text()); });
            return !reg.test(text.join(' '));
        }).hide();
    });
});
