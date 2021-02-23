$(function () {
    const API_URL = 'http://campis.g6.cz/live-points/live_points_api.php';

    $.get(API_URL + '?action=getLead', (res) => {
        res.forEach((lead) => {
            $('#lead').append(new Option(lead.name, lead.id));
        });

        $('#lead').change(showPoints);
    });

    function showPoints() {
        $('#points').inputFilter(function (value) {
            return /^-?\d*$/.test(value); // Allow digits only, using a RegExp
        });
        $('.points-input').show();
        $('.note-input').show();

        $('#submitPoints').show();
        $('#points').on('change paste keyup', enableSubmitButton);
        $('#submitPoints').click(submitPoints);
    }

    function enableSubmitButton() {
        if ($.isNumeric($('#points').val())) {
            $('#submitPoints').prop('disabled', false);
        } else {
            $('#submitPoints').prop('disabled', true);
        }
    }

    function submitPoints(evt) {
        if ($.isNumeric($('#points').val())) {
            const values = {
                leadId: $('#lead').val(),
                points: $('#points').val(),
                note: $('#note').val(),
            };

            if (values.leadId && values.points) {
                $.post(API_URL, values, (res) => {
                    $('.toast').toast({ delay: 3000 });
                    $('.toast').toast('show');
                    $('#points').val('');
                    $('#note').val('');
                    $('#submitPoints').prop('disabled', true);
                });
            }

            evt.preventDefault();
            evt.stopPropagation();
            evt.stopImmediatePropagation();
        }
    }

    $.fn.inputFilter = function (inputFilter) {
        return this.on('input keydown keyup mousedown mouseup select contextmenu drop', function () {
            if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
            } else if (this.hasOwnProperty('oldValue')) {
                this.value = this.oldValue;
                // this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
            } else {
                this.value = '';
            }
        });
    };
});
