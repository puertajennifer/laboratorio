<?php

# Incluir autoload
include "vendor/autoload.php";

$parseador = new \Smalot\PdfParser\Parser();
$nombreDocumento = "files/Report_PCR_PPL0018018_MARINA SANTELLI_ID_16044B500174.pdf";
$documento = $parseador->parseFile($nombreDocumento);

$texto = $documento->getText();
echo "<pre>";
echo $texto;
echo "</pre>";