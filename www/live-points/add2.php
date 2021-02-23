<!DOCTYPE html>
<html>
    <head>
        <title>Přidat rychlé body</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta charset="utf-8" />
        <script src="./jquery.js"></script>
        <script src="./bootstrap-4.5.0-dist/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="./add-points.css" />
        <link rel="stylesheet" href="./bootstrap-4.5.0-dist/css/bootstrap.min.css" />
        <script
            src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
            integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
            crossorigin="anonymous"
        ></script>
    </head>
    <body>
        <div class="container-fluid">
            <div id="container">
                <p class="heading">Přidat body</p>
                <form>
                    <div class="form-group">
                        <label for="team">Vyberte oddíl:</label>
                        <select class="form-control" name="team" id="team">
                            <option disabled selected value>- Oddíl -</option>
                        </select>
                    </div>
                    <div class="form-check team-only">
                        <input type="checkbox" class="form-check-input" value="1" name="team_only" id="team_only">
                        <label class="form-check-label" for="team_only">Přidat body celému oddílu</label>
                    </div>
                    <div class="form-group child-input">
                        <label for="child">Osoba:</label>
                        <select class="form-control" name="child" id="children">
                            <option disabled selected value>- Dítě -</option>
                        </select>
                    </div>
                    <div class="form-group points-input">
                        <label for="points">Body:</label>
                        <input type="number" class="form-control" placeholder="Počet bodů" id="points" />
                    </div>
                    <div class="form-group note-input">
                        <label for="note">Poznámka:</label>
                        <input type="text" class="form-control" placeholder="Poznámka" id="note" />
                    </div>

                    <button type="button" class="btn btn-primary btn-block" id="submitPoints" disabled>
                        Přidat body
                    </button>
                </form>
            </div>
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="mr-auto">Body úspěšně uloženy</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div id="footer">
                <div class="powered-by">Powered by UPOZ information labs &copy; 2020</div>
                <div class="info"><input class="info-button" type="button" value=" i "></div>
            </div>
        </div>
        <div id="info-box">
            <input type="button" class="close-button" value=" X ">
            <strong>Plusové body</strong>
            <ul>
                <li>Příkladné chování: 10 b.</li>
                <li>Pomoc kamarádovi: 5 - 10 b.</li>
                <li>Výjimečný výkon ve hře: 5 b.</li>
                <li>Zpívání u táboráku: 5 b.</li>
                <li>Úklid v táboře: 7 b.</li>
                <li>Příprava dřeva: 7 b.</li>
                <li>Motivace oddílu: 5 b.</li>
                <li>Pomoc vedoucímu: 5 b.</li>
            </ul>
            <strong>Mínusové body</strong>
            <ul>
                <li>Sprostá slova: -10 b.</li>
                <li>Neuposlechnutí vedoucího: -10 b.</li>
                <li>Ztráty a zapomínání věcí: -5 b.</li>
                <li>Neuklízení po sobě: -5 b.</li>
                <li>Narušování a nevěnování se programu: -6 b.</li>
                <li>Vyrušování na nástupu: -5 b.</li>
                <li>Nesplnění služebních povinností: -10 b.</li>
                <li>Vyhýbání se hygieně/rozcvičce: -5 b.</li>
            </ul>
        </div>
        <script src="./add-points.js"></script>
    </body>
</html>
