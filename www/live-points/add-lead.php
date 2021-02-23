<!DOCTYPE html>
<html>
    <head>
        <title>Přidat body vedoucímu</title>
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
                <p class="heading">Přidat body vedoucímu</p>
                <form>
                    <div class="form-group">
                        <label for="lead">Vyberte vedoucího:</label>
                        <select class="form-control" name="lead" id="lead">
                            <option disabled selected value>- Člen vedení -</option>
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
            </div>
        </div>
        <script src="./add-lead.js"></script>
    </body>
</html>
