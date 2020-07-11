<?php
ini_set('display_errors', getenv('DISPLAY_ERRORS'));
error_reporting(E_ALL);

require 'vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

use GuzzleHttp;

$raw = file_get_contents('php://input');
$e = json_decode($raw,1);

// voiceNotification('+442033898295', '+31612345678');

// GPS devices : Device ID | Nr to call from | Nr to call to 
$devices = [
    9051125461 => ['callFrom' => '+442033898295', 'callTo' => '+31612345678'], // BMW
];  

 if (!array_key_exists($e['device']['uniqueId'], $devices)){ exit(); }

 $deviceId = $e['device']['uniqueId'];


// Events to report by call
$reportEvents = array('deviceMoving', 'alarm', 'geofenceExit');


if (in_array($e['event']['type'], $reportEvents)){
    voiceNotification($devices[$deviceId]['callFrom'], $devices[$deviceId]['callTo']); // CM.com
}


// CM.com Voice Api integration
function voiceNotification($callFrom, $callTo){

    $body = [
        'callee' => $callTo,
        'caller' => $callFrom,
        'anonymous' => false,
        'prompt' => 'Car alarm activated',
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

