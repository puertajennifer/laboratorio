<?php 

include('plantilla_mail.php');

$conn = getCnxDB();
$errorMSG =  "";

ini_set('max_execution_time', 200);

$sql = "SELECT * FROM test_paciente WHERE enviar = 1";
    
foreach ($conn->query($sql) as $row) {
    $id =  $row['id'];
   
    if ($id > 0)
    {
        $errorMSG  = envio_mai($id);        
    }  
}

if ($errorMSG == ""){
    echo "success";
}else{
    echo $errorMSG;
}

