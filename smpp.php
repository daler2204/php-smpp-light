<?php
/**
 *ORIGINAL AUTHOR
 * @package net
 * Class for receiving or sending sms through SMPP protocol.
 * @version 0.2
 * @author paladin
 * @since 04/25/2006
 * @see http://www.smpp.org/doc/public/index.html - SMPP 3.4 protocol specification
 */

/**
 * FORK BY xhit
 * @package net
 * Class for receiving or sending sms through SMPP protocol.
 * @version fork 0.1
 * @author xhit
 * @since 02/01/2019
 */

/**
 *This class is also a merge from smpp lib of onlinecity 
 * @see https://github.com/onlinecity/php-smpp
*/

class SMPP{

// Command ids - SMPP v3.4 - 5.1.2.1 page 110-111
  const GENERIC_NACK = 0x80000000;
  const BIND_RECEIVER = 0x00000001;
  const BIND_RECEIVER_RESP = 0x80000001;
  const BIND_TRANSMITTER = 0x00000002;
  const BIND_TRANSMITTER_RESP = 0x80000002;
  const QUERY_SM = 0x00000003;
  const QUERY_SM_RESP = 0x80000003;
  const SUBMIT_SM = 0x00000004;
  const SUBMIT_SM_RESP = 0x80000004;
  const DELIVER_SM = 0x00000005;
  const DELIVER_SM_RESP = 0x80000005;
  const UNBIND = 0x00000006;
  const UNBIND_RESP = 0x80000006;
  const REPLACE_SM = 0x00000007;
  const REPLACE_SM_RESP = 0x80000007;
  const CANCEL_SM = 0x00000008;
  const CANCEL_SM_RESP = 0x80000008;
  const BIND_TRANSCEIVER = 0x00000009;
  const BIND_TRANSCEIVER_RESP = 0x80000009;
  const OUTBIND = 0x0000000B;
  const ENQUIRE_LINK = 0x00000015;
  const ENQUIRE_LINK_RESP = 0x80000015;
  
  //  Command status - SMPP v3.4 - 5.1.3 page 112-114
  const ESME_ROK = 0x00000000; // No Error
  const ESME_RINVMSGLEN = 0x00000001; // Message Length is invalid
  const ESME_RINVCMDLEN = 0x00000002; // Command Length is invalid
  const ESME_RINVCMDID = 0x00000003; // Invalid Command ID
  const ESME_RINVBNDSTS = 0x00000004; // Incorrect BIND Status for given command
  const ESME_RALYBND = 0x00000005; // ESME Already in Bound State
  const ESME_RINVPRTFLG = 0x00000006; // Invalid Priority Flag
  const ESME_RINVREGDLVFLG = 0x00000007; // Invalid Registered Delivery Flag
  const ESME_RSYSERR = 0x00000008; // System Error
  const ESME_RINVSRCADR = 0x0000000A; // Invalid Source Address
  const ESME_RINVDSTADR = 0x0000000B; // Invalid Dest Addr
  const ESME_RINVMSGID = 0x0000000C; // Message ID is invalid
  const ESME_RBINDFAIL = 0x0000000D; // Bind Failed
  const ESME_RINVPASWD = 0x0000000E; // Invalid Password
  const ESME_RINVSYSID = 0x0000000F; // Invalid System ID
  const ESME_RCANCELFAIL = 0x00000011; // Cancel SM Failed
  const ESME_RREPLACEFAIL = 0x00000013; // Replace SM Failed
  const ESME_RMSGQFUL = 0x00000014; // Message Queue Full
  const ESME_RINVSERTYP = 0x00000015; // Invalid Service Type
  const ESME_RINVNUMDESTS = 0x00000033; // Invalid number of destinations
  const ESME_RINVDLNAME = 0x00000034; // Invalid Distribution List name
  const ESME_RINVDESTFLAG = 0x00000040; // Destination flag (submit_multi)
  const ESME_RINVSUBREP = 0x00000042; // Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)
  const ESME_RINVESMSUBMIT = 0x00000043; // Invalid esm_SUBMIT field data
  const ESME_RCNTSUBDL = 0x00000044; // Cannot Submit to Distribution List
  const ESME_RSUBMITFAIL = 0x00000045; // submit_sm or submit_multi failed
  const ESME_RINVSRCTON = 0x00000048; // Invalid Source address TON
  const ESME_RINVSRCNPI = 0x00000049; // Invalid Source address NPI
  const ESME_RINVDSTTON = 0x00000050; // Invalid Destination address TON
  const ESME_RINVDSTNPI = 0x00000051; // Invalid Destination address NPI
  const ESME_RINVSYSTYP = 0x00000053; // Invalid system_type field
  const ESME_RINVREPFLAG = 0x00000054; // Invalid replace_if_present flag
  const ESME_RINVNUMMSGS = 0x00000055; // Invalid number of messages
  const ESME_RTHROTTLED = 0x00000058; // Throttling error (ESME has exceeded allowed message limits)
  const ESME_RINVSCHED = 0x00000061; // Invalid Scheduled Delivery Time
  const ESME_RINVEXPIRY = 0x00000062; // Invalid message (Expiry time)
  const ESME_RINVDFTMSGID = 0x00000063; // Predefined Message Invalid or Not Found
  const ESME_RX_T_APPN = 0x00000064; // ESME Receiver Temporary App Error Code
  const ESME_RX_P_APPN = 0x00000065; // ESME Receiver Permanent App Error Code
  const ESME_RX_R_APPN = 0x00000066; // ESME Receiver Reject Message Error Code
  const ESME_RQUERYFAIL = 0x00000067; // query_sm request failed
  const ESME_RINVOPTPARSTREAM = 0x000000C0; // Error in the optional part of the PDU Body.
  const ESME_ROPTPARNOTALLWD = 0x000000C1; // Optional Parameter not allowed
  const ESME_RINVPARLEN = 0x000000C2; // Invalid Parameter Length.
  const ESME_RMISSINGOPTPARAM = 0x000000C3; // Expected Optional Parameter missing
  const ESME_RINVOPTPARAMVAL = 0x000000C4; // Invalid Optional Parameter Value
  const ESME_RDELIVERYFAILURE = 0x000000FE; // Delivery Failure (data_sm_resp)
  const ESME_RUNKNOWNERR = 0x000000FF; // Unknown Error

  //SMPP bind parameters
  var $system_type="WWW";
  var $interface_version=0x34;
  var $addr_ton=0;
  var $addr_npi=0;
  var $address_range="";
  //Timeout of enquirelink, default 10 seconds
  var $enquirelink_timeout = 10;
  //ESME transmitter parameters
  var $sms_service_type="";
  var $sms_source_addr_ton=0;
  var $sms_source_addr_npi=0;
  var $sms_dest_addr_ton=0;
  var $sms_dest_addr_npi=0;
  var $sms_esm_class=0;
  var $sms_protocol_id=0;
  var $sms_priority_flag=0;
  var $sms_schedule_delivery_time="";
  var $sms_validity_period="";
  var $sms_registered_delivery_flag=0;
  var $sms_replace_if_present_flag=0;
  var $sms_data_coding=0;
  var $sms_sm_default_msg_id=0;
  
  /**
   * Constructs the smpp class
   * @param $host - SMSC host name or host IP
   * @param $port - SMSC port
   */

  function __construct($host, $port){

    //Last time the of the enquirelink
    $this->lastenquire = 0;
    //internal parameters
    $this->sequence_number=1;
    $this->debug=false;
    $this->pdu_queue=array();
    $this->host=$host;
    $this->port=intval($port);
    $this->state="closed";
    //open the socket
    $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
    if($this->socket)$this->state="open";

  }

  //Function to know if is necessary send the enquirelink to SMSC
  function checkForEnquire(){

    $now = microtime(true);

    $duration = $now-$this->lastenquire;
    $hours = (int)($duration/60/60);
    $minutes = (int)($duration/60)-$hours*60;
    $seconds = (int)$duration-$hours*60*60-$minutes*60;
    
    if($this->debug){
      echo 'Seconds from last enquire link = ' . $seconds . PHP_EOL;
    }

    if ($seconds >= $this->enquirelink_timeout){
      $this->enquirelink();
    }
  }

  /**
   * Binds the receiver. One object can be bound only as receiver or only as trancmitter.
   * @param $login - ESME system_id
   * @param $port - ESME password
   * @return true when bind was successful
   */
  function bindReceiver($login, $pass){

    if($this->state!="open")return false;
    if($this->debug){
      echo "Binding receiver...\n\n";
    }
    $status=$this->_bind($login, $pass, SMPP::BIND_RECEIVER);
    if($this->debug){
      echo "Binding status  : $status\n\n";
    }
    if($status===0)$this->state="bind_rx";
    return ($status===0);
  }

  /**
   * Binds the transmitter. One object can be bound only as receiver or only as trancmitter.
   * @param $login - ESME system_id
   * @param $port - ESME password
   * @return true when bind was successful
   */
  function bindTransmitter($login, $pass){

    if($this->state!="open")return false;
    if($this->debug){
      echo "Binding transmitter...\n\n";
    }
    $status=$this->_bind($login, $pass, SMPP::BIND_TRANSMITTER);
    if($this->debug){
      echo "Binding status  : $status\n\n";
    }
    if($status===0)$this->state="bind_tx";

    return ($status===0);
  }

  /**
   * Closes the session on the SMSC server.
   */
  function close(){
    if($this->state=="closed")return;
    if($this->debug){
      echo "Unbinding...\n\n";
    }
    $status=$this->sendCommand(SMPP::UNBIND,"");
    if($this->debug){
      echo "Unbind status   : $status\n\n";
    }
    fclose($this->socket);
    $this->state="closed";
  }

  /**
   * Read one SMS from SMSC. Can be executed only after bindReceiver() call.
   * Receiver not send enquirelink 
   * This method bloks. Method returns on socket timeout or enquire_link signal from SMSC.
   * @return sms associative array or false when reading failed or no more sms.
   */

  function readSMS(){

    if($this->state!="bind_rx")return false;
    $command_id=SMPP::DELIVER_SM;
    //check the queue
    for($i=0;$i<count($this->pdu_queue);$i++){
      $pdu=$this->pdu_queue[$i];
      if($pdu['id']==$command_id){
        //remove response
        array_splice($this->pdu_queue, $i, 1);
        return parseSMS($pdu);
      }
    }
    //read pdu
    do{
      if($this->debug){
        echo "read sms...\n\n";
      }
      $pdu=$this->readPDU();
      //check for enquire link command
      if($pdu['id']==SMPP::ENQUIRE_LINK){
        $this->sendPDU(SMPP::ENQUIRE_LINK_RESP, "", $pdu['sn']);
        return false;
      }
      array_push($this->pdu_queue, $pdu);
    }while($pdu && $pdu['id']!=$command_id);
    if($pdu){
      array_pop($this->pdu_queue);
      return $this->parseSMS($pdu);
    }
    return false;
  }

  /**
   * Send one SMS from SMSC. Can be executed only after bindTransmitter() call.
   * @return true on succesfull send, false if error encountered
   */
  function sendSMS($from, $to, $message){
    if (strlen($from)>20 || strlen($to)>20 || strlen($message)>160)return false;
    if($this->state!="bind_tx")return false;
    
    //TON
    $this->sms_source_addr_ton = $this->setTon($from);
    $this->sms_dest_addr_ton = $this->setTon($to);

    //NPI
    $this->sms_source_addr_npi = $this->setNPI($from);
    $this->sms_dest_addr_npi = $this->setNPI($to);

    $pdu = pack('a1cca'.(strlen($from)+1).'cca'.(strlen($to)+1).'ccca1a1ccccca'.(strlen($message)+1),
      $this->sms_service_type,
      $this->sms_source_addr_ton,
      $this->sms_source_addr_npi,
      $from,//source_addr
      $this->sms_dest_addr_ton,
      $this->sms_dest_addr_npi,
      $to,//destination_addr
      $this->sms_esm_class,
      $this->sms_protocol_id,
      $this->sms_priority_flag,
      $this->sms_schedule_delivery_time,
      $this->sms_validity_period,
      $this->sms_registered_delivery_flag,
      $this->sms_replace_if_present_flag,
      $this->sms_data_coding,
      $this->sms_sm_default_msg_id,
      strlen($message),//sm_length
      $message//short_message
    );

    //Before each message verify if necessary send the enquirelink to SMSC
    $this->checkForEnquire();
    $status=$this->sendCommand(SMPP::SUBMIT_SM,$pdu);
    return $status;
  }

  //Get text of error
  function getStatusMessage($statuscode)
  {
    if (is_bool($statuscode)) return 'Connection Error';
    
    switch ($statuscode) {
      case SMPP::ESME_ROK: return 'OK';
      case SMPP::ESME_RINVMSGLEN: return 'Message Length is invalid';
      case SMPP::ESME_RINVCMDLEN: return 'Command Length is invalid';
      case SMPP::ESME_RINVCMDID: return 'Invalid Command ID';
      case SMPP::ESME_RINVBNDSTS: return 'Incorrect BIND Status for given command';
      case SMPP::ESME_RALYBND: return 'ESME Already in Bound State';
      case SMPP::ESME_RINVPRTFLG: return 'Invalid Priority Flag';
      case SMPP::ESME_RINVREGDLVFLG: return 'Invalid Registered Delivery Flag';
      case SMPP::ESME_RSYSERR: return 'System Error';
      case SMPP::ESME_RINVSRCADR: return 'Invalid Source Address';
      case SMPP::ESME_RINVDSTADR: return 'Invalid Dest Addr';
      case SMPP::ESME_RINVMSGID: return 'Message ID is invalid';
      case SMPP::ESME_RBINDFAIL: return 'Bind Failed';
      case SMPP::ESME_RINVPASWD: return 'Invalid Password';
      case SMPP::ESME_RINVSYSID: return 'Invalid System ID';
      case SMPP::ESME_RCANCELFAIL: return 'Cancel SM Failed';
      case SMPP::ESME_RREPLACEFAIL: return 'Replace SM Failed';
      case SMPP::ESME_RMSGQFUL: return 'Message Queue Full';
      case SMPP::ESME_RINVSERTYP: return 'Invalid Service Type';
      case SMPP::ESME_RINVNUMDESTS: return 'Invalid number of destinations';
      case SMPP::ESME_RINVDLNAME: return 'Invalid Distribution List name';
      case SMPP::ESME_RINVDESTFLAG: return 'Destination flag (submit_multi)';
      case SMPP::ESME_RINVSUBREP: return 'Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)';
      case SMPP::ESME_RINVESMSUBMIT: return 'Invalid esm_SUBMIT field data';
      case SMPP::ESME_RCNTSUBDL: return 'Cannot Submit to Distribution List';
      case SMPP::ESME_RSUBMITFAIL: return 'submit_sm or submit_multi failed';
      case SMPP::ESME_RINVSRCTON: return 'Invalid Source address TON';
      case SMPP::ESME_RINVSRCNPI: return 'Invalid Source address NPI';
      case SMPP::ESME_RINVDSTTON: return 'Invalid Destination address TON';
      case SMPP::ESME_RINVDSTNPI: return 'Invalid Destination address NPI';
      case SMPP::ESME_RINVSYSTYP: return 'Invalid system_type field';
      case SMPP::ESME_RINVREPFLAG: return 'Invalid replace_if_present flag';
      case SMPP::ESME_RINVNUMMSGS: return 'Invalid number of messages';
      case SMPP::ESME_RTHROTTLED: return 'Throttling error (ESME has exceeded allowed message limits)';
      case SMPP::ESME_RINVSCHED: return 'Invalid Scheduled Delivery Time';
      case SMPP::ESME_RINVEXPIRY: return 'Invalid message (Expiry time)';
      case SMPP::ESME_RINVDFTMSGID: return 'Predefined Message Invalid or Not Found';
      case SMPP::ESME_RX_T_APPN: return 'ESME Receiver Temporary App Error Code';
      case SMPP::ESME_RX_P_APPN: return 'ESME Receiver Permanent App Error Code';
      case SMPP::ESME_RX_R_APPN: return 'ESME Receiver Reject Message Error Code';
      case SMPP::ESME_RQUERYFAIL: return 'query_sm request failed';
      case SMPP::ESME_RINVOPTPARSTREAM: return 'Error in the optional part of the PDU Body.';
      case SMPP::ESME_ROPTPARNOTALLWD: return 'Optional Parameter not allowed';
      case SMPP::ESME_RINVPARLEN: return 'Invalid Parameter Length.';
      case SMPP::ESME_RMISSINGOPTPARAM: return 'Expected Optional Parameter missing';
      case SMPP::ESME_RINVOPTPARAMVAL: return 'Invalid Optional Parameter Value';
      case SMPP::ESME_RDELIVERYFAILURE: return 'Delivery Failure (data_sm_resp)';
      case SMPP::ESME_RUNKNOWNERR: return 'Unknown Error';
      default:
        return 'Unknown error';
    }
  }

  ////////////////private functions///////////////


  /*
  TON:
            Unknown = 0,
            International = 1,
            National = 2,
            NetworkSpecific = 3,
            SubscriberNumber = 4,
            Alphanumeric = 5,
            Abbreviated = 6,

  */

  function setTon($address){

    //Check if empty or only number
    if (empty($address) || ctype_digit($address)){
      $NationalNumberLenght = 8;

      //If length is 8 then TON is National (2)
      if(strlen($address) == $NationalNumberLenght) return 2; //National

      //If empty and length is > 8 then TON is International (1)
      if(empty($address) || strlen($address) > $NationalNumberLenght) return 1; //International
    }
    
    //If address is alphanumeric then TON is Alphanumeric (5)
    if (!ctype_digit($address)) return 5; //Alphanumeric

    //If no apply condition then TON is Unknown (0)
    return 0; //Unknown

  }

  /*
  NPI:
            Unknown = 0,
            ISDN = 1
  */
  function setNPI($address){

    //If address is alphanumeric then NPI is Unknown(0)
    if (!ctype_digit($address)) return 0; //Unknown

    //If no apply condition then NPI is ISDN (1)
    return 1; //ISDN

  }

//Send the enquirelink
public function enquireLink()
  {
    $response = $this->sendCommand(SMPP::ENQUIRE_LINK, "");
    if ($response == 0) {
      $this->lastenquire = microtime(true);
      if ($this->debug){
        echo "ENQUIRE!!" . PHP_EOL;
      }
    }
    return $response;
  }

  /**
   * @private function
   * Binds the socket and opens the session on SMSC
   * @param $login - ESME system_id
   * @param $port - ESME password
   * @return bind status or false on error
   */
  function _bind($login, $pass, $command_id){
    //make PDU
    $pdu = pack(
      'a'.(strlen($login)+1).
      'a'.(strlen($pass)+1).
      'a'.(strlen($this->system_type)+1).
      'CCCa'.(strlen($this->address_range)+1),
      $login, $pass, $this->system_type,
      $this->interface_version, $this->addr_ton,
      $this->addr_npi, $this->address_range);
    $status=$this->sendCommand($command_id,$pdu);
    return $status;
  }
  /**
   * @private function
   * Parse deliver PDU from SMSC.
   * @param $pdu - deliver PDU from SMSC.
   * @return parsed PDU as array.
   */
  function parseSMS($pdu){
    //check command id
    if($pdu['id']!=SMPP::DELIVER_SM)return false;
    //unpack PDU
    $ar=unpack("C*",$pdu['body']);
    print_r($ar);
    $sms=array('service_type'=>$this->getString($ar,6),
      'source_addr_ton'=>array_shift($ar),
      'source_addr_npi'=>array_shift($ar),
      'source_addr'=>$this->getString($ar,21),
      'dest_addr_ton'=>array_shift($ar),
      'dest_addr_npi'=>array_shift($ar),
      'destination_addr'=>$this->getString($ar,21),
      'esm_class'=>array_shift($ar),
      'protocol_id'=>array_shift($ar),
      'priority_flag'=>array_shift($ar),
      'schedule_delivery_time'=>array_shift($ar),
      'validity_period'=>array_shift($ar),
      'registered_delivery'=>array_shift($ar),
      'replace_if_present_flag'=>array_shift($ar),
      'data_coding'=>array_shift($ar),
      'sm_default_msg_id'=>array_shift($ar),
      'sm_length'=>array_shift($ar),
      'short_message'=>$this->getString($ar,255)
    );
    if($this->debug){
      echo "Delivered sms:\n";
      print_r($sms);
      echo "\n";
    }
    //send response of recieving sms
    $this->sendPDU(SMPP::DELIVER_SM_RESP, "\0", $pdu['sn']);
    return $sms;
  }
  /**
   * @private function
   * Sends the PDU command to the SMSC and waits for responce.
   * @param $command_id - command ID
   * @param $pdu - PDU body
   * @return PDU status or false on error
   */
  function sendCommand($command_id, $pdu){
    if($this->state=="closed")return false;
    $this->sendPDU($command_id, $pdu, $this->sequence_number);
    $status=$this->readPDU_resp($this->sequence_number, $command_id);
    $this->sequence_number=$this->sequence_number+1;
    return $status;
  }
  /**
   * @private function
   * Prepares and sends PDU to SMSC.
   * @param $command_id - command ID
   * @param $pdu - PDU body
   * @param $seq_number - PDU sequence number
   */
  function sendPDU($command_id, $pdu, $seq_number){
    $length=strlen($pdu) + 16;
    $header=pack("NNNN", $length, $command_id, 0, $seq_number);
    if($this->debug){
      echo "Send PDU        : $length bytes\n";
      $this->printHex($header.$pdu);
      echo "command_id      : ".$command_id."\n";
      echo "sequence number : $seq_number\n\n";
    }

    $writed = @fwrite($this->socket, $header.$pdu, $length);

    //Close conection if not bind
    if ($writed == FALSE) {
      if ($this->debug) echo "Lost connection to SMSC" . PHP_EOL;
      exit();
    };

    
  }

  /**
   * @private function
   * Waits for SMSC responce on specific PDU.
   * @param $seq_number - PDU sequence number
   * @param $command_id - PDU command ID
   * @return PDU status or false on error
   */
  function readPDU_resp($seq_number, $command_id){
    //create response id
    $command_id=$command_id|SMPP::GENERIC_NACK;
    //check queue
    for($i=0;$i<count($this->pdu_queue);$i++){
      $pdu=$this->pdu_queue[$i];
      if($pdu['sn']==$seq_number && $pdu['id']==$command_id){
        //remove response
        array_splice($this->pdu_queue, $i, 1);
        return $pdu['status'];
      }
    }
    //read pdu
    do{
      $pdu=$this->readPDU();
      if($pdu)array_push($this->pdu_queue, $pdu);
    }while($pdu && ($pdu['sn']!=$seq_number || $pdu['id']!=$command_id));
    //remove response from queue
    if($pdu){
      array_pop($this->pdu_queue);
      return $pdu['status'];
    }
    return false;
  }

  /**
   * @private function
   * Reads incoming PDU from SMSC.
   * @return readed PDU or false on error.
   */
  function readPDU(){
    //read PDU length
    $tmp=fread($this->socket, 4);
    if(!$tmp)return false;
    extract(unpack("Nlength", $tmp));
    //read PDU headers
    $tmp2=fread($this->socket, 12);
    if(!$tmp2)return false;
    extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $tmp2));
    //read PDU body
    if($length-16>0){
      $body=fread($this->socket, $length-16);
      if(!$body)return false;
    }else{
      $body="";
    }
    if($this->debug){
      echo "Read PDU        : $length bytes\n";
      $this->printHex($tmp.$tmp2.$body);
      echo "body len        : " . strlen($body) . "\n";
      echo "Command id      : $command_id\n";
      echo "Command status  : $command_status\n";
      echo "sequence number : $sequence_number\n\n";
    }
    $pdu=array(
      'id'=>$command_id,
      'status'=>$command_status,
      'sn'=>$sequence_number,
      'body'=>$body);
    return $pdu;
  }

  /**
   * @private function
   * Reads C style zero padded string from the char array.
   * @param $ar - input array
   * @param $maxlen - maximum length to read.
   * @return readed string.
   */
  function getString(&$ar, $maxlen=255){
    $s="";
    $i=0;
    do{
      $c=array_shift($ar);
      if($c!=0)$s.=chr($c);
      $i++;
    }while($i<$maxlen && $c!=0);
    return $s;
  }

  /**
   * @private function
   * Prints the binary string as hex bytes.
   * @param $maxlen - maximum length to read.
   */
  function printHex($pdu){
    $ar=unpack("C*",$pdu);
    foreach($ar as $v){
      $s=dechex($v);
      if(strlen($s)<2)$s="0$s";
      print "$s ";
    }
    print "\n";
  }
}
?>