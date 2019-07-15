<?php
    include_once 'library/simplexslx.php';
    include_once 'ToXml/GenerateXmlFile.php';

    $errorMessage = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $generator = new \ExcelToXml\ToXml\GenerateXmlFile();
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

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="assets/main.css">
    </head>
    <body>
    <div class="container">
        <div class="row">
            <div class="col-md text-center mb-4">
                <h1 class="h3 mb-3 font-weight-normal"> Excel => XML </h1>
            </div>
        </div>

        <div class="row">
            <div class="col"></div>
            <div class="col">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-rozetka-tab" data-toggle="tab" href="#nav-rozetka" role="tab" aria-controls="nav-rozetka" aria-selected="true">Rozetka</a>
                        <a class="nav-item nav-link" id="nav-f-ua-tab" data-toggle="tab" href="#nav-f-ua" role="tab" aria-controls="nav-f-ua" aria-selected="false">F.ua</a>
                    </div>
                </nav>
            </div>
            <div class="col"></div>
        </div>

        <div class="row">
            <div class="col"></div>
            <div class="col">
                <p id="error-message" class="error-message"><?= $errorMessage ?></p>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-rozetka" role="tabpanel" aria-labelledby="nav-rozetka-tab">
                        <form enctype="multipart/form-data" method="POST" class="form-send-excel">
                            <div class="text-center mb-4">
                                <a href="assets/rozetka_excel_example.xlsx">Скачать пустой Excel файл (Rozetka)</a>
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
                                <input required type="file" name="excel" class="form-control" id="rozetkaExcelFile">
                                <label for="rozetkaExcelFile">Выбери файл:</label>
                            </div>
                            <input type="hidden" name="converter" value="rozetka">
                            <input class="btn btn-lg btn-primary btn-block" type="submit" onclick="document.getElementById('error-message').remove();"></input>
                        </form>
                    </div>



                    <div class="tab-pane fade" id="nav-f-ua" role="tabpanel" aria-labelledby="nav-f-ua-tab">
                        <form enctype="multipart/form-data" method="POST" class="form-send-excel">
                            <div class="text-center mb-4">
                                <a href="assets/f_ua_excel_example.xlsx">Скачать пустой Excel файл (F.ua)</a>
                            </div>

                            <div class="form-label-group">
                                <input required type="text" name="filename" class="form-control" id="filename">
                                <label for="filename">Имя файла:</label>
                            </div>

                            <div class="form-label-group">
                                <input required type="file" name="excel" class="form-control" id="fUaExcelFile">
                                <label for="fUaExcelFile">Выбери файл:</label>
                            </div>
                            <input type="hidden" name="converter" value="f-ua">
                            <input class="btn btn-lg btn-primary btn-block" type="submit" onclick="document.getElementById('error-message').remove();"></input>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col"></div>
        </div>


        <p class="mt-5 mb-3 text-muted text-center">© <?php echo date('Y'); ?></p>
    </div>

    </body>
</html>