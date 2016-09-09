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
use GrappboxBundle\Entity\Devices;
use DateTime;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *  @IgnoreAnnotation("apiDescription")
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


	/**
	* @api {post} /V0.2/notification/registerdevice Register user device
	* @apiName registerDevice
	* @apiGroup Notification
	* @apiDescription Register user mobile device for mobile notification send process
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token user authentication token
	* @apiParam {String} device_type device_type: "iOS" | "WP" | "Android"
	* @apiParam {String} device_token device token : uri, token or reg_id
	* @apiParam {String} device_name device name
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"device_type": "iOS",
	*			"device_token": "az5fds4zerv*8aze8ff8z9z8yh8f9d8g9yuy9ee214rtaze",
	*			"device_name": "John Doe's iPhone"
	*		}
	*	}
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.15.3",
	*			"return_message": "Notification - registerDevice - Complete Success"
	*		},
	*		"data": {}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "15.1.3",
	*			"return_message": "Notification - registerDevice - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "15.1.6",
	*			"return_message": "Notification - registerDevice - Missing Parameter"
	*		}
	*	}
	*
	*/
	public function registerDeviceAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("15.1.3", "Notification", "registerDevice"));
		if (!array_key_exists("device_token", $content) || !array_key_exists("device_type", $content) || !array_key_exists("device_name", $content))
			return ($this->setBadRequest("15.1.6", "Notification", "registerDevice", "Missing parameter"));

		$em = $this->getDoctrine()->getManager();
		$device = $em->getRepository("GrappboxBundle:Devices")->findBy(array("user" => $user, "type" => $content->device_type, "token" => $content->device_token));

		if ($device instanceof Devices)
		{
			$device->setName($content->name);
			$em->flush();
		}
		else {
			$device = new Devices();
			$device->setName($content->device_name);
			$device->setType($content->device_type);
			$device->setToken($content->device_token);
			$device->setUser($user);

			$em->persist($device);
			$em->flush();
		}

		return $this->setCreated("1.15.3", "Notification", "registerDevice", "Complete Success", (Object)array());
	}

	/**
	* @api {delete} /V0.2/notification/unregisterdevice/:token/:id Unregister user device
	* @apiName unregisterDevice
	* @apiGroup Notification
	* @apiDescription Unregister user mobile device to mobile notification send process
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token user authentication token
	* @apiParam {String} id device id in DB
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.15.3",
	*			"return_message": "Notification - unregisterDevice - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "15.2.3",
	*			"return_message": "Notification - unregisterDevice - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "15.2.4",
	*			"return_message": "Notification - unregisterDevice - Bad Parameter: id"
	*		}
	*	}
	*/
	public function unregisterDeviceAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("15.2.3", "Notification", "unregisterDevice"));

		$em = $this->getDoctrine()->getManager();
		$device = $em->getRepository("GrappboxBundle:Devices")->find($id);
		if (!($device instanceof Devices))
			return $this->setBadRequest("15.2.4", "Notification", "unregisterDevice", "Bad Parameter: id");

		$em->remove($device);
		$em->flush();
		$response["info"]["return_code"] = "1.15.1";
		$response["info"]["return_message"] = "Notification - unregisterDevice - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /V0.2/notification/getuserdevices/:token Get user registered devices
	* @apiName getuserDevices
	* @apiGroup Notification
	* @apiDescription Get user registered devices informations
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token user authentication token
	*
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.15.1",
	*			"return_message": "Notification - getuserdevices - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 3,
	*					"user": {
	*						"id": 13,
	*						"firstname": "John",
	*						"lastname": "Doe"
	*					},
	*					"name": "John Doe's iPhone",
	*					"token": "az5fds4zerv*8aze8ff8z9z8yh8f9d8g9yuy9ee214rtaze",
	*					"type": "iOS"
	*				},
	*				{
	*					"id": 4,
	*					"user": {
	*					  "id": 13,
	*					  "firstname": "John",
	*					  "lastname": "Doe"
	*					},
	*					"name": "John Doe's Android Phone",
	*					"token": "aZ5fds4zeMPC8ff8z9DFT8yh8f9F8g9yuy9",
	*					"type": "Android"
	*				}
	*			]
	*		}
	*	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.15.3",
	*			"return_message": "Notification - getUserDevices - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "15.3.3",
	*			"return_message": "Notification - getuserdevices - Bad ID"
	*		}
	*	}
	*/
	public function getUserDevicesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("15.3.3", "Notification", "unregisterDevice"));

		$em = $this->getDoctrine()->getManager();
		$device = $em->getRepository("GrappboxBundle:Devices")->findBy(array("user" => $user));

		$array = array();
		foreach ($device as $key => $value) {
			$array[] = $value->objectToArray();
		}

		if (count($array) <= 0)
			return $this->setNoDataSuccess("1.15.3", "Notification", "unregisterDevice");
		return $this->setSuccess("1.15.1", "Notification", "unregisterDevice", "Complete Success", array("array" => $array));
	}

	/**
	* @api {get} /V0.2/notification/getnotifications/:token/:read/:offset/:limit Get user notifications
	* @apiName getNotifications
	* @apiGroup Notification
	* @apiDescription Get user notifications
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token user authentication token
	* @apiParam {String} read "true" || "false" to get read or unread notifications
	* @apiParam {int} offset offset from where to get notifications (start to 0)
	* @apiParam {int} limit number max of notifications to get
	*
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.15.1",
	*			"return_message": "Notification - getNotifications - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 3,
	*					"type": "Bugtracker",
	*					"targetId": 1,
	*					"message": "You have been assigned to ticket Ticket de Test",
	*					"createdAt": {
	*						"date": "2016-01-12 14:09:46",
	*						"timezone_type": 3,
	*						"timezone": "Europe/Paris"
	*					},
	*					"isRead": false
	*				},
	*				{
	*					"id": 4,
	*					"type": "Bugtracker",
	*					"targetId": 1,
	*					"message": "The ticket Ticket de Test has been closed",
	*					"createdAt": {
	*						"date": "2016-01-12 14:12:46",
	*						"timezone_type": 3,
	*						"timezone": "Europe/Paris"
	*					},
	*					"isRead": false
	*				}
	*			]
	*		}
	*	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.15.3",
	*			"return_message": "Notification - getNotifications - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "15.4.3",
	*			"return_message": "Notification - getNotifications - Bad ID"
	*		}
	*	}
	*/
	public function getNotificationsAction(Request $request, $token, $read, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("15.4.3", "Notification", "getNotifications"));

		if ($read == "true")
			$read_value = true;
		else if ($read == "false")
			$read_value = false;

		$em = $this->getDoctrine()->getManager();
		$notification = $em->getRepository("GrappboxBundle:Notification")->findBy(array("user" => $user, "isRead" => $read), array(), $limit, $offset);

		$notif_array = array();
		foreach ($notification as $key => $value) {
			$notif_array[] = $value->objectToArray();
		}

		if (count($notif_array) <= 0)
			return ($this->setNoDataSuccess("1.15.3", "Notification", "getNotifications"));

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

		$notification->setRead(true);
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

		$em = $this->getDoctrine()->getManager();

		return new JsonResponse($this->pushNotificationAction([1, 2], $mdata, $wdata, $em));
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
	public function pushNotification($usersIds, $mdata, $wdata, $em)
	{

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
	public function android($data, $reg_id)
	{
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
	public function WP($data, $uri)
	{
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
	public function iOS($data, $devicetoken)
	{
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
	private function useCurl(&$model, $url, $headers, $fields = null)
	{
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
