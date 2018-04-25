<?php
    include_once 'simplexslx.php';

    $errorMessage = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            if ($_FILES['excel']['error']) {
                throw new \Exception('Ошибка ' . $_FILES['excel']['error'] .  ': Файл не загружен');
            }

            if (!$_FILES['excel']['tmp_name'] || !is_file($_FILES['excel']['tmp_name'])) {
                throw new \Exception('Ошибка: Не удалось временно сохранить Excel файл');
            }

            print_r(parseFile($_FILES['excel']['tmp_name']));

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
    }

    function parseFile($filepath)
    {
        $result = [];
        if ($xlsx = SimpleXLSX::parse($filepath)) {
           $result = $xlsx->rows();
         } else {
           echo SimpleXLSX::parse_error();
         }
        return $result;
    }
?>
<html>
    <head>
        <title>Excel to XML converter</title>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">

        <link rel="stylesheet" href="assets/main.css">
    </head>
    <body>

        <form enctype="multipart/form-data" method="POST" class="form-send-excel">
            <div class="text-center mb-4">
                <h1 class="h3 mb-3 font-weight-normal"> Excel => XML </h1>
            </div>

            <div class="form-label-group">
                <input type="file" name="excel" class="form-control" id="excelFile">
                <label for="excelFile">Выбери файл:</label>
            </div>
            <p class="error-message"><?= $errorMessage ?></p>
            <input class="btn btn-lg btn-primary btn-block" type="submit"></input>
            <p class="mt-5 mb-3 text-muted text-center">© 2018</p>
        </form>

    </body>
</html>