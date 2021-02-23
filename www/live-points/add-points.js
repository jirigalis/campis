$(function () {
    const API_URL = 'http://campis.g6.cz/live-points/live_points_api.php';

    $('.close-button').click(hideInfo);
    $('.info-button').click(showInfo);

    $.get(API_URL + '?action=getTeams', (res) => {
        res.forEach((team) => {
            $('#team').append(new Option(team.number + ' - ' + team.name, team.id));
        });

        $('#team').change(loadChildren);
    });

    function loadChildren() {
        const teamId = $('#team').val();
        $.get(API_URL + '?action=getChildren&teamId=' + teamId, (res) => {
            $('#children').find('option').remove().end();
            res.forEach((child) => {
                $('#children').append(new Option(child.name, child.id));
            });
        });

        $('.child-input').show();
        showPoints();
    }

    function showPoints() {
        $('#points').inputFilter(function (value) {
            return /^-?\d*$/.test(value); // Allow digits only, using a RegExp
        });
        $('.points-input').show();
        $('.note-input').show();
        $('.team-only').show();
        $('.team-only').change(handleTeamOnlyCheck);

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
            if (document.querySelector('#team_only').checked) {
                const values = {
                    teamId: $('#team').val(),
                    points: $('#points').val(),
                    note: $('#note').val(),
                };

                if (values.teamId && values.points) {
                    $.post(API_URL, values, (res) => {
                        $('.toast').toast({ delay: 3000 });
                        $('.toast').toast('show');
                        $('#points').val('');
                        $('#note').val('');
                        $('#submitPoints').prop('disabled', true);
                    });
                }
            } else {
                const values = {
                    childId: $('#children').val(),
                    points: $('#points').val(),
                    note: $('#note').val(),
                };

                if (values.childId && values.points) {
                    $.post(API_URL, values, (res) => {
                        $('.toast').toast({ delay: 3000 });
                        $('.toast').toast('show');
                        $('#points').val('');
                        $('#note').val('');
                        $('#submitPoints').prop('disabled', true);
                    });
                }
            }

            evt.preventDefault();
            evt.stopPropagation();
            evt.stopImmediatePropagation();
        }
    }

    function handleTeamOnlyCheck() {
        if (document.querySelector('#team_only').checked) {
            $('#children').prop('disabled', true);
            $('.child-input').hide();
        } else {
            $('#children').prop('disabled', false);
            $('.child-input').show();
        }
    }

    function showInfo() {
        $('#info-box').show();
    }

    function hideInfo() {
        $('#info-box').hide();
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
