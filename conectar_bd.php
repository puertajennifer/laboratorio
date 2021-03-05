<?php 
function getCnxDB()
{
    $ini = parse_ini_file('appdemocratest.ini');
    $db_host = $ini['host'];
    $db_name = $ini['database'];
    $db_user = $ini['user'];
    $db_pass = $ini['pass'];

    try {
        $conn = new PDO( "mysql:host=$db_host;dbname=$db_name", "$db_user", "$db_pass");
        $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     }
     catch(PDOException $e)
     {
         echo $sql . "<br>" . $e->getMessage();
     }
    return $conn;
}
?>