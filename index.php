<?php

error_reporting(E_ALL);

// speed things up with gzip plus ob_start() is required for csv export
if(!ob_start('ob_gzhandler'))
	ob_start();

header('Content-Type: text/html; charset=utf-8');

include('lazy_mofo.php');

$max_upload = ini_get('max_file_uploads');

echo "
<!DOCTYPE html>
<html>
<head>
	<title>DEMOCRATEST</title>
	<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'>
    <meta charset='utf-8'>
    <meta http-equiv='content-type' content='text/html; charset=utf-8' />
	<link rel='stylesheet' type='text/css' href='style.css'>
	<link rel='stylesheet' href='css/bootstrap.css'>
	<link rel='stylesheet' href='css/animate.css'>	
</head>
<body>
	<script type='text/javascript' src='js/form_carga-scripts.js'></script>
	<script type='text/javascript' src='js/form_actualizacion-scripts.js'></script>
	<script src='js/bootstrap.min.js' crossorigin='anonymous'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js'></script>

	<div id='wait' style='display: none; width: 100%; height: 100%; top: 100px; left: 0px; position: fixed; z-index: 10000; text-align: center;'>
	<img src='images/ajax-loader.gif' width='45' height='45' alt='Loading...' style='position: fixed; top: 50%; left: 50%;' />
</div>
"; 

// enter your database host, name, username, and password
$ini = parse_ini_file('appdemocratest.ini');

$db_host = $ini['host'];
$db_name = $ini['database'];
$db_user = $ini['user'];
$db_pass = $ini['pass'];


// connect with pdo 
try {
	$dbh = new PDO("mysql:host=$db_host;dbname=$db_name;", $db_user, $db_pass);
}
catch(PDOException $e) {
	die('pdo connection error: ' . $e->getMessage());
}

echo "

<script>
$(document).ready(function() {
	$(document).ready(function () { 
		$(document).ajaxStart(function () {
			   $('#wait').show();
		   });
		   $(document).ajaxStop(function () {
			   $('#wait').hide();
		   });
		   $(document).ajaxError(function () {
			   $('#wait').hide();
		   });   
	   });
});
</script>

<script>
	function show(){		
		document.getElementById('wait').style.display = 'block';		
	}

	function hide(){
		document.getElementById('wait').style.display = 'none';		
	}

	function blockearbtn(){		
		document.getElementById('form2-submit').disabled=true;	
	}

	function desblockearbtn(){
		document.getElementById('form2-submit').disabled=false;	
	}
</script>


<script>
	$(document).ready(function() {
		$('#form_carga').submit(function (e) { 
			e.preventDefault();	
			$('#form-submit').attr('disabled', true);						

			var parametros=new FormData($(this)[0]);
			$.ajax({
				url: 'cargarxls.php',
				type: 'post',
				data: parametros,
				contentType: false,
				processData: false,
				success: function(text){
                    if (text == 'success'){
                        alert('Archivo procesado satisfactoriamente');
						window.location='index.php';
                    } else {
                        formError();
                        submitMSG(false,text);
                    }
					$('#form-submit').attr('disabled', false);
				}
			});
			return false;
		});
	});
</script>

<script>
	$(document).ready(function() {
		$('#form_actualizacion').submit(function (e) { 
			blockearbtn();
			show();
			e.preventDefault();	

			var numFiles = document.getElementById('archivospdf').files.length;
			var maximo = document.getElementById('maxupload').value;			
			var iteraciones = Math.ceil(numFiles / maximo);			

			if (iteraciones == 0)
			{
				iteraciones = 1;
			}

			var i = 0;
			var cont = 1;

			do {
				var parametros = new FormData();
				var idlab = $('#inputLab2').val();
				parametros.append('inputLab',idlab);	

				var k = 0;
								
				for (var j = i; j < i + maximo; j++)
				{
					parametros.append('archivospdf[]', document.getElementById('archivospdf').files[j]);					
					k = j;
					if (k >= numFiles)
					{
						break;
					}
				}				

				$.ajax({
					url: 'cargar_archivos_result.php',
					async: false,
					type: 'post',
					data: parametros,
					contentType: false,
					processData: false,
					success: function(text){
						if (text == 'success'){													
							window.location='index.php';
						} else {
							formError();
							submitMSG2(false,text);
						}														
					}
				});

				i = i + k;
				cont++;
				
			} while (cont <= iteraciones);

			alert('Proceso culminado');
			window.location='index.php';
			desblockearbtn();			
			hide();	
			return false;			
		});		
	});
</script>
";

echo "
<div class='container-fluid' >
	<div class='header'>
		<div class='col-md-12'>
			<img alt='Bootstrap Image Preview' src='images/image001.png'>
		</div>
		<div class='col-md-12'>
			<br/>
		</div>		
	</div>
	<div class='header'>
		<div class='col-md-4'>
			<h2>
				Excel
			</h2>
			<p>
				<form role='form' enctype='multipart/form-data' name='form_carga' id='form_carga' 
				data-toggle='validator' class='shake' action = 'cargarxls.php' method='post'>
					<div class='form-group'>						 
						<label for='inputLab'>
							Laboratorio
						</label>
						<select class='form-control' id='inputLab' name='inputLab' required>
								<option value=''>Seleccione</option>";
								$stmt = $dbh->prepare('SELECT * FROM laboratorio');
								$stmt->execute();
								
								while($row=$stmt->fetch(PDO::FETCH_ASSOC))
								{
									extract($row);
									echo "<option value='";
									echo $id; 
									echo "'>";
									echo $nombre; 
									echo "</option>";				
								}			
					echo "</select>
					</div>
					<div class='form-group'>
						<input type='file' accept='.xls,.xlsx' class='form-control-file'  id='customFile' name='customFile' required/>						
					</div>
					<div class='form-group'>			
						<div class='form-check col-sm-4'>
							<input class='form-check-input' type='radio' name='optCarga' id='rdCarga' value='c' checked/>Cargar
							<div class='help-block with-errors'></div>
							<input class='form-check-input' type='radio' name='optCarga' id='rdActualizar' value='a'/>Actualizar
							<div class='help-block with-errors'></div>
						</div>			
						<button type='submit' id='form-submit' class='btn btn-democratest'>
							Procesar
						</button>							
					</div>	
					<div class='row'>
						<div id='msgSubmit' class='h5 text-center hidden'>Archivo procesado</div>			
						<div class='clearfix'></div>
					</div>	
				</form>
			</p>
			
		</div>
		<div class='col-md-4'>
			<h2>
				PDF
			</h2>
			<p>
				<form role='form' enctype='multipart/form-data' name='form_actualizacion' id='form_actualizacion' 
				data-toggle='validator' class='shake' >
					<div class='form-group'>						 
						<label for='inputLab'>
							Laboratorio
						</label>
						<select class='form-control' id='inputLab2' name='inputLab2' required>
								<option value=''>Seleccione</option>";
								$stmt = $dbh->prepare('SELECT * FROM laboratorio');
								$stmt->execute();
								
								while($row=$stmt->fetch(PDO::FETCH_ASSOC))
								{
									extract($row);
									echo "<option value='";
									echo $id; 
									echo "'>";
									echo $nombre; 
									echo "</option>";				
								}			
					echo "</select>
					</div>
					<div class='form-group'>
						<input type='file' class='form-control-file' id='archivospdf' accept='.pdf' multiple='' required/>											
					</div>
					<div class='form-group'>											
						<button type='submit' id='form2-submit' class='btn btn-democratest'>
							Procesar
						</button>
						<input id='maxupload' name='maxupload' type='hidden' value="; echo $max_upload ; echo ">	
						<label> Máximo "; echo $max_upload ; echo " archivos por directorio</label>							
					</div>	
					<div class='row'>
						<div id='msgSubmit2' class='h5 text-center hidden'>Archivo procesado</div>			
						<div class='clearfix'></div>						
					</div>
				</form>
			</p>			
		</div>
		<div class='col-md-12'>
			<hr/>
			<a href='javascript:cerrar_jornada()' class='btn btn-democratest' >Cerrar Jornada</a>
			<script>
				function cerrar_jornada(){
					var opcion = confirm('Esta a punto de eliminar toda la data ¿Desea continuar?');
					if (opcion == true) {
						$.ajax({
							url: 'cerrar_jornada.php',
							type: 'post',						
							dataType:'html',
							asycn:false, 
							success: function(text){
								if (text == 'success'){                               
									window.location='index.php';
									alert('Jornada cerrada satisfactoriamente');
								} else {
									formError();
									submitMSG(false,text);
								}
							}
						});
					}                     
				}
			</script>

			<a href='javascript:enviarmail_batch()' class='btn btn-democratest'>Enviar emails en Lote</a>
			<script>
				function enviarmail_batch(){
					var opcion = confirm('¿Desea enviar por email los resultados de los registros seleccionados?');
					if (opcion == true) {
						$.ajax({
							url: 'enviomail_masivo.php',
							type: 'post',						
							dataType:'html',
							asycn:false, 
							success: function(text){
								if (text == 'success'){                               
									window.location='index.php';
									alert('Correos enviados satisfactoriamente');
								} else {
									formError();
									submitMSG(false,text);
								}
							}
						});
					}                     
				}
			</script>
			<hr/>
		</div>
	</div>
</div>
";

// create LM object, pass in PDO connection, see i18n folder for country + language options 
$lm = new lazy_mofo($dbh, 'es-es');


// table name for updates, inserts and deletes
$lm->table = 'test_paciente';


// identity / primary key for table
$lm->identity_name = 'id';

// hide delete and edit links
$lm->grid_delete_link = "";
$lm->grid_edit_link = "";

// optional, make friendly names for fields
$lm->rename['enviar'] = 'Enviar';
$lm->rename['codigo_lab'] = 'Laboratorio';
$lm->rename['nombre_lab'] = 'Laboratorio';
$lm->rename['fecha_test'] = 'Fecha Muestra';
$lm->rename['codigo_test'] = 'Codigo Muestra';
$lm->rename['pasaporte_paciente'] = 'Pasaporte';
$lm->rename['nombre_paciente'] = 'Nombre';
$lm->rename['apellido1_paciente'] = 'Apellido';
$lm->rename['fecha_nac_paciente'] = 'Fecha Nacimiento';
$lm->rename['sexo_paciente'] = 'Sexo';
$lm->rename['email_paciente'] = 'Email';
$lm->rename['motivo_test'] = 'Motivo';
$lm->rename['resultado_test'] = 'Resultado';
$lm->rename['pdf_resultado'] = 'PDF Resultado';
$lm->rename['sts_enviomail'] = 'Enviado al Correo';
$lm->rename['fecha_envio'] = 'Fecha Envio';

// optional, define input controls on the form
//$lm->form_input_control['id'] = array('type' => 'select', 'sql' => 'select id from test_paciente');

// optional, define editable input controls on the grid
$lm->grid_input_control["enviar"] = array("type" => 'checkbox');
$lm->grid_input_control['is_active'] = array('type' => 'checkbox');
$lm->grid_input_control["pasaporte_paciente"] = array("type" => 'text');
$lm->grid_input_control["nombre_paciente"] = array("type" => 'text');
$lm->grid_input_control["apellido1_paciente"] = array("type" => 'text');
$lm->grid_input_control["fecha_nac_paciente"] = array("type" => 'text');
$lm->grid_input_control["sexo_paciente"] = array("type" => 'text');
$lm->grid_input_control["email_paciente"] = array("type" => 'text');
$lm->grid_input_control["motivo_test"] = array("type" => 'text');

// optional, define output control on the grid 
//$lm->grid_output_control['url_enviomail'] = array('type' => 'email'); // make email clickable

// show search box, but _search parameter still needs to be passed to query below 
$lm->grid_show_search_box = true;

$new_search_1 = $lm->clean_out(@$_REQUEST['new_search_1']);
$new_search_2 = $lm->clean_out(@$_REQUEST['new_search_2']);
$new_search_3 = $lm->clean_out(@$_REQUEST['new_search_3']);
$new_search_4 = $lm->clean_out(@$_REQUEST['new_search_4']);
$new_search_5 = $lm->clean_out(@$_REQUEST['new_search_5']);

// redefine our own search form with two inputs instead of the default one
$lm->grid_search_box = "
<form class='lm_search_box'>
    <input type='text' name='new_search_1' value='$new_search_1' size='20' class='lm_search_input' placeholder='Codigo Muestra'>
    <input type='text' name='new_search_2' value='$new_search_2' size='20' class='lm_search_input' placeholder='Pasaporte'>
	<input type='text' name='new_search_3' value='$new_search_3' size='20' class='lm_search_input' placeholder='Nombre'>
	<input type='text' name='new_search_4' value='$new_search_4' size='20' class='lm_search_input' placeholder='Apellido'>
	<input type='text' name='new_search_5' value='$new_search_5' size='20' class='lm_search_input' placeholder='Email'>
    <input type='submit' value='Buscar' class='lm_button'>
    <input type='hidden' name='action' value='search'>
</form>
"; 

// set name parameters
$lm->grid_sql_param[':new_search_1'] =  '%' . @$_REQUEST['new_search_1'] . '%';
$lm->grid_sql_param[':new_search_2'] = '%' . @$_REQUEST['new_search_2'] . '%';
$lm->grid_sql_param[':new_search_3'] =  '%' . @$_REQUEST['new_search_3'] . '%';
$lm->grid_sql_param[':new_search_4'] = '%' . @$_REQUEST['new_search_4'] . '%';
$lm->grid_sql_param[':new_search_5'] = '%' . @$_REQUEST['new_search_5'] . '%';


// query to define grid view
// IMPORTANT - last column must be the identity/key for [edit] and [delete] links to appear
// include an 'order by' to prevent potential parsing issues
$lm->grid_sql = "
SELECT 
a.enviar as enviar,
a.id as id,
b.nombre as nombre_lab, 
a.fecha_test as fecha_test, 
a.codigo_test as codigo_test, 
a.pasaporte_paciente as pasaporte_paciente, 
a.nombre_paciente as nombre_paciente, 
a.apellido1_paciente as apellido1_paciente,  
a.fecha_nac_paciente as fecha_nac_paciente, 
a.sexo_paciente as sexo_paciente, 
a.email_paciente as email_paciente, 
a.motivo_test as motivo_test, 
a.resultado_test as resultado_test, 
a.pdf_resultado as pdf_resultado, 
a.sts_enviomail as sts_enviomail,
a.fecha_envio as fecha_envio,
a.id as id
FROM test_paciente a left join laboratorio b on a.codigo_lab = b.id
where coalesce(RTRIM(LTRIM(a.codigo_test)), '') like :new_search_1
and   coalesce(RTRIM(LTRIM(a.pasaporte_paciente)), '') like :new_search_2
and   coalesce(RTRIM(LTRIM(a.nombre_paciente)), '') like :new_search_3
and   coalesce(RTRIM(LTRIM(a.apellido1_paciente)), '') like :new_search_4
and   coalesce(RTRIM(LTRIM(a.email_paciente)), '') like :new_search_5
order by a.id desc, a.fecha_test desc
";

// optional, define what is displayed on edit form. identity id must be passed in also.  
if(!isset($_REQUEST['id'])){
    // form for adding records
    $lm->form_sql = "SELECT 
	id,
	codigo_lab,
	coalesce(codigo_test, '') as codigo_test, 
	coalesce(fecha_test, '') as fecha_test, 	
	coalesce(pasaporte_paciente, '') as pasaporte_paciente, 
	nombre_paciente,
	apellido1_paciente,  
	coalesce(fecha_nac_paciente, '') as fecha_nac_paciente, 
	coalesce(sexo_paciente, '') as sexo_paciente,
	coalesce(email_paciente, '') as email_paciente, 	
	coalesce(motivo_test, '') as motivo_test
	FROM test_paciente 
	where id = :id";
}
else{
    // form for editing records
    $lm->form_sql = $lm->form_sql = "
select 
  id
, fecha_test
, codigo_test
, pasaporte_paciente
, nombre_paciente
, apellido1_paciente
, fecha_nac_paciente
, sexo_paciente
, email_paciente
, motivo_test
from  test_paciente  
where id = :id
";
}

// bind parameter for form query
$lm->form_sql_param[':id'] = @$_REQUEST['id']; 


// optional, validation - regexp, 'email' or a user defined function, all other parameters optional 
// validation using regular expression, slashes required
$lm->on_insert_validate["codigo_lab"]    = array("regexp" => "validar_laboratorio", "error_msg" => "Falta Codigo de Laboratorio", "placeholder" => "Obligatorio");
$lm->on_insert_validate["pasaporte_paciente"]    = array("regexp" =>  "/.+/", "error_msg" => "Falta DNI / NIE o Pasaporte", "placeholder" => "Obligatorio");
$lm->on_insert_validate["nombre_paciente"]   = array("regexp" => "/.+/", "error_msg" => "Falta Nombre", "placeholder" => "Obligatorio"); 
$lm->on_insert_validate["apellido1_paciente"]   = array("regexp" => "/.+/", "error_msg" => "Falta Apellido", "placeholder" => "Obligatorio"); 
$lm->on_insert_validate["email_paciente"] = array('regexp' => 'email', 'error_msg' => 'Email invalido','placeholder' => 'this is optional', 'optional' => true);
// validation using regular expression, slashes required

// copy validation rules, same rules when updating
$lm->on_update_validate = $lm->on_insert_validate;  

// user defined validation example
function validar_laboratorio(){
    if(empty($_POST['codigo_lab']))
        return false; // fail
    else
        return true; // success
}

$lm->on_insert_user_function = 'set_blank';

function set_blank(){

    if(!isset($_POST['pasaporte_paciente']))
        $_POST['pasaporte_paciente'] = " ";
		
	if(!isset($_POST['apellido2_paciente']))
		$_POST['apellido2_paciente'] = " ";
	
	if(!isset($_POST['fecha_nac_paciente']))
		$_POST['fecha_nac_paciente'] = " ";
	
	if(!isset($_POST['sexo_paciente']))
		$_POST['sexo_paciente'] = " ";
	
	if(!isset($_POST['email_paciente']))
		$_POST['email_paciente'] = " ";
	
	if(!isset($_POST['fecha_test']))
		$_POST['fecha_test'] = " ";
	
	if(!isset($_POST['motivo_test']))
		$_POST['motivo_test'] = " ";
	
	if(!isset($_POST['codigo_test']))
		$_POST['codigo_test'] = " ";
	
	if(!isset($_POST['resultado_test']))
		$_POST['resultado_test'] = " ";

	if(!isset($_POST['pdf_resultado']))
		$_POST['pdf_resultado'] = " ";		
	
	if(!isset($_POST['nProtein']))
		$_POST['nProtein'] = " ";
	
	if(!isset($_POST['sProtein']))
		$_POST['sProtein'] = " ";
	
	if(!isset($_POST['orfLab']))
		$_POST['orfLab'] = " ";	
	
	if(!isset($_POST['ELISA']))
		$_POST['ELISA'] = " ";
	
	if(!isset($_POST['PCRMultidiagnostico']))
		$_POST['PCRMultidiagnostico'] = " ";
		
	if(!isset($_POST['archivo_resultados']))
		$_POST['archivo_resultados'] = " ";
		
	if(!isset($_POST['sts_enviomail']))
		$_POST['sts_enviomail'] = " ";
	
	if(!isset($_POST['fecha_envio']))
		$_POST['fecha_envio'] = " ";	
}

$lm->on_update_user_function = $lm->on_insert_user_function;

// run the controller
$lm->run();

echo "
<hr />
<div class='footer'>
	<div class='col-md-12'>©2020 <a href='https://democratest.com/' target='_blank'>Democratest.</a> Todos los derechos reservados</div>	
</div>
</body></html>";


