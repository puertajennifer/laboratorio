<?php 

include('conectar_bd.php');

$conn = getCnxDB();
$success = false;
$subidos = false;
$errorMSG =  "";

$id_lab = $_POST['inputLab2']; 
$dir_subida = 'files/';

ini_set('max_execution_time', 200);

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(copy($_FILES['archivospdf']['tmp_name'], $dir_subida.$_FILES['archivospdf']['name']))
    {
       //obtenemos datos de nuestro ZIP
        $nombre = $_FILES["archivospdf"]["name"];
        $ruta = $_FILES["archivospdf"]["tmp_name"];
        $tipo = $_FILES["archivospdf"]["type"];

        $ext = pathinfo($nombre, PATHINFO_EXTENSION);

        switch ($ext)
        {
            case 'zip':
                // Función descomprimir 
                $zip = new ZipArchive;
                if ($zip->open($ruta) === TRUE) 
                {
                    //función para extraer el ZIP
                    $extraido = $zip->extractTo($dir_subida);                    
                    $zip->close();

                    $from = $dir_subida . substr($nombre,0,strlen($nombre)-4);
                    $to = $dir_subida;                  
                    
                    $dir = opendir($from);
                                
                    //Recorro el directorio para leer los archivos que tiene
                    while(($file = readdir($dir)) !== false){
                        //Leo todos los archivos excepto . y ..
                        if(strpos($file, '.') !== 0){
                            //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
                            copy($from.'/'.$file, $to.'/'.$file);
                        }
                    }
                    $subidos = true;
                }
                break;                     
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
