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

function borrar_directorio_carga($dir_subida){
    $files = glob($dir_subida . '/*'); //obtenemos todos los nombres de los ficheros
    foreach($files as $file){
        if(is_file($file))
        unlink($file); //elimino el fichero
    }
}

function limpiar_data($conn){
    $sql = "TRUNCATE TABLE test_paciente";
    
    $stmt = $conn->query($sql);
}
