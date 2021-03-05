<?php 

include('conectar_bd.php');

require 'vendor/autoload.php';
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());

ini_set('max_execution_time', 200); 

$conn = getCnxDB();
$success = false;
$errorMSG =  "";

$id_lab = $_POST['inputLab']; 
$flg_carga = $_POST['optCarga'];

$dir_subida = 'uploads/';
$archivo_xls = $_FILES['customFile']['name'];

$fichero_subido = $dir_subida . basename($_FILES['customFile']['name']);

if (move_uploaded_file($_FILES['customFile']['tmp_name'], $fichero_subido)) {
    $documento = IOFactory::load($fichero_subido);   
    $hoja = $documento->getActiveSheet();
    $fila_encab = 0;

    if($flg_carga=="c"){
        $sql = "SELECT a.fila_encab FROM plantillaxls a LEFT JOIN laboratorio b on a.id = b.id_plantilla WHERE b.id = ".$id_lab;
        $stmt = $conn->query($sql);
        $result = $stmt->fetch();
        $fila_encab = $result[0];

        if ($fila_encab > 1 )
        {
            $documento->getActiveSheet()->removeRow(1, $fila_encab - 1);
            $hoja = $documento->getActiveSheet();
            $fila_encab = 1; 
        }
    }

    //obtener array con equvalencias de campos en bd/excel 
    $camposequiv = getCamposFromXLS($hoja);   
    $max = $hoja->getHighestRow(); 
    $cont = 0;

    //validar si el valor de la celda corresponde a un encabezado
    $valor = $hoja->getCellByColumnAndRow(1,1)->getValue(); 

    if (es_encabezado_columna($valor))
    {
        for ($fila = 2; $fila <= $max; $fila++)
        {
            $cont = $cont + 1;
            $valorCalculado = $hoja->getCellByColumnAndRow(1,$fila)->getCalculatedValue();
            $valorFormateado = $hoja->getCellByColumnAndRow(1,$fila)->getFormattedValue();
                        
            if( $valorCalculado != null or $valorFormateado != null ) { 
                if( $valor != '' or $valorFormateado != '') {                
                    //AJUSTES:
                    // - VALIDAR DATOS REQUERIDOS ANTES DE CARGAR EXCEL - SI HAY DATOS FALTANTES, NO CARGAR ARCHIVO                   
                    if($flg_carga=="c"){
                        $existe = muestra_existe($conn, $id_lab, $hoja, $fila, $camposequiv, $cont);

                        if (!$existe){
                            insertar_muestra($conn, $id_lab, $hoja, $fila, $camposequiv, $cont);
                        }
                        else{
                            actualizar_muestra($conn, $id_lab, $hoja, $fila, $camposequiv, $cont);
                        }                    
                    }   
                    else{
                        actualizar_resultado_muestra($conn, $id_lab, $hoja, $fila, $camposequiv, $cont);                 
                    }             
                    $success = true; 
                } 
            }               
        }  
    } 
    else {
        $cont = 1;
        $errorMSG = "Error procesando el archivo: verifique que la plantilla pertenezca al laboratorio seleccionado."; 
    }
}  
else {
    $errorMSG = "Error procesando el archivo: no se pudo cargar el archivo seleccionado."; 
}  

if ($cont == 0) {
    $success = false; 
    $errorMSG = "El archivo no contiene registros"; 
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


function es_encabezado_columna($valor_celda)
{
    $encabezado = false;
    $header = strtoupper($valor_celda);

    //ENCABEZADOS MANEJADOS POR ARQUIMEA
    if(preg_match('/(BARCODE)/',$header) == 1)
    {
        return true;
    }
   
    if(preg_match('/(TIPO TEST)/',$header) == 1)
    {
        return true;
    }

    //ENCABEZADOS MANEJADOS POR LIFE LENGTH    
    if(preg_match('/(NOMBRE)/',$header) == 1)
    {
        return true;
    }    

    //ENCABEZADOS MANEJADOS POR LIFE LENGTH    
    if(preg_match('/(Nº DOCUMENTO)/',$header) == 1)
    {
        return true;
    }    
    return $encabezado;
}


function insertar_muestra($conn, $id_lab, $hoja, $fila, $camposequiv, $cont)
{
    try {
        $str_sql_insert = "INSERT INTO test_paciente (codigo_lab, fecha_carga, cedula_paciente, ";
        $str_sql_insert =  $str_sql_insert . "pasaporte_paciente, nombre_paciente, apellido1_paciente, ";
        $str_sql_insert =  $str_sql_insert . "apellido2_paciente, fecha_nac_paciente, sexo_paciente, " ; 
        $str_sql_insert =  $str_sql_insert . "email_paciente, fecha_test, motivo_test, codigo_test, ";
        $str_sql_insert =  $str_sql_insert . "tipo_test, resultado_test, nProtein, sProtein, orfLab, ELISA, ";
        $str_sql_insert =  $str_sql_insert . "PCRMultidiagnostico, sts_enviomail, fecha_envio) ";
        $str_sql_insert =  $str_sql_insert . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ";
        $str_sql_insert =  $str_sql_insert . " ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($str_sql_insert);

        $stmt->bindParam( 1, $codigo_lab);
        $stmt->bindParam( 2, $fecha_carga);
        $stmt->bindParam( 3, $cedula_paciente);
        $stmt->bindParam( 4, $pasaporte_paciente);
        $stmt->bindParam( 5, $nombre_paciente);
        $stmt->bindParam( 6, $apellido1_paciente);
        $stmt->bindParam( 7, $apellido2_paciente);
        $stmt->bindParam( 8, $fecha_nac_paciente);
        $stmt->bindParam( 9, $sexo_paciente);
        $stmt->bindParam( 10, $email_paciente);
        $stmt->bindParam( 11, $fecha_test);
        $stmt->bindParam( 12, $motivo_test);
        $stmt->bindParam( 13, $codigo_test);
        $stmt->bindParam( 14, $tipo_test);
        $stmt->bindParam( 15, $resultado_test);                        
        $stmt->bindParam( 16, $nProtein);
        $stmt->bindParam( 17, $sProtein);
        $stmt->bindParam( 18, $orfLab);
        $stmt->bindParam( 19, $ELISA);
        $stmt->bindParam( 20, $PCRMultidiagnostico);
        $stmt->bindParam( 21, $sts_enviomail);
        $stmt->bindParam( 22, $fecha_envio);

        $codigo_lab = $id_lab;

        $fecha_carga = date("d/m/Y");  

        $cedula_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "cedula_paciente");
        $pasaporte_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "pasaporte_paciente"); 

        if($pasaporte_paciente == " ")
        {
            $pasaporte_paciente = $cedula_paciente;
            $cedula_paciente = " "; 
        }
        
        $nombre_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "nombre_paciente"); 
        $apellido1_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "apellido1_paciente"); 
        $apellido2_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "apellido2_paciente"); 
        $fecha_nac_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "fecha_nac_paciente"); 
        $sexo_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "sexo_paciente"); 
        $email_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "email_paciente"); 
        $fecha_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "fecha_test"); 
        $motivo_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "motivo_test"); 
        $codigo_test = trim(getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "codigo_test")); 
        $tipo_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "tipo_test"); 
        $resultado_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "resultado_test"); 
        $nProtein = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "nProtein"); 
        $sProtein = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "sProtein"); 
        $orfLab = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "orfLab"); 
        $ELISA = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "ELISA"); 
        $PCRMultidiagnostico = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "PCRMultidiagnostico"); 
        $sts_enviomail = " ";   
        $fecha_envio = " ";  

        $stmt->execute();
    }
    catch (Exception $e) {
        $result_oper = "Error insertando" . "- fila:" . $cont;
    }  
    
}

function actualizar_muestra($conn, $id_lab, $hoja, $fila, $camposequiv, $cont)
{
    try {
        $str_sql_update = "UPDATE test_paciente SET " ;         
        $str_sql_update =  $str_sql_update . "codigo_lab = ?, fecha_carga = ?, cedula_paciente = ?, ";
        $str_sql_update =  $str_sql_update . "pasaporte_paciente = ?, nombre_paciente = ?, ";
        $str_sql_update =  $str_sql_update . "apellido1_paciente = ?, apellido2_paciente = ?, ";
        $str_sql_update =  $str_sql_update . "fecha_nac_paciente = ?, sexo_paciente = ?, ";
        $str_sql_update =  $str_sql_update . "email_paciente = ?, fecha_test = ?, ";
        $str_sql_update =  $str_sql_update . "motivo_test = ?, tipo_test = ?, ";
        $str_sql_update =  $str_sql_update . "ELISA = ?, PCRMultidiagnostico = ? ";
        $str_sql_update =  $str_sql_update . "WHERE codigo_test = ? ";
        
        $stmt = $conn->prepare($str_sql_update);

        $stmt->bindParam( 1, $codigo_lab);
        $stmt->bindParam( 2, $fecha_carga);
        $stmt->bindParam( 3, $cedula_paciente);
        $stmt->bindParam( 4, $pasaporte_paciente);
        $stmt->bindParam( 5, $nombre_paciente);
        $stmt->bindParam( 6, $apellido1_paciente);
        $stmt->bindParam( 7, $apellido2_paciente);
        $stmt->bindParam( 8, $fecha_nac_paciente);
        $stmt->bindParam( 9, $sexo_paciente);
        $stmt->bindParam( 10, $email_paciente);
        $stmt->bindParam( 11, $fecha_test);
        $stmt->bindParam( 12, $motivo_test);
        $stmt->bindParam( 13, $tipo_test);
        $stmt->bindParam( 14, $ELISA);
        $stmt->bindParam( 15, $PCRMultidiagnostico);
        $stmt->bindParam( 16, $codigo_test);

        $codigo_lab = $id_lab;

        $fecha_carga = date("d/m/Y");  

        $cedula_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "cedula_paciente");
        $pasaporte_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "pasaporte_paciente"); 

        if($pasaporte_paciente == " ")
        {
            $pasaporte_paciente = $cedula_paciente;
            $cedula_paciente = " "; 
        }
        
        $nombre_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "nombre_paciente"); 
        $apellido1_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "apellido1_paciente"); 
        $apellido2_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "apellido2_paciente"); 
        $fecha_nac_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "fecha_nac_paciente"); 
        $sexo_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "sexo_paciente"); 
        $email_paciente = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "email_paciente"); 
        $fecha_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "fecha_test"); 
        $motivo_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "motivo_test"); 
        $codigo_test = trim(getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "codigo_test")); 
        $tipo_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "tipo_test"); 
        $ELISA = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "ELISA"); 
        $PCRMultidiagnostico = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "PCRMultidiagnostico"); 

        $stmt->execute();
    }
    catch (Exception $e) {
        $result_oper = "Error actualizando muestra" . "- fila:" . $cont;
    }      
}

function actualizar_resultado_muestra($conn, $id_lab, $hoja, $fila, $camposequiv, $cont)
{
    try {
        $str_sql_update = "UPDATE test_paciente SET " ;         
        $str_sql_update =  $str_sql_update . "resultado_test = ?, nProtein = ?, sProtein = ?, ";
        $str_sql_update =  $str_sql_update . "orfLab = ? ";
        $str_sql_update =  $str_sql_update . "WHERE codigo_lab = ? AND pasaporte_paciente = ? ";
        $str_sql_update =  $str_sql_update . "AND codigo_test = ? ";
    
        $stmt = $conn->prepare($str_sql_update);

        $stmt->bindParam( 1, $resultado_test);
        $stmt->bindParam( 2, $nProtein);
        $stmt->bindParam( 3, $sProtein);
        $stmt->bindParam( 4, $orfLab);           
        $stmt->bindParam( 5, $codigo_lab); 
        $stmt->bindParam( 6, $valorident);     
        $stmt->bindParam( 7, $codigo_test);   

        $resultado_test = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "resultado_test"); 
        $nProtein = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "nProtein"); 
        $sProtein = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "sProtein"); 
        $orfLab = getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "orfLab"); 
        $codigo_lab = $id_lab;
        $cedula_paciente = trim(getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "cedula_paciente"));
        $pasaporte_paciente = trim(getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "pasaporte_paciente")); 
        $codigo_test = trim(getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "codigo_test")); 

        if($cedula_paciente == " ")
            $valorident = $pasaporte_paciente;
        else    
            $valorident = $cedula_paciente;

        $stmt->execute();
    }
    catch (Exception $e) {
        $result_oper = "Error actualizando resultado" . "- fila:" . $cont;
    }      
}

function muestra_existe($conn, $id_lab, $hoja, $fila , $camposequiv, $cont)
{
    $existe = false; 
    $codigo_test = trim(getValorEnXLSParaCampo($hoja, $fila , $camposequiv, "codigo_test")); 

    try {
        $sql = "SELECT id FROM test_paciente WHERE codigo_test = '". $codigo_test . "'";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch();
        $id_muestra = $result[0];

        if ($id_muestra > 1 )
        {
            $existe = true; 
        }
    }
    catch (Exception $e) {
        $errorMSG = "Error buscando muestra - fila:" . $cont;
    }  
    
    return $existe;
}

function getCamposFromXLS($hoja) 
{
    //leer la fila 1 para ubicar las columnas equivalentes a cada campo en tabla   
    $camposequiv = array(array());
    $row = 1;
    $lastColumn = $hoja->getHighestColumn();
    $lastColumn++;
    $campos = "";

    for ($column = 'A'; $column != $lastColumn; $column++) {
        $campos = "";
        
        if ($hoja->cellExists($column.$row)) {
            $cell = $hoja->getCell($column.$row);
            $ccv = $cell->getCalculatedValue();
            
            if( $ccv != null ) {
                $header = strtoupper($cell);

                if(preg_match('/(DOCUMENTO)/',$header) == 1)
                {
                    $campos = "cedula_paciente";
                }    

                if(preg_match('/(PASAPORTE)/',$header) == 1)
                {
                    $campos = "pasaporte_paciente";
                }    
                
                if(preg_match('/(NOMBRE)/',$header) == 1)
                {
                    $campos = "nombre_paciente";
                }   

                if(preg_match('/(APELLIDO)/',$header) == 1)
                {
                    $campos = "apellido1_paciente";
                }   

                if(preg_match('/(APELLIDO2)/',$header) == 1)
                {
                    $campos = "apellido2_paciente";
                }  

                if(preg_match('/(FECHA DE NACIMIENTO)/',$header) == 1)
                {
                    $campos = "fecha_nac_paciente";
                } 

                if(preg_match('/(FECHA_NACIMIENTO)/',$header) == 1)
                {
                    $campos = "fecha_nac_paciente";
                }                

                if(preg_match('/(SEXO)/',$header) == 1)
                {
                    $campos = "sexo_paciente";
                }

                if(preg_match('/(GENERO)/',$header) == 1)
                {
                    $campos = "sexo_paciente";
                }

                if(preg_match('/(EMAIL)/',$header) == 1)
                {
                    $campos = "email_paciente";
                }

                if(preg_match('/(FECHA DE EXTRACC)/',$header) == 1)
                {
                    $campos = "fecha_test";
                }

                if(preg_match('/(FECHA_TOMA_MUESTRA)/',$header) == 1)
                {
                    $campos = "fecha_test";
                }

                if(preg_match('/(MOTIVO)/',$header) == 1)
                {
                    $campos = "motivo_test";
                }

                if(preg_match('/(CODE)/',$header) == 1)
                {
                    $campos = "codigo_test";
                }

                if(preg_match('/(CODIGO)/',$header) == 1)
                {
                    $campos = "codigo_test";
                }

                if(preg_match('/(BARCODE)/',$header) == 1)
                {
                    $campos = "codigo_test";
                }

                if(preg_match('/(PCR)/',$header) == 1)
                {
                    $campos = "codigo_test";
                }

                if(preg_match('/(DIGO DE MUESTRA)/',$header) == 1)
                {
                    $campos = "codigo_test";
                }

                if(preg_match('/(DIGODEMUESTRA)/',$header) == 1)
                {
                    $campos = "codigo_test";
                }

                if(preg_match('/(TIPO TEST)/',$header) == 1)
                {
                    $campos = "tipo_test";
                }

                if(preg_match('/(RESULTADO)/',$header) == 1)
                {
                    $campos = "resultado_test";
                }

                if(preg_match('/(NPROTEIN)/',$header) == 1)
                {
                    $campos = "nProtein";
                }

                if(preg_match('/(SPROTEIN)/',$header) == 1)
                {
                    $campos = "sProtein";
                }

                if(preg_match('/(ORFLAB)/',$header) == 1)
                {
                    $campos = "orfLab";
                }    

                if(preg_match('/(ELISA)/',$header) == 1)
                {
                    $campos = "ELISA";
                }

                if(preg_match('/(MULTI-D)/',$header) == 1)
                {
                    $campos = "PCRMultidiagnostico";
                }

               //echo "encabezado:" . $header . "- campo:" . $campos . ";";

               array_push($camposequiv,$campos);             
           }            
       }         
   }    
   return $camposequiv;
}

function getColumnaCampo($array, $campo)
{
    $columna = 0;
    $max = sizeof($array);
    
    for($i = 0; $i < $max; $i++)
    {
        if($array[$i] == $campo)
        {
            $columna = $i;
            break;
        }   
    }   
    return $columna;
}

function getValorEnXLSParaCampo($hoja, $fila, $arraycamposequiv, $campobusq)
{
    $retorno = " ";
    $nro_col = getColumnaCampo($arraycamposequiv, $campobusq);
    
    //echo "campobusq:" . $campobusq . "- nro_col:" . $nro_col . "; ";
    
    if ($nro_col > 0)
    {
        $valor = $hoja->getCellByColumnAndRow($nro_col,$fila)->getCalculatedValue();
        if ($valor != null)
        {
            $retorno = $valor; 
        }   
        else
        {
            $valor = $hoja->getCellByColumnAndRow($nro_col,$fila)->getFormattedValue();
            if ($valor != null)
            {
                $retorno = $valor; 
            }  
            else
            {
                $valor = $hoja->getCellByColumnAndRow($nro_col,$fila)->getValue();
                if ($valor != null)
                {
                    $retorno = $valor; 
                }  
            }
        } 

        if(preg_match('/(FECHA)/', strtoupper($campobusq)) == 1)
        {
           $celda = $valor;            
           
            //if (!DateTime::createFromFormat('Y/m/d H:i:s', $celda))
            if (Date::isDateTime($hoja->getCellByColumnAndRow($nro_col,$fila)))
            {
                $fecha_date = Date::excelToDateTimeObject($valor);
                $fecha_str = date_format($fecha_date, 'Y/m/d H:i:s');
                if(preg_match('/(00:00:00)/', $fecha_str) == 1)
                {
                    $retorno = date_format($fecha_date, 'Y/m/d');
                } 
                else
                {
                    $retorno = $fecha_str;
                }    
            }            
            else
            {
                $retorno = $valor;
            }
           
        }                      
    }
    return $retorno;
}
?>