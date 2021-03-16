<?php 

include('conectar_bd.php');

$conn = getCnxDB();
$success = false;

$errorMSG =  "";
$dir_subidapdf = 'files/';
$dir_subidaxls = 'uploads/';

ini_set('max_execution_time', 200);

borrar_directorio_carga($dir_subidapdf);
borrar_directorio_carga($dir_subidaxls);

limpiar_data($conn );

$success = true;

if ($success && $errorMSG == ""){
    echo "success";
}else{
     if($errorMSG == ""){
         echo "Ha ocurrido un error procesando el archivo.";
     } else {
         echo $errorMSG;
     }
}

function borrar_directorio_carga($dir){    
    $count = 0;

    // ensure that $dir ends with a slash so that we can concatenate it with the filenames directly
    $dir = rtrim($dir, "/\\") . "/";

    // use dir() to list files
    $list = dir($dir);

    // store the next file name to $file. if $file is false, that's all -- end the loop.
    while(($file = $list->read()) !== false) {
        if($file === "." || $file === "..") continue;
        if(is_file($dir . $file)) {
            unlink($dir . $file);
            $count++;
        } elseif(is_dir($dir . $file)) {
            $count += borrar_directorio_carga($dir . $file);
        }
    }
}

function limpiar_data($conn){
    $sql = "TRUNCATE TABLE test_paciente";
    
    $stmt = $conn->query($sql);
}
