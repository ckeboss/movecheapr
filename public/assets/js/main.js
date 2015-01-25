$(function() {
    $('#search_form').on('submit', function(e) {
        e.preventDefault();
        $('#results-table').hide();
        $('.loader-box').show();
        $form = $(this);
        $.ajax({
            type: "POST",
            url : $form.attr('action'),
            data: $form.serialize(),
            success: function(response) {
                $('.loader-box').hide();
                $('#results-table').empty();
                $.each(response.data, function(key, value) {
                    var table_row = '<tr><td style="vertical-align: middle;">'+key+' Foot Truck</td><td><dl>';
                    if(value.uhaul) {
                        table_row += '<dt>Uhaul</dt><dd>$'+value.uhaul+' - <a class="btn btn-xs btn-primary" role="button" href="#">Book Now</a></dd>';
                    }
                    if(value.budget) {
                        table_row += '<dt>Budget</dt><dd>$'+value.budget+' - <a class="btn btn-xs btn-primary" role="button" href="#">Book Now</a></dd>';
                    }
                    table_row += '</dl></td></tr>';
                    $('#results-table').append(table_row);
                });
                $('#results-table').show();
            }
        });
    });
});
