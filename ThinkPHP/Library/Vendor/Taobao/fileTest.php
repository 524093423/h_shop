<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 

	$c = new TopClient;
	$c->appkey = "23594783";
	$c->secretKey = "4eccd1a094104b1f29302da2600169c6";
	$req = new AlibabaAliqinFcSmsNumSendRequest;
	$req->setSmsType("normal");
	$req->setSmsFreeSignName("╪рце©з");
	$req->setSmsParam("{\"info\":\"1234\"}");
	$req->setRecNum("13126808448");
	$req->setSmsTemplateCode("SMS_39255277");
	$resp = $c->execute($req);
    var_dump($resp);
?>