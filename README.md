# PHP SMPP LIGHT is a fork from phplwsmpp with necessary functions

Lightweight PHP implementation of the SMPP 3.3 and SMPP 3.4 API. Includes the SMPP receiver and SMPP transmitter implementations.
This use code from paladin and online city

-Tested in PHP [5.6.40] [7.1.30] [7.2.19] [7.3.6]

Tested in Windows 10 and Ubuntu

## Transmitter
```php
<?php

require_once "../smpp.php";
$tx=new SMPP('IP_SMSC',PORT); // make sure the port is integer
$tx->debug=false;
$tx->bindTransmitter("username","password");
$result = $tx->sendSMS("2121","999999999","Hello world");
echo $tx->getStatusMessage($result);
$tx->close();
unset($tx);

?>

```

If the SMSC receive GSM 03.38 encoder you can encode your message with:

```php
require_once "../gsmencoder.php";
$gsmencoder = new GsmEncoder;
$message = $gsmencoder->utf8_to_gsm0338($message);
```

And send it!

The TON and NPI of source and destination address are setting automatically, so you don't need set it.

This library send enquire link to SMPP server every 10 seconds, you need to change this value according to timeout of connection from SMPP Server. Majority libraries I found send enquire link every second, this is a lack in performance and network (a little) and some SMPP have connection timeout of hours, so if talk with your SMPP provider for ak.


## Receiver

```php
<?php
//for reading pending SMS you must frequently call this script. Use crontab job for example.
ob_start();
//print "<pre>";
require_once "../smpp.php";//SMPP protocol
//connect to the smpp server
$tx=new SMPP('IP_SMSC',PORT);
$tx->debug=true;

//bind the receiver
//$tx->system_type="WWW";
$tx->addr_npi=1;
$tx->bindReceiver("username","password");

do{
	//read incoming sms
	$sms=$tx->readSMS();
	//check sms data
	if($sms && !empty($sms['source_addr']) && !empty($sms['destination_addr']) && !empty($sms['short_message'])){
		//send sms for processing in smsadv
		$from=$sms['source_addr'];
		$to=$sms['destination_addr'];
		$message=$sms['short_message'];
	    //run some processing function for incomming sms
	    process_message($from,$to,$message);
	}
//until we have sms in queue
}while($sms);
//close the smpp connection
$tx->close();
unset($tx);
//clean any output
ob_end_clean();

function process_message($from,$to,$message){
	print "Received SMS\nFrom: $from\nTo:   $to\nMsg:  $message";
}
?>
```
