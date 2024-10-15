<?php

$uploaddir = __DIR__ . '/../uploads/';
if (!file_exists($uploaddir))
    mkdir($uploaddir, 0777);

$uploadfile = $uploaddir . basename($_FILES['file']['name']);

if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    echo "Файл корректен и был успешно загружен.\n";
} else {
    echo "Возможная атака с помощью файловой загрузки!\n";
}

// echo 'Некоторая отладочная информация:';
// print_r($_FILES);

?>