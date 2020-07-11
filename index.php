<?php

ini_set('display_errors', getenv('DISPLAY_ERRORS'));
error_reporting(E_ALL);

$raw = file_get_contents('php://input');			// Bug in traccar, only way to retrieve valid JSON!
$e = json_decode($raw,1);							// Raw JSON needs to be decoded

//debug($raw);										// Dump Raw input to file!


require 'vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

//use GuzzleHttp;
use GuzzleHttp as GuzzleHttp;

// GPS devices : Device ID | Nr to call from | Nr to call to 
$devices = [
    '0870' => ['callFrom' => '+442033898295', 'callTo' => '+31612345678'], // BMW
];  

// Call on the following events!
//$reportEvents = array('deviceMoving', 'alarm', 'geofenceExit','deviceOnline');        // Can be used for debugging
$reportEvents = array('deviceMoving', 'alarm', 'geofenceExit');

// Check if some keys exists in the received JSON and if they do continue!
if (array_key_exists('status',   $e['device'])) { $deviceStatus= $e['device']['status']; } 		else $deviceStatus = 'empty'; 
if (array_key_exists('name',     $e['device'])) { $deviceName  = $e['device']['name'];  } 		else $deviceName   = 'empty';
if (array_key_exists('uniqueId', $e['device'])) { $deviceId    = $e['device']['uniqueId']; }  	else $deviceId     = 'empty'; 
if (array_key_exists('type',     $e['event']))  { $deviceEvent = $e['event']['type']; } 		else $deviceEvent  = 'empty';

if ($deviceId != 'empty' && $deviceStatus != 'empty')				// We need a deviceId and Status
{
	debug('Step 1: Status: '.$deviceStatus.' for device: '.$deviceName.' with id: '.$deviceId);	
	if (array_key_exists($deviceId, $devices))					// Check the device ID against the requestes devices!
	{		
		debug('Step 2: Status: '.$deviceStatus.' for device: '.$deviceName.' with id: '.$deviceId);	
			
		if ($deviceEvent != 'empty') 							// The device is in the list but does it have an event?
		{
	 		if (in_array($deviceEvent, $reportEvents)) 			// It seems so, is the event in the reportEvents array?
	 		{
	 			$message = $deviceName. ' GPS alarm triggered: '.$deviceEvent.'!';
				debug('Step 3: '.$deviceName.' - with id: '.$deviceId.' '.$deviceEvent.' message: '.$message);
   				voiceNotification($devices[$deviceId]['callFrom'], $devices[$deviceId]['callTo'],$message); 	// Send voice notification CM.com
			}
			else 
			{
				debug('Step 4: Not interested in event: '.$deviceEvent.' for device: '.$deviceName.' just logging it then!');
			}
		}
		else // We don't need to do anything as there seems to be no deviceEvent.
		{
				debug('Step 5: '.$deviceName.'-'.$deviceId.' '.$deviceEvent);	
		}
	}	
	else // It seems we are not interested in this device!
	{
		debug('Step 6: Not interested in this Device');	
	}
}
else // Nothing to do
{
	debug('Step 7: Nothing for Status: '.$deviceStatus.' for device: '.$deviceName.' with id: '.$deviceId.' and event '.$deviceEvent);	
} 

// CM.com Voice Api integration
function voiceNotification($callFrom, $callTo, $message){

    $body = [
        'callee' => $callTo,
        'caller' => $callFrom,
        'anonymous' => false,
        'prompt' => $message,
        'prompt-type' => 'TTS',
        'voicemail-response' => 'Restart',
        'voice' => [
            'language' => 'en-UK',
            'gender' => 'Female',
            'number' => 2,
            'volume' => 4,
        ],
        'max-replays' => 1, 
        'auto-replay' => 1, 
        'callback-url' => ''
    ];

    $client = new \GuzzleHttp\Client();

        $request = $client->post(getenv('CM_VOICE_API_URL'),
            [
                'headers' => [
                    'X-CM-PRODUCTTOKEN' => getenv('X_CM_PRODUCT_TOKEN'),
                    'Content-Type' => 'Application/json'
                ],
                'body' => json_encode($body)
            ]
        );

    return $request->getBody();
}

function debug($line)
{
	$fp = fopen('request.log', 'a');
	fwrite($fp, date('Y-m-d H:i:s').': '.$line.PHP_EOL);
	fclose($fp);
}
