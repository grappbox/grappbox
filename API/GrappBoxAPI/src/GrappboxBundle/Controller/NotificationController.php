<?php

namespace GrappboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use GrappboxBundle\Controller\RolesAndTokenVerificationController;

use GrappboxBundle\Entity\Notification;
//use GrappboxBundle\Entity\Device;
use DateTime;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 */
class NotificationController extends RolesAndTokenVerificationController
{
  public function registerDeviceAction()
  {
    // renvois l'objet device avec id , user_id , device token, device_type
    // si user_id existe et que device_token && device_type n'ont pas changé ne fait rien et renvois le device Object

  }

  public function unregisterDeviceAction(Request $request, $id)
  {
      // $id == $device->id
  }

  public function getUserDeviceAction(Request $request, $id)
  {
    // $id == $user->id

    // return array of device object
  }

  public function pushNotification()
  {
    // get Devices

    // push on Android

    // push on Windwos Phone

    // push on iOS
  }

  public function getNotificationsAction(Request $request, $token)
  {
    // pour Desktop et Web

    $user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
    $notification = $em->getRepository("GrappboxBundle:Notification")->findBy(array("user_id" => $user->getId()/*, "read" => false*/));

    $notif_array = array();
    foreach ($notification as $key => $value) {
      $notif_array[] = $vlaue->objectToArray();
    }

    //if (count(notif_array) <= 0)
      // return "no data to return"
    return new JsonResponse(array("array" => $notif_array));
  }

  public function setNotificationToReadAction(Request $request, $token, $id)
  {
    // set notif->read as true
    $user = $this->checkToken($token);
    if (!$user)
      return ($this->setBadTokenError());
    $em = $this->getDoctrine()->getManager();
    $notification = $em->getRepository("GrappboxBundle:Notification")->find($id);

    //if (!($notification instanceof Notification))
      // return error "no data"/"bad id"
    // $notification->setDeletedAt(new DateTime('now')):
    // $em->flush();

    //return "success"
  }

  // (Android)API access key from Google API's Console.
	private static $API_ACCESS_KEY = 'AIzaSyDG3fYAj1uW7VB-wejaMJyJXiO5JagAsYI'; //TODO to change
	// (iOS) Private key's passphrase.
	private static $passphrase = 'joashp'; // TODO to change
	// (Windows Phone 8) The name of our push channel.
  private static $channelName = "joashp"; // TODO to change

  // Sends Push notification for Android users
	public function android($data, $reg_id) {
	        $url = 'https://android.googleapis.com/gcm/send';
	        $message = array(
	            'title' => $data['mtitle'],
	            'message' => $data['mdesc'],
	            'subtitle' => '',
	            'tickerText' => '',
	            'msgcnt' => 1,
	            'vibrate' => 1
	        );

	        $headers = array(
	        	'Authorization: key=' .self::$API_ACCESS_KEY,
	        	'Content-Type: application/json'
	        );

	        $fields = array(
	            'registration_ids' => array($reg_id),
	            'data' => $message,
	        );

	    	return $this->useCurl($url, $headers, json_encode($fields));
    	}

	// Sends Push's toast notification for Windows Phone 8 users
	public function WP($data, $uri) {
		$delay = 2;
		$msg =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
		        "<wp:Notification xmlns:wp=\"WPNotification\">" .
		            "<wp:Toast>" .
		                "<wp:Text1>".htmlspecialchars($data['mtitle'])."</wp:Text1>" .
		                "<wp:Text2>".htmlspecialchars($data['mdesc'])."</wp:Text2>" .
		            "</wp:Toast>" .
		        "</wp:Notification>";

		$sendedheaders =  array(
		    'Content-Type: text/xml',
		    'Accept: application/*',
		    'X-WindowsPhone-Target: toast',
		    "X-NotificationClass: $delay"
		);

		$response = $this->useCurl($uri, $sendedheaders, $msg);

		$result = array();
		foreach(explode("\n", $response) as $line) {
		    $tab = explode(":", $line, 2);
		    if (count($tab) == 2)
		        $result[$tab[0]] = trim($tab[1]);
		}

		return $result;
	}

  // Sends Push notification for iOS users
	public function iOS($data, $devicetoken) {

		$deviceToken = $devicetoken;

		$ctx = stream_context_create();
		// ck.pem is your certificate file
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);

		// Open a connection to the APNS server
		$fp = stream_socket_client(
			'ssl://gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);

		// Create the payload body
		$body['aps'] = array(
			'alert' => array(
			    'title' => $data['mtitle'],
                'body' => $data['mdesc'],
			 ),
			'sound' => 'default'
		);

		// Encode the payload as JSON
		$payload = json_encode($body);

		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));

		// Close the connection to the server
		fclose($fp);

		if (!$result)
			return 'Message not delivered' . PHP_EOL;
		else
			return 'Message successfully delivered' . PHP_EOL;

	}

	// Curl
	private function useCurl(&$model, $url, $headers, $fields = null) {
	        // Open connection
	        $ch = curl_init();
	        if ($url) {
	            // Set the url, number of POST vars, POST data
	            curl_setopt($ch, CURLOPT_URL, $url);
	            curl_setopt($ch, CURLOPT_POST, true);
	            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	            // Disabling SSL Certificate support temporarly
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	            if ($fields) {
	                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	            }

	            // Execute post
	            $result = curl_exec($ch);
	            if ($result === FALSE) {
	                die('Curl failed: ' . curl_error($ch));
	            }

	            // Close connection
	            curl_close($ch);

	            return $result;
        }
    }

}
