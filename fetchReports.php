<?php
// fetchReports.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbcon.php');

$reportRef = $database->getReference('report');
$reportSnapshot = $reportRef->getSnapshot();
$reportData = $reportSnapshot->getValue();
?>
