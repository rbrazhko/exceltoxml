<?php
    include_once 'library/simplexslx.php';
    include_once 'toXml/GenerateXmlFile.php';

    $errorMessage = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $generator = new GenerateXmlFile();
            $generator->generate();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
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
                <a href="assets/excel_example.xlsx">Скачать пустой Excel файл</a>
            </div>

            <div class="form-label-group">
                <input required type="text" name="companyName" class="form-control" id="companyName" value="Smuzi Market">
                <label for="companyName">Название компании:</label>
            </div>

            <div class="form-label-group">
                <input required type="text" name="brand" class="form-control" id="brand">
                <label for="brand">Бренд:</label>
            </div>

            <div class="form-label-group">
                <input required type="file" name="excel" class="form-control" id="excelFile">
                <label for="excelFile">Выбери файл:</label>
            </div>
            <p id="error-message" class="error-message"><?= $errorMessage ?></p>
            <input class="btn btn-lg btn-primary btn-block" type="submit" onclick="document.getElementById('error-message').remove();"></input>
            <p class="mt-5 mb-3 text-muted text-center">© 2018</p>
        </form>

    </body>
</html>