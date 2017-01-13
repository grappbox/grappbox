<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use SQLBundle\Controller\RolesAndTokenVerificationController;
use SQLBundle\Controller\User;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *	@IgnoreAnnotation("apiDescription")
 *  @IgnoreAnnotation("apiParamExample")
 *  @IgnoreAnnotation("apiHeader")
 *  @IgnoreAnnotation("apiHeaderExample")
 */
class UserController extends RolesAndTokenVerificationController
{
  public function passwordEncryptAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository("SQLBundle:User")->findOneBy(array('id' => $id));

    $pwd = $user->getPassword();
    $encoder = $this->container->get('security.password_encoder');
    $encoded = $encoder->encodePassword($user, $pwd);
    $user->setPassword($encoded);
    $em->flush();
    return new JsonResponse("Success: Password encoded for user ".$user->getFirstname()." ".$user->getLastname());
  }

	public function basicInformationsAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.1.3", "User", "basicinformations"));

		$method = $request->getMethod();
		$em = $this->getDoctrine()->getManager();

		if ($method == "GET")
			return $this->getBasicInformations($user);
		else if ($method == "PUT")
			return $this->putBasicInformations($content, $user, $em);
	}

	/**
	* @api {get} /0.3/user Request the basic informations of the connected user
	* @apiName getBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations of the connected user
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	*
	* @apiSuccess {int} id Id of the person
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	* @apiSuccess {Date} birthday Birthday of the person
	* @apiSuccess {string} avatar Avatar last modif date
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {String} phone Phone number of the person
	* @apiSuccess {String} country Country the person in living in
	* @apiSuccess {String} linkedin Linkedin of the person
	* @apiSuccess {String} viadeo Viadeo of the person
	* @apiSuccess {String} twitter Twitter of the person
	* @apiSuccess {Boolean} is_client if the user is a client
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getbasicinformations - Complete Success"
	*		},
	*		"data": {
	*			"id": 2,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"birthday": "1945-06-18",
	*			"avatar": "1945-06-18 06:00:00",
	*			"email": "john.doe@gmail.com"
	*			"phone": "+33984231475",
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe",
	*			"is_client": false
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.1.3",
	*			"return_message": "User - getbasicinformations - Bad Token"
	*		}
	*	}
	*/
	private function getBasicInformations($user)
	{
		return $this->setSuccess("1.7.1", "User", "getbasicinformations", "Complete Success", $user->fullObjectToArray());
	}

	/**
	* @api {get} /0.3/user/:userId Request the basic informations for a user
	* @apiName getUserBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations for the given user
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} userId id of the user you want some informations
	*
	* @apiSuccess {int} id Id of the person
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	* @apiSuccess {Date} birthday Birthday of the person
	* @apiSuccess {date} avatar Avatr last date of modif
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {String} phone Phone number of the person
	* @apiSuccess {String} country Country the person in living in
	* @apiSuccess {String} linkedin Linkedin of the person
	* @apiSuccess {String} viadeo Viadeo of the person
	* @apiSuccess {String} twitter Twitter of the person
	* @apiSuccess {Boolean} is_client if the user is a client
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getuserbasicinformations - Complete Success"
	*		},
	*		"data": {
	*			"id": 2,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"birthday": "1945-06-18"
	*			"avatar": "1945-06-18 06:00:00",
	*			"email": "john.doe@gmail.com"
	*			"phone": "+33984231475",
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe",
	*			"is_client": false
	*		}
	* 	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.2.3",
	*			"return_message": "User - getuserbasicinformations - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.2.4",
	*			"return_message": "User - getuserbasicinformations - Bad Parameter: userId"
	*		}
	*	}
	*/
	public function getUserBasicInformationsAction(Request $request, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.2.3", "User", "getuserbasicinformations"));

		$userInfos = $this->getDoctrine()->getManager()->getRepository('SQLBundle:User')->find($userId);
		if ($userInfos === null)
			return $this->setBadRequest("7.2.4", "User", "getuserbasicinformations", "Bad Parameter: userId");

		return $this->setSuccess("1.7.1", "User", "getuserbasicinformations", "Complete Success", $userInfos->fullObjectToArray());
	}

	/**
	* @api {put} /0.3/user Update the basic informations of the user connected
	* @apiName putBasicInformations
	* @apiGroup Users
	* @apiDescription Update the basic informations of the user connected
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {String} [firstname] First name of the person
	* @apiParam {String} [lastname] Last name of the person
	* @apiParam {Date} [birthday] Birthday of the person
	* @apiParam {Text} [avatar] Avatar of the person
	* @apiParam {String} [oldPassword] Old password of the person. oldPassword and password must be set if you want to change password
	* @apiParam {String} [password] New password of the person. oldPassword and password must be set if you want to change password
	* @apiParam {String} [phone] Phone number of the person
	* @apiParam {String} [country] Country the person in living in
	* @apiParam {String} [linkedin] Linkedin of the person
	* @apiParam {String} [viadeo] Viadeo of the person
	* @apiParam {String} [twitter] Twitter of the person
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"birthday": "1945-06-18",
	*			"avatar": "10001111001100110010101010",
	*			"oldPassword": "toto",
	*			"password": "azertyuiop",
	*			"phone": +33984231475,
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe"
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"birthday": "1945-06-18",
	*			"password": "azertyuiop",
	*			"phone": +33984231475,
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"twitter": "twitter.com/john.doe"
	*		}
	*	}
	*
	* @apiSuccess {int} id Id of the person
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	* @apiSuccess {Date} birthday Birthday of the person
	* @apiSuccess {string} avatar Avatar last date of modif
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {String} phone Phone number of the person
	* @apiSuccess {String} country Country the person in living in
	* @apiSuccess {String} linkedin Linkedin of the person
	* @apiSuccess {String} viadeo Viadeo of the person
	* @apiSuccess {String} twitter Twitter of the person
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - putuserbasicinformations - Complete Success"
	*		},
	*		"data": {
	*			"id": 1
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"birthday": "1945-06-18"
	*			"avatar": "1945-06-18 06:00:00",
	*			"email": "john.doe@gmail.com"
	*			"phone": "+33984231475",
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe"
	*		}
	* 	}
	* @apiSuccessExample {json} Notifications Avatar
	*	{
	*		"data": {
	*			"title": "avatar user",
	*			"body": {
	*				"id": 1
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"birthday": "1945-06-18"
	*				"avatar": "1945-06-18 06:00:00",
	*				"email": "john.doe@gmail.com"
	*				"phone": "+33984231475",
	*				"country": "France",
	*				"linkedin": "linkedin.com/john.doe",
	*				"viadeo": "viadeo.com/john.doe",
	*				"twitter": "twitter.com/john.doe"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.1.3",
	*			"return_message": "User - putbasicinformations - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: oldPassword
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.1.4",
	*			"return_message": "User - putbasicinformations - Bad Parameter: oldPassword"
	*		}
	*	}
  * @apiErrorExample Bad Parameter: avatar
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.1.4",
	*			"return_message": "User - putbasicinformations - Bad Parameter: avatar"
	*		}
	*	}
	*/
	private function putBasicInformations($content, $user, $em)
	{
		$content = $content->data;

		if (array_key_exists('firstname', $content))
			$user->setFirstname($content->firstname);
		if (array_key_exists('lastname', $content))
			$user->setLastname($content->lastname);
		if (array_key_exists('birthday', $content))
		{
			$birthday = date_create($content->birthday);
			$user->setBirthday($birthday);
		}
		if (array_key_exists('avatar', $content))
		{
      		$filepath = "/var/www/static/app/user/".$user->getId();

			$file = base64_decode($content->avatar);
			if ($file == false)
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: avatar");

			$image = imagecreatefromstring($file);
			if ($image == false)
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: avatar");

			if (!imagejpeg($image, $filepath, 80))
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: avatar");

			imagedestroy($image);

			$fileurl = 'https://static.grappbox.com/app/user/'.$user->getId();

			$user->setAvatar($fileurl);
			$user->setAvatarDate(new \DateTime);

			//notifs
			$mdata['mtitle'] = "avatar user";
			$mdata['mdesc'] = json_encode($user->objectToArray());
			$wdata['type'] = "avatar user";
			$wdata['targetId'] = $user->getId();
			$wdata['message'] = json_encode($user->objectToArray());
			$userNotif = array();
			foreach ($user->getProjects() as $key => $value) {
				foreach ($value->getUsers() as $key => $user) {
					if (!in_array($user->getId(), $userNotif))
						$userNotif[] = $user->getId();
				}
			}
			if (count($userNotif) > 0)
				$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
		}
		if (array_key_exists('phone', $content))
			$user->setPhone($content->phone);
		if (array_key_exists('country', $content))
			$user->setCountry($content->country);
		if (array_key_exists('linkedin', $content))
			$user->setLinkedin($content->linkedin);
		if (array_key_exists('viadeo', $content))
			$user->setViadeo($content->viadeo);
		if (array_key_exists('twitter', $content))
			$user->setTwitter($content->twitter);
		if (array_key_exists('password', $content) && array_key_exists('oldPassword', $content))
		{
			$encoder = $this->container->get('security.password_encoder');
			if (!($encoder->isPasswordValid($user, $content->oldPassword)))
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: oldPassword");
			$encoded = $encoder->encodePassword($user, $content->password);
			$user->setPassword($encoded);
		}

		$em->flush();

		return $this->setSuccess("1.7.1", "User", "putbasicinformations", "Complete Success", $user->fullObjectToArray());
	}

	/**
	* @api {get} /0.3/user/id/:firstName/:lastName Request the user Id with the first and last name
	* @apiName getIdByName
	* @apiGroup Users
	* @apiDescription Request the user Id with the first name and the last name
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {String} firstName first name of the user
	* @apiParam {String} lastName last name of the user
	*
	* @apiSuccess {Object[]} array Array of persons
	* @apiSuccess {Number} array.id Id of the person
	* @apiSuccess {String} array.firstname First name of the person
	* @apiSuccess {String} array.lastname Last name of the person
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getidbyname - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 2,
	*					"firstname": "John",
	*					"lastname": "Doe"
	*				}
	*			]
	*		}
	* 	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.7.3",
	*			"return_message": "User - getidbyname - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.4.3",
	*			"return_message": "User - getidbyname - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.4.4",
	*			"return_message": "User - getidbyname - Bad Parameters"
	*		}
	*	}
	*/
	public function getIdByNameAction(Request $request, $firstname, $lastname)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.4.3", "User", "getidbyname"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:User');
		$qb = $repository->createQueryBuilder('u')->where('u.firstname = :firstname', 'u.lastname = :lastname')->setParameter('firstname', $firstname)->setParameter('lastname', $lastname);
		$users = $qb->getQuery()->getResult();
		if ($users === null)
			return $this->setBadRequest("7.4.4", "User", "getidbyname", "Bad Parameters");

		$arr = array();

		foreach ($users as $user) {
			$id = $user->getId();

			$arr[] = array("id" => $id, "firstname" => $firstname, "lastname" => $lastname);
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.7.3", "User", "getidbyname");

		return $this->setSuccess("1.7.1", "User", "getidbyname", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /0.3/user/id/:email Request the user Id with the email
	* @apiName getIdByEmail
	* @apiGroup Users
	* @apiDescription Request the user Id with the email
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {String} email email of the user
	*
	* @apiSuccess {Number} id id of the person
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getidbyemail - Complete Success"
	*		},
	*		"data": {
	*			"id": 2,
	*			"firstname": "John",
	*			"lastname": "Doe"
	*		}
	* 	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.5.3",
	*			"return_message": "User - getidbyemail - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: email
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.5.4",
	*			"return_message": "User - getidbyemail - Bad Parameter: email"
	*		}
	*	}
	*/
	public function getIdByEmailAction(Request $request, $email)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.5.3", "User", "getidbyemail"));

		$userEmail = $this->getDoctrine()->getManager()->getRepository('SQLBundle:User')->findOneByEmail($email);

		if ($userEmail === null)
			return $this->setBadRequest("7.5.4", "User", "getidbyemail", "Bad Parameter: email");

		$id = $userEmail->getId();
		$firstname = $userEmail->getFirstname();
		$lastname = $userEmail->getLastname();

		return $this->setSuccess("1.7.1", "User", "getidbyemail", "Complete Success", array("id" => $id, "firstname" => $firstname, "lastname" => $lastname));
	}

	/**
	* @api {get} /0.3/user/avatar/:userId Get user avatar
	* @apiName getUserAvatar
	* @apiGroup Users
	* @apiDescription Get the avatar of the given user
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} userId Id of the user
	*
	* @apiSuccess {Text} avatar avatar of the user
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getUserAvatar - Complete Success"
	*		},
	*		"data": {
	*			"avatar": "10100011000011001"
	*		},
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.9.3",
	*			"return_message": "User - getUserAvatar - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.9.4",
	*			"return_message": "User - getUserAvatar - Bad Parameter: userId"
	*		}
	*	}
	*/
	public function getUserAvatarAction(Request $request, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.9.3", "User", "getUserAvatar"));

		$em = $this->getDoctrine()->getManager();
		$requestedUser = $em->getRepository('SQLBundle:User')->find($userId);

		if ($requestedUser === null)
			return $this->setBadRequest("7.9.4", "User", "getUserAvatar", "Bad Parameter: userId");

		return $this->setSuccess("1.7.1", "User", "getUserAvatar", "Complete Success", array("avatar" => $requestedUser->getAvatar()));
	}

	/**
	* @api {get} /0.3/user/project/avatars/:projectId Get all project user avatar
	* @apiName getAllProjectUserAvatar
	* @apiGroup Users
	* @apiDescription Get the avatar of all the users of the given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the user
	*
	* @apiSuccess {Object[]} array users list
	* @apiSuccess {int} array.userId user id
	* @apiSuccess {text} array.avatar user avatar
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getAllProjectUserAvatar - Complete Success"
	*		},
	*		"data": {
	*			"userId": 13,
	*			"avatar": "10100011000011001"
	*		},
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.9.3",
	*			"return_message": "User - getAllProjectUserAvatar - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "7.9.4",
	*			"return_message": "User - getAllProjectUserAvatar - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getAllProjectUserAvatarAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.10.3", "User", "getAllProjectUserAvatar"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("7.10.4", "User", "getAllProjectUserAvatar", "Bad Parameter: projectId");

		foreach ($project->getUsers() as $key => $user) {
			$data[] = array("userId" => $user->getId(), "avatar" => $user->getAvatar());
		}

		return $this->setSuccess("1.7.1", "User", "getAllProjectUserAvatar", "Complete Success", array("array" => $data));
	}
}
