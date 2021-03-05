<?php
include('conectar_bd.php');

require 'vendor/autoload.php';
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


function envio_mai( $id_paciente)
{
    $enviar  = true;
    $errorMSG =  "";

    $mail = new PHPMailer(true);

    $conn = getCnxDB();

    $sql = "SELECT * FROM test_paciente WHERE id = " . $id_paciente;

    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $email = $result['email_paciente'];
    $nombre = $result['nombre_paciente'] . " " . $result['apellido1_paciente'] . ". ";
    $pdfresult = $result['pdf_resultado'];
   
    if ($email == "" ){
        $errorMSG = "El paciente no tiene email asociado, actualice e intente nuevamente.";
        $enviar  = false;
    }  
    else
    {
        if ($pdfresult == "" ){
            $errorMSG = "El paciente no tiene actualizada la informacion de archivo de resultados, por favor verifique.";
            $enviar  = false;
        }  
        else
        {
            $attachemnt = "files/" . $pdfresult;
            //verificar si existe el archivo en el directorio            
            if (!file_exists($attachemnt)) {
                $errorMSG = "No existe archivo de resultados a enviar, por favor verifique.";
                $enviar  = false;
            } 
        } 
    } 
   
    if ($enviar){  
        $message = cuerpo_mail($nombre);

        try {
            //Create a new PHPMailer instance           
            //$mail->SMTPDebug = 3;      
            $mail->IsSMTP();

            $ini = parse_ini_file('appdemocratest.ini');
            $emailFrom = $ini['emailFrom'];
            $nameemailFrom = $ini['nameemailFrom'];
            $username = $ini['Username'];
            $password = $ini['Password'];
            $subject = $ini['subjectMail'];
            
            
            //Configuracion servidor mail
            //$mail->setFrom($emailFrom);
            $mail->setFrom($emailFrom, $nameemailFrom);
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls'; //seguridad
            //$mail->Host = 'smtp.gmail.com'; // servidor smtp
            $mail->Host = $ini['hostmail'];
            //$mail->Port = 587; //puerto
            $mail->Port =$ini['portmail'];
            $mail->Username = $username; //nombre usuario
            $mail->Password = $password; //contraseña
            $mail->addAttachment($attachemnt); 
            $mail->isHTML(true); 
            $mail->CharSet = 'UTF-8'; 
            // Set email format to HTML
            
            //Agregar destinatario           
            $mail->addAddress($email); 
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );  
            
            $str_sql_update = "UPDATE test_paciente SET enviar = 0 , sts_enviomail = ?, fecha_envio = ?  WHERE id = ?" ;
            
            $stmt = $conn->prepare($str_sql_update);

            $stmt->bindParam( 1, $sts_enviomail);
            $stmt->bindParam( 2, $fecha_envio);
            $stmt->bindParam( 3, $id_paciente);

            
            if ($mail->send()) {
                $enviado = true;
                $sts_enviomail = "SI";          
            } else {
                $errorMSG = "NO ENVIADO, intentar de nuevo.";
                $sts_enviomail = "NO"; 
            }  

            $fecha_envio = date("d/m/Y");  
            
            $stmt->execute(); 
        }
        catch (Exception $e) {
            $errorMSG = "No se ha podido enviar el email - Error: {$mail->ErrorInfo}";
        }     
    }

}

function cuerpo_mail($nombre){
    $message  = "<html><body>";
    
    $message .= "<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
    
    $message .= "<tr><td>";
    
    $message .= "<table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='max-width:650px; background-color:#fff; font-family:Verdana, Geneva, sans-serif;'>";
     
    $message .= "<thead>
       </thead>";
     
    $message .= "<tbody>
         <tr>
        <td colspan='4' style='padding:15px;'>
          <p style='font-size:12px;text-align: justify;'>Estimado/a Sr./a, ".$nombre."</p>
          <p style='font-size:12px;text-align: justify;'>Le adjuntamos el resultado de su test. PCR.</p>
          <p style='font-size:12px;text-align: justify;'>Los casos positivos tendrán que ponerse en contacto con su centro de salud para seguir las instrucciones de salud pública.
         </p>
         <p style='font-size:12px;text-align: justify;'><b>Si el resultado ha sido positivo en Covid-19 el laboratorio clínico en cumplimiento de la normativa vigente debe realizar una declaración obligatoria urgente a los servicios de vigilancia epidemiológica de Salud Pública de las CCAA, con el fin de aplicar los protocolos de salud pública del Ministerio de Sanidad.</b>
         <hr />
          <p style='font-size:12px;text-align: justify;'>TIENE EL TRABAJADOR/A OBLIGACIÓN DE COMUNICAR QUE ESTÁ CONTAGIADO/A O TIENE SOSPECHA DE CONTACTO CON EL COVID-19?
         </p>
          <p style='font-size:12px;text-align: justify;'>Sí, la situación actual de emergencia sanitaria derivada del COVID-19 permite que prevalezcan los intereses generales y la salud pública ante el consentimiento individual de cada trabajador/a en relación con sus datos personales (tal y como señala, el informe número 0017/2020 de la Agencia de Protección de Datos)        
         </p>
         <p style='font-size:12px;text-align: justify;'>Por lo tanto, <b>el trabajador o trabajadora que tenga sospecha de contacto con el nuevo coronavirus <u>debe informar a su empleador,</u></b> a fin de salvaguardar, además de su propia salud, la de los demás trabajadores del centro de trabajo, para que se puedan adoptar las medidas oportunas.        
         </p>
          <p style='font-size:12px;text-align: justify;'>Hay que tener en cuenta que, el acceso y tratamiento de estos datos, se deberá minimizar en función de la finalidad, es decir, la empresa podrá tener conocimiento de qué personas trabajadoras están afectadas por Covid-19, pero no debería tener acceso a los informes médicos o conocimiento de otros diagnósticos.        
         </p>
         <hr /> 
          <p style='font-size:12px;text-align: justify;'>Confiamos haya quedado satisfecho con la prestación del servicio y le agradecemos la confianza depositada en Democratest.        
         </p>
          <p style='font-size:12px;text-align: justify;'>DEMOCRATEST, S.L.</p>
         <p style='font-size:10px;text-align: justify;'>Este mensaje es privado y confidencial y solamente para la persona a la que va dirigido. Si usted ha recibido este mensaje por error, no debe revelar, copiar, distribuir o usarlo en ningún sentido. Le rogamos lo comunique al remitente y borre dicho mensaje y cualquier documento adjunto que pudiera contener. No hay renuncia a la confidencialidad ni a ningún privilegio por causa de transmisión errónea o mal funcionamiento.
         Cualquier opinión expresada en este mensaje pertenece únicamente al autor remitente, y no representa necesariamente la opinión de Democratest, a no ser que expresamente se diga y el remitente esté autorizado para hacerlo. Los correos electrónicos no son seguros, no garantizan la confidencialidad ni la correcta recepción de los mismos, dado que pueden ser interceptados, manipulados, destruidos, llegar con demora, incompletos, o con virus. Democratest no se hace responsable de las alteraciones que pudieran hacerse al mensaje una vez enviado.</p>
        </td>
       </tr>       
       </tbody>";
     
    $message .= "</table>";
    
    $message .= "</td></tr>";
    $message .= "</table>";
    
    $message .= "</body></html>";

    return  $message;
}
?>