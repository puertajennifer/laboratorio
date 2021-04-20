<?php 

include('conectar_bd.php');

$conn = getCnxDB();

$sql = "UPDATE test_paciente SET enviar = ABS(enviar - 1)";

$stmt = $conn->query($sql);

echo "success";

