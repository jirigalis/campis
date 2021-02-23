$(function () {
    const API_URL = 'http://campis.g6.cz/live-points/live_points_api.php';
    const teamContainerIds = [];
    const container = $('#container');
    let teamCount;
    let refreshInterval;
    let reloadPageInterval;

    $.get(API_URL + '?action=teamCount', (res) => {
        teamCount = res;
        generateTeamContainers();
        loadData();
    });

    function loadData() {
        $.getJSON(API_URL, (result) => {
            let i = 1;
            result.forEach((team) => {
                updateTeamContainer(i++, team.name, team.sum);
            });
            refreshTopChildrenData();
            refreshTopLeadData();
            refreshInterval = setInterval(refreshData, 15000);
            reloadPageInterval = setInterval(reloadPage, 60000);
        });
    }

    function reloadPage() {
        $.get(API_URL + '?action=getReloadFlag', (result) => {
            if (result == 1) {
                location.reload();
                $.post(API_URL, { reload: false }, null);
            }
        });
    }

    function refreshData() {
        $.getJSON(API_URL, (result) => {
            let i = 1;
            result.forEach((team) => {
                updateTeamContainer(i++, team.name, team.sum);
            });
            refreshTopChildrenData();
            refreshTopLeadData();
        });
    }

    function refreshTopChildrenData() {
        $.getJSON(API_URL + '?action=getTopChildrenForToday', (result) => {
            updateTopChildren(result);
        });
    }

    function refreshTopLeadData() {
        $.getJSON(API_URL + '?action=getTopLeadForToday', (result) => {
            updateTopLead(result);
        });
    }

    function updateTeamContainer(containerId, teamName, teamPoints) {
        $('#team_container_' + containerId + ' .team-name').html(teamName);
        $('#footer .last-update').html('Datum poslední aktualizace: ' + getCurrentDate());

        let oldPointsVal = parseInt($('#points_' + containerId).text());

        if (isNaN(oldPointsVal)) {
            oldPointsVal = 0;
            $('#points_' + containerId).html(oldPointsVal);
        }

        if (teamPoints != oldPointsVal) {
            const pointsDiff = teamPoints - oldPointsVal;
            let duration = 3;

            if (pointsDiff > 50 && pointsDiff < 99) {
                duration = 5;
            } else if (pointsDiff > 100 && pointsDiff < 199) {
                duration = 7;
            } else if (pointsDiff > 200 && pointsDiff < 299) {
                duration = 10;
            } else if (pointsDiff > 300 && pointsDiff < 399) {
                duration = 12;
            } else if (pointsDiff > 400 && pointsDiff < 499) {
                duration = 15;
            }

            const countUp = new CountUp('points_' + containerId, teamPoints, {
                useGrouping: false,
                duration: duration,
                startVal: oldPointsVal,
            });
            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }
        }
    }

    function generateEmptyTeamContainer(containerId) {
        return $(
            `<div class="team-wrapper" id="team_container_` +
                containerId +
                `">
                <div class="team-name"></div>
                <div class="team-points" id="points_` +
                containerId +
                `"></div>
            </div>`
        );
    }

    function generateTeamContainers() {
        for (let i = 1; i <= teamCount; i++) {
            container.append(generateEmptyTeamContainer(i));
        }
    }

    function updateTopChildren(data) {
        const table = $('#top_children_table');
        table.html('');
        let i = 1;
        if (data.length > 0) {
            $('#top_children_container').show();
        } else {
            $('#top_children_container').hide();
        }
        data.forEach((child) => {
            table.append('<tr><td>' + i++ + '.</td><td>' + child.name + '</td><td>' + child.sum + ' b.</td></tr>');
        });
    }

    function updateTopLead(data) {
        const table = $('#top_lead_table');
        table.html('');
        let i = 1;
        if (data.length > 0) {
            $('#top_lead_container').show();
        } else {
            $('#top_lead_container').hide();
        }
        data.forEach((lead) => {
            table.append('<tr><td>' + i++ + '.</td><td>' + lead.name + '</td><td>' + lead.sum + ' b.</td></tr>');
        });
    }

    function getCurrentDate() {
        const date = new Date();

        const dateStr =
            date.getDate() +
            '. ' +
            (date.getMonth() + 1) +
            '. ' +
            date.getFullYear() +
            ' ' +
            addZero(date.getHours()) +
            ':' +
            addZero(date.getMinutes()) +
            ':' +
            addZero(date.getSeconds());

        function addZero(i) {
            if (i < 10) {
                i = '0' + i;
            }
            return i;
        }

        return dateStr;
    }
});
