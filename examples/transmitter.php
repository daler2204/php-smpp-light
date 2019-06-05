<?php

require_once "../smpp.php";
$tx=new SMPP('192.168.1.90',5018); // make sure the port is integer
$tx->debug=false;
$tx->bindTransmitter("username","password");
$result = $tx->sendSMS("2121","999999999","Hello world");
echo $tx->getStatusMessage($result);
$tx->close();
unset($tx);

?>
