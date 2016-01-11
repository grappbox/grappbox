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
	// (Android)API access key from Google API's Console.
  	private static $API_ACCESS_KEY = 'AIzaSyDG3fYAj1uW7VB-wejaMJyJXiO5JagAsYI'; //TODO to change

	// (iOS) Private key's passphrase.
	private static $passphrase = 'joashp'; // TODO to change

	// (Windows Phone 8) The name of our push channel.
  	private static $channelName = "joashp"; // TODO to change

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

	public function getNotificationsAction(Request $request, $token, $read, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("15.4.3", "Notification", "getNotification"));

    	if ($read == "true")
			$read_value = true;
		else if
			$read_value = false;


		$em = $this->getDoctrine()->getManager();
    	$notification = $em->getRepository("GrappboxBundle:Notification")->findBy(array("user" => $user, "read" => $read), array(), $limit, $offset);

    	$notif_array = array();
    	foreach ($notification as $key => $value) {
      		$notif_array[] = $value->objectToArray();
	    }

    	if (count(notif_array) <= 0)
      		return ($this->setNoDataSuccess("1.15.3", "Notification", "getNotification"));

	    return ($this->setSuccess("1.15.1", "Notification", "getNotifications", "Complete Success", array("array" => $notif_array)));
  	}

	public function setNotificationToReadAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
		  return ($this->setBadTokenError("15.5.3", "Notification", "setNotificationToRead"));

		$em = $this->getDoctrine()->getManager();
		$notification = $em->getRepository("GrappboxBundle:Notification")->find($id);

		if (!($notification instanceof Notification))
		  return ($this->setBadRequest("15.5.3", "Notification", "setNotificationToRead", "Bad ID"));

		$notification->setRead(true):
		$em->flush();

		return ($this->setSuccess("1.15.1", "Notification", "setNotificationRead", "Complete Success", $notification->objectToArray()));
	}

	public function pushTestAction()
	{
		$mdata['mtitle'] = "Timeline - New message";
		$mdata['mdesc'] = "There is a new message on the timeline";

		$wdata['type'] = "Project";
		$wdata['targetId'] = 2;
		$wdata['message'] = "You have been added on the project Grappbox";

		return new JsonResponse($this->pushNotification([1, 2], $data, $wdata));
	}

	/*
	** send push notification(mobile) and create a notification(desktop/web) in the db
	** $userIds Array of user id that should be notified
	**
	** $mdata is an array for mobile notification containing:
	** mtitle: the title of the notification
	** mdesc: the description of the notification
	** exemple: $mdata['mtitle'] = "Timeline - New message"
	**			$mdata['mdesc'] = "There is a new message on the timeline"
	**
	** $wdata is an array for web and desktop notification containing:
	** type: Part of the application that changed
	** targetId: Id of the part (which timeline, which bug, which project, ...) that has been modified
	** $message: message for the user
	** exemple: $wdata['type']: "Project"
	**			$wdata['targetId']: 3
	**			$wdata['message']: "You've been added on the project X" (with X being the name of the project)
	*/
	public function pushNotification($usersIds, $mdata, $wdata)
	{
		// get Devices
		$em = $this->getDoctrine()->getManager();

		foreach ($usersIds as $userId) {
			$user = $em->getRepository("GrappboxBundle:User")->find($userId);

			if ($user != null)
			{
				//notificaton for devices
				// $devices = $em->getRepository("GrappboxBundle:Devices")->findByuser($user);

				// foreach ($devices as $device) {
				// 	$type = $device->getType();
				// 	$token = $device->getToken();

				// 	switch ($type) {
				// 		case 'android':
				// 			$this->android($mdata, $token)
				// 			break;
				// 		case 'ios':
				// 			$this->iOS($mdata, $token)
				// 			break,
				// 		case 'wp':
				// 			$this->WP($mdata, $token)
				// 			break;
				// 		default:
				// 			break;
				// 	}
				// }

				//notification for web and desktop
				$notification = new Notification();
				$notification->setUser($user);
				$notification->setType($wdata['type']);
				$notification->setTargetId($wdata['targetId']);
				$notification->setMessage($wdata['message']);
				$notification->setIsRead(false);
				$notification->setCreatedAt(new \Datetime);

				$em->persist($notification);
				$em->flush();
			}
		}

		return true;
	}


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
