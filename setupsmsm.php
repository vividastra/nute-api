<?php 
 
// Update the path below to your autoload.php, 
// see https://getcomposer.org/doc/01-basic-usage.md 
require_once '/path/to/vendor/autoload.php'; 
 
use Twilio\Rest\Client; 
 
$sid    = "AC36f2234ff5c25e2619135cd710ac0362"; 
$token  = "7ed535184125c2f4bb31874c9cdbcc3a"; 
$twilio = new Client($sid, $token); 
 
$message = $twilio->messages 
                  ->create("+918095143061", // to 
                           array( 
                               "from" => "+12513177918", 
                               "messagingServiceSid" => "MGc223e6bf48a8eff6cb41f5dad637c5a5",      
                               "body" => "OTP MESSAGE FROM NUTE" 
                           ) 
                  ); 
 
print($message->sid);