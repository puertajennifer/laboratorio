<?php

include('plantilla_mail.php');

$id_paciente = 0;
$enviado = false;
$errorMSG =  "";

if(isset($_POST['id']))
{
    $id_paciente = $_POST['id'];

    $errorMSG  = envio_mai($id_paciente);        
}

if ($errorMSG == ""){
    echo "success";
}
else{
    echo $errorMSG;    
}
?>