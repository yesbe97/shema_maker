<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

</head>
<body>
    <div style="width:50%;margin-right:auto;margin-left: auto;">
        <h1>Inschrijvingen importeren</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel_file">Data set</label>
                <input class="form-control" name="excel_file" type="file" />
            </div>
            <button class="btn btn-primary" name="import">Import</button>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
    </div>
</body>
</html>