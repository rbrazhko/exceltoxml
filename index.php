<?php
    include_once 'simplexslx.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_FILES['excel']['error']) {
            echo 'Ошибка ' . $_FILES['excel']['error'] .  ': Файл не загружен';
        } else {
            echo 'File received';

            $uploaddir = __DIR__ . "/uploads/";
            $uploadedfile = $uploaddir . basename($_FILES['excel']['name']);

            if(move_uploaded_file($_FILES['excel']['tmp_name'], $uploadedfile)) {
//                var_dump(parseFile($uploadedfile));
                print_r(parseFile($uploadedfile));
                echo "The file has been uploaded successfully";
            } else {
                echo 'Ошибка: Не удалось сохранить файл';
            }
        }
    }


    function parseFile($filepath)
    {
        $result = [];
        if ( $xlsx = SimpleXLSX::parse($filepath) ) {
           $result = $xlsx->rows();
         } else {
           echo SimpleXLSX::parse_error();
         }
        return $result;
    }



?>

<form action="index.php" enctype="multipart/form-data" method="POST">
    <input type="file" name="excel"> <br><br>
    <input type="submit">
</form>
