<?php
require_once("../API/qqConnectAPI.php");
$qc = new QC();
echo $qc->qq_callback();
echo $qc->get_openid();
echo "authCOde:".$_GET['code']."<br/>";
echo "state:".$_GET['state'];
?>
