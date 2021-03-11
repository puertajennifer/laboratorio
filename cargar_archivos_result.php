<?php 

include('conectar_bd.php');

$conn = getCnxDB();
$success = false;
$subidos = false;
$errorMSG =  "";

$id_lab = $_POST['inputLab']; 

$dir_subida = 'files/';

$count = 0;
$exito = 0;

ini_set('max_execution_time', 200);

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $cant_archivos_upload = sizeof($_FILES['archivospdf']['name']);
    //echo "cant_archivos_upload:" . $cant_archivos_upload;
    
    ini_set('max_file_uploads', $cant_archivos_upload);

    foreach ($_FILES['archivospdf']['name'] as $i => $name) {
        if (strlen($_FILES['archivospdf']['name'][$i]) > 1) {
            if (move_uploaded_file($_FILES['archivospdf']['tmp_name'][$i], $dir_subida.$name)) {
                //echo "archivo " . $dir_subida.$name . " subido con exito;";
                $count++;      
                $subidos = true;         
            }
        }
    }

    if ($subidos)
    {
        $errorMSG = actualizarbd_pdfresultados($dir_subida, $id_lab, $conn);  
        if($errorMSG == ""){
            $success = true;
        }
    }    
}

if ($success && $errorMSG == ""){
    echo "success";
}else{
     if($errorMSG == ""){
         echo "Ha ocurrido un error procesando el archivo.";
     } else {
         echo $errorMSG;
     }
}

function actualizarbd_pdfresultados($path, $id_lab, $conn){
    $countpdf = 0;    
    $countupdate = 0;    
    $pdfupdate = "";
    $msgerror = "";

    //OBTENER LISTADO DE PACIENTES EN BD PARA EL LABORATORIO SELECCIONADO
    $sql = "SELECT * FROM test_paciente WHERE codigo_lab = ".$id_lab;
    
    foreach ($conn->query($sql) as $row) {
        $codigo_test =  $row['codigo_test'];
        //print "codigo_test:" . $row['codigo_test'] . "\t";       

        if ($codigo_test !== "")
        {
            $dir = opendir($path);
            $files = array();
            $countpcrfile = 0;

            //recorrer el directorio de carga para ubicar el pdf con el nombre de la muestra
            while ($current = readdir($dir)){
                if( $current != "." && $current != "..") {
                    if(!is_dir($path.$current)) {
                        if (strpos($current,".pdf") == true)
                        {
                            $countpdf = $countpdf + 1; 
                            //echo "countpdf " .  $countpdf . "-";
                            //echo "current " .  $current . "-";
                                          
                            if(preg_match('/('.  $codigo_test . ')/', $current) == 1)
                            {                                
                                //SI EL ARCHIVO CONTIENE EL CODIGO DEL TEST:
                                //echo "encontrado pdf:" . $current;                            
                                $pdfupdate = $current;
                                
                                $countpcrfile = $countpcrfile + 1;                           
                                //echo " countpcrfile " .  $countpcrfile . "-";
                            }   
                        }
                    }
                }
            }
        } 

        //actualzar registro en bd
        if ($countpcrfile > 1 )
        {
            $pdfupdate = "Archivo duplicado " . $codigo_test . ". Favor corregir.";
        }
        //cuando no haya pdf para una muestra, actualizar mensaje en pdf resultado
        if ($countpcrfile == 0 )
        {
            $pdfupdate = "No existe PDF " . $codigo_test . ". Favor corregir.";
        }
    
        try {
            $str_sql_update = "UPDATE test_paciente SET " ;         
            $str_sql_update =  $str_sql_update . "pdf_resultado = ? ";
            $str_sql_update =  $str_sql_update . "WHERE codigo_lab = ? AND codigo_test = ? ";
        
            $stmt = $conn->prepare($str_sql_update);
    
            $stmt->bindParam( 1, $pdfupdate);
            $stmt->bindParam( 2, $id_lab);
            $stmt->bindParam( 3, $codigo_test);
    
            $stmt->execute();
            $countupdate = $countupdate + 1;
        }
        catch (Exception $e) {
            $msgerror = "Error actualizando archivo de resultado" . " - codigo muestra:" . $codigo_test . ".";
        }   
    }   

    if ($countpdf == 0)
    {
        $msgerror = "No existe informacion para actualizar.";
    }
   
    if ($countupdate == 0)
    {
        $msgerror = "No se actualizaron registros.";
    }

   
    return $msgerror;
}
