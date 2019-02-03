$(document).ready(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.hot-up, .hot-down').on('click', function() {
        var t = $(this);
        var url = $('form').data('url');
        var id = t.siblings('.hot').data('id');
        var value = parseInt(t.siblings('.hot').data('value'));
        var action = (t.is('.hot-up')) ? 'up' : 'down';

        if ((value < 4) && (action == 'up') || (value > 0) && (action == 'down')) {
            value = (action == 'up') ? value + 1 : value - 1;
            console.log(url, id, value);
            $.ajax({
                url: url,
                type: 'POST',
                data: {'id': id, 'value': value},
                beforeSend: function() {
                    t.parent('.cell-hot').addClass('show');
                },
                success: function(result){
                    t.parent('.cell-hot').removeClass('show');
                    t.siblings('.hot').data('value', value);
                    t.siblings('.hot').removeClass().addClass('hot hot-' + value);
                }
            });
        }
    });

    
});