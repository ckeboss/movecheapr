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
                    var table_row = '<tr style="text-align: left;"><td style="vertical-align: middle;">'+key+' Foot Truck</td><td style="text-align: right;"><dl>';
                    if(value.uhaul) {
                        table_row += '<dt>Uhaul</dt><dd>$'+value.uhaul.formatMoney(2, '.', ',')+' - <a class="btn btn-xs btn-primary" role="button" href="http://www.uhaul.com">Book Now</a></dd>';
                    }
                    if(value.budget) {
                        table_row += '<dt>Budget</dt><dd>$'+value.budget.formatMoney(2, '.', ',')+' - <a class="btn btn-xs btn-primary" role="button" href="https://www.budgettruck.com">Book Now</a></dd>';
                    }
                    table_row += '</dl></td></tr>';
                    $('#results-table').append(table_row);
                });
                $('#results-table').show();
            }
        });
    });
    
    $('#date').datetimepicker({minDate: moment(), format: 'MM/DD/YYYY', widgetPositioning: {horizontal: 'left'}});
});

//http://stackoverflow.com/questions/149055/how-can-i-format-numbers-as-money-in-javascript
Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };
