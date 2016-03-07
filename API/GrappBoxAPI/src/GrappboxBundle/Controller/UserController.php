<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use GrappboxBundle\Controller\RolesAndTokenVerificationController;

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
 */
class UserController extends RolesAndTokenVerificationController
{
	public function basicInformationsAction(Request $request, $token)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($token);
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
	* @api {get} /V0.2/user/basicinformations/:token Request the basic informations of the connected user
	* @apiName getBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations of the connected user
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token token of the person connected
	*
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	* @apiSuccess {Date} birthday Birthday of the person
	* @apiSuccess {Text} avatar Avatr of the person
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {Number} phone Phone number of the person
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
	*			"return_message": "User - getbasicinformations - Complete Success"
	*		},
	*		"data": {
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"birthday": "1945-06-18",
	*			"avatar": "10001111001100110010101010",
	*			"email": "john.doe@gmail.com"
	*			"phone": "+33984231475",
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.1.3",
	*			"return_message": "User - getbasicinformations - Bad ID"
	*		}
	*	}
	*/
	private function getBasicInformations($user)
	{
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
		if ($birthday != null)
			$birthday = $birthday->format('Y-m-d');
		$avatar = $user->getAvatar();
		$email = $user->getEmail();
		$phone = $user->getPhone();
		$country = $user->getCountry();
		$linkedin = $user->getLinkedin();
		$viadeo = $user->getViadeo();
		$twitter = $user->getTwitter();

		return $this->setSuccess("1.7.1", "User", "getbasicinformations", "Complete Success", array("firstname" => $firstName, "lastname" => $lastName, "birthday" => $birthday,
			"avatar" => $avatar, "email" => $email, "phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	* @api {get} /V0.2/user/getuserbasicinformations/:token/:userId Request the basic informations for a user
	* @apiName getUserBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations for the given user
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token token of the person connected
	* @apiParam {Number} userId id of the user you want some informations
	*
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	* @apiSuccess {Date} birthday Birthday of the person
	* @apiSuccess {Text} avatar Avatr of the person
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {Number} phone Phone number of the person
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
	*			"return_message": "User - getuserbasicinformations - Complete Success"
	*		},
	*		"data": {
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"birthday": "1945-06-18"
	*			"avatar": "10001111001100110010101010",
	*			"email": "john.doe@gmail.com"
	*			"phone": "+33984231475",
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe"
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.2.3",
	*			"return_message": "User - getuserbasicinformations - Bad ID"
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
	public function getUserBasicInformationsAction(Request $request, $token, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.2.3", "User", "getuserbasicinformations"));

		$userInfos = $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:User')->find($userId);
		if ($userInfos === null)
			return $this->setBadRequest("7.2.4", "User", "getuserbasicinformations", "Bad Parameter: userId");

		$firstName = $userInfos->getFirstname();
		$lastName = $userInfos->getLastname();
		if ($userInfos->getBirthday() instanceof DateTime)
			$birthday = $userInfos->getBirthday()->format('Y-m-d');
		else
			$birthday = null;
		$avatar = $userInfos->getAvatar();
		$email = $userInfos->getEmail();
		$phone = $userInfos->getPhone();
		$country = $userInfos->getCountry();
		$linkedin = $userInfos->getLinkedin();
		$viadeo = $userInfos->getViadeo();
		$twitter = $userInfos->getTwitter();

		return $this->setSuccess("1.7.1", "User", "getuserbasicinformations", "Complete Success", array("firstname" => $firstName, "lastname" => $lastName, "birthday" => $birthday,
			"avatar" => $avatar, "email" => $email, "phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	* @api {put} /V0.2/user/basicinformations/:token Update the basic informations of the user connected
	* @apiName putBasicInformations
	* @apiGroup Users
	* @apiDescription Update the basic informations of the user connected
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} [firstname] First name of the person
	* @apiParam {String} [lastname] Last name of the person
	* @apiParam {Date} [birthday] Birthday of the person
	* @apiParam {Text} [avatar] Avatar of the person
	* @apiParam {String} [email] Email of the person
	* @apiParam {String} [password] Password of the person
	* @apiParam {Number} [phone] Phone number of the person
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
	*			"email": "john.doe@gmail.com",
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
	* @apiSuccess {String} firstname First name of the person
	* @apiSuccess {String} lastname Last name of the person
	* @apiSuccess {Date} birthday Birthday of the person
	* @apiSuccess {Text} avatar Avatr of the person
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {Number} phone Phone number of the person
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
	*			"avatar": "10001111001100110010101010",
	*			"email": "john.doe@gmail.com"
	*			"phone": "+33984231475",
	*			"country": "France",
	*			"linkedin": "linkedin.com/john.doe",
	*			"viadeo": "viadeo.com/john.doe",
	*			"twitter": "twitter.com/john.doe"
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.1.3",
	*			"return_message": "User - putbasicinformations - Bad ID"
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
			$user->setAvatar($content->avatar);
		if (array_key_exists('email', $content) && $user->getEmail() != $content->email)
		{
			if ($em->getRepository('GrappboxBundle:User')->findOneBy(array('email' => $content->email)))
				return $this->setBadRequest("Email already in DB");
			else if ($content->email != "")
				return $this->setBadRequest("Email invalid");
			else
				$user->setEmail($content->email);

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
		if (array_key_exists('password', $content))
		{
			$encoder = $this->container->get('security.password_encoder');
   			$encoded = $encoder->encodePassword($user, $content->password);
			$user->setPassword($encoded);
		}

		$em->flush();

		$id = $user->getId();
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
		if ($birthday!= null)
			$birthday = $birthday->format('Y-m-d');
		$avatar = $user->getAvatar();
		$email = $user->getEmail();
		$phone = $user->getPhone();
		$country = $user->getCountry();
		$linkedin = $user->getLinkedin();
		$viadeo = $user->getViadeo();
		$twitter = $user->getTwitter();

		return $this->setSuccess("1.7.1", "User", "putbasicinformations", "Complete Success", array("id" => $id, "firstname" => $firstName, "lastname" => $lastName, "birthday" => $birthday,
			"avatar" => $avatar, "email" => $email, "phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	* @api {get} /V0.2/user/getidbyname/:token/:firstName/:lastName Request the user Id with the first and last name
	* @apiName getIdByName
	* @apiGroup Users
	* @apiDescription Request the user Id with the first name and the last name
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user's authentication token
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
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.4.3",
	*			"return_message": "User - getidbyname - Bad ID"
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
	public function getIdByNameAction(Request $request, $token, $firstname, $lastname)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.4.3", "User", "getidbyname"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:User');
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
	* @api {get} /V0.2/user/getidbyemail/:token/:email Request the user Id with the email
	* @apiName getIdByEmail
	* @apiGroup Users
	* @apiDescription Request the user Id with the email
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user's authentication token
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
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "7.5.3",
	*			"return_message": "User - getidbyemail - Bad ID"
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
	public function getIdByEmailAction(Request $request, $token, $email)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.5.3", "User", "getidbyemail"));

		$userEmail = $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:User')->findOneByEmail($email);

		if ($userEmail === null)
			return $this->setBadRequest("7.5.4", "User", "getidbyemail", "Bad Parameter: email");

		$id = $userEmail->getId();
		$firstname = $userEmail->getFirstname();
		$lastname = $userEmail->getLastname();

		return $this->setSuccess("1.7.1", "User", "getidbyemail", "Complete Success", array("id" => $id, "firstname" => $firstname, "lastname" => $lastname));
	}

	/**
	* @api {get} /V0.2/user/getnextmeetings/:token Request the next meetings of the connected user
	* @apiName getNextMeetings
	* @apiGroup Users
	* @apiDescription Request the next meetings of the connected user
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} array array of events
	* @apiSuccess {Object[]} projects Project informations
	* @apiSuccess {String} array.projects.name Name of the project
	* @apiSuccess {Text} array.projects.logo Logo of the project
	* @apiSuccess {String} array.type Type of meeting
	* @apiSuccess {String} array.title Title of the meeting
	* @apiSuccess {String} array.description Description of the event
	* @apiSuccess {Datetime} array.begin_date Date of the begining of the meeting
	* @apiSuccess {Datetime} array.end_date Date of meeting's ending
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getnextmeetings - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"projects": {
	*						"name": "Grappbox",
	*						"logo": "DATA"
	*					},
	*					"type": "Client",
	*					"title": "Cahier Des Charges",
	*					"description": "Mise à jour du CDC avec le client",
	*					"begin_date": {
	*						"date": "1945-06-18 08:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"end_date": {
	*						"date": "1945-06-18 18:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					}
	*				},
	*				{
	*					"projects": {
	*						"name": "",
	*						"logo": ""
	*					},
	*					"type": "Personnel",
	*					"title": "Dentiste",
	*					"description": "Rdv avec le dentiste pour changer ma couronne",
	*					"begin_date": {
	*						"date": "1946-06-18 09:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"end_date": {
	*						"date": "1946-06-18 11:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.7.3",
	*			"return_message": "User - getnextmeetings - No Data Success"
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
	*			"return_code": "7.6.3",
	*			"return_message": "User - getnextmeetings - Bad ID"
	*		}
	*	}
	*/
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.6.3", "User", "getnextmeetings"));

		return $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Event')->findNextMeetingsV2($user->getId(), "7", "User", "getnextmeetings");
	}

	/**
	* @api {get} /V0.2/user/getprojects/:token Request the user connected projects
	* @apiName getProjects
	* @apiGroup Users
	* @apiDescription Request all the user's connected projects
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} array Array of projects
	* @apiSuccess {Number} array.id Id of the project
	* @apiSuccess {String} array.name Name of the project
	* @apiSuccess {String} array.description Description of the project
	* @apiSuccess {Object[]} array.creator Informations about the creator
	* @apiSuccess {Number} array.creator.id Id of the creator
	* @apiSuccess {String} array.creator.firstname Firstname of the creator
	* @apiSuccess {String} array.creator.lastname Lastname of the creator
	* @apiSuccess {String} array.phone Phone of the project
	* @apiSuccess {String} array.company Company of the project
	* @apiSuccess {String} array.logo Logo of the project
	* @apiSuccess {String} array.contact_mail Mail for the project
	* @apiSuccess {String} array.facebook Facebook of the project
	* @apiSuccess {String} array.twitter Twitter of the project
	* @apiSuccess {Datetime} array.deleted_at Date of deletion of the project
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getprojects - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 2,
	*					"name": "Grappbox",
	*					"description": "Grappbox est une application de gestion de projet.",
	*					"creator": {
	*						"id": 2,
	*						"firstname": "John",
	*						"lastname": "Snow"
	*					}
	*					"phone": "+339 46 12 45 78",
	*					"company": "Ubisoft",
	*					"logo": "DATA",
	*					"contact_mail": "contact@grappbox.com",
	*					"facebook": "www.facebook.com/GrappBox",
	*					"twitter": "twitter.com/GrappBox",
	*					"deleted_at": null
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.7.3",
	*			"return_message": "User - getprojects - No Data Success"
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
	*			"return_code": "7.7.3",
	*			"return_message": "User - getprojects - Bad ID"
	*		}
	*	}
	*/
	public function getProjectsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.7.3", "User", "getprojects"));

		return $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Project')->findUserProjectsV2($user->getId(), "7", "User", "getprojects");
	}

	/**
	* @api {get} /V0.2/user/getalltasks/:token Request the user connected tasks
	* @apiName getAllTasks
	* @apiGroup Users
	* @apiDescription Request the user connected tasks
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} array Array of tasks
	* @apiSuccess {Number} array.id Id of the task
	* @apiSuccess {String} array.title Title of the task
	* @apiSuccess {String} array.description Description of the task
	* @apiSuccess {Object[]} array.project Project Informations
	* @apiSuccess {Number} array.project.id Project id link to the task
	* @apiSuccess {String} array.project.name Project's name
	* @apiSuccess {Datetime} array.due_date Due date for the task
	* @apiSuccess {Datetime} array.started_at Begining of the task
	* @apiSuccess {Datetime} array.finished_at Task finished date
	* @apiSuccess {Datetime} array.created_at Date of creation of the task
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getalltasks - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 2,
	*					"title": "Whiteboard API",
	*					"description": "Implémentation de la partie whiteboard de l'API",
	*					"project": {
	*						"id": 3,
	*						"name": "Grappbox"
	*					},
	*					"due_date": {"date": "1947-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"started_at": {"date": "1945-06-18 18:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"finished_at": {"date": "1946-12-24 16:28:78", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"created_at": {"date": "1945-06-18 15:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
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
	*			"return_message": "User - getalltasks - No Data Success"
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
	*			"return_code": "7.8.3",
	*			"return_message": "User - getalltasks - Bad ID"
	*		}
	*	}
	*/
	public function getAllTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.8.3", "User", "getalltasks"));

		return $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Task')->findUserAllTasksV2($user->getId(), "7", "User", "getalltasks");
	}

	/**
	* @api {get} /V0.2/user/getcurrentandnexttasks/:token Request the user connected current and next tasks
	* @apiName getCurrentAndNextTasks
	* @apiGroup Users
	* @apiDescription Request the user connected current and next tasks
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} array Array of tasks
	* @apiSuccess {Number} array.id Id of the task
	* @apiSuccess {String} array.title Title of the task
	* @apiSuccess {String} array.description Description of the task
	* @apiSuccess {Object[]} array.project Project informations
	* @apiSuccess {Number} array.project.id Project id link to the task
	* @apiSuccess {String} array.project.name Project's name
	* @apiSuccess {Datetime} array.due_date Due date for the task
	* @apiSuccess {Datetime} array.started_at Begining of the task
	* @apiSuccess {Datetime} array.finished_at Task finished date
	* @apiSuccess {Datetime} array.created_at Date of creation of the task
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.7.1",
	*			"return_message": "User - getcurrentandnexttasks - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 2,
	*					"title": "Whiteboard API",
	*					"description": "Implémentation de la partie whiteboard de l'API",
	*					"project": {
	*						"id": 3,
	*						"name": "Grappbox"
	*					},
	*					"due_date": {"date": "1947-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"started_at": {"date": "1945-06-18 18:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"finished_at": {"date": "1946-12-24 16:28:78", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"created_at": {"date": "1945-06-18 15:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
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
	*			"return_message": "User - getcurrentandnexttasks - No Data Success"
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
	*			"return_code": "7.9.3",
	*			"return_message": "User - getcurrentandnexttasks - Bad ID"
	*		}
	*	}
	*/
	public function getCurrentAndNextTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.9.3", "User", "getcurrentandnexttasks"));

		return $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Task')->findUserCurrentAndNextTasksV2($user->getId(), "7", "User", "getcurrentandnexttasks");
	}
}
