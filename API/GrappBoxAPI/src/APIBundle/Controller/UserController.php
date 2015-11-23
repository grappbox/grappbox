<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Controller\RolesAndTokenVerificationController;
use APIBundle\Entity\User;

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
class UserController extends RolesAndTokenVerificationController
{
	public function basicInformationsAction(Request $request, $token)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$method = $request->getMethod();
		$em = $this->getDoctrine()->getManager();

		if ($method == "GET")
			return new JsonResponse($this->getBasicInformations($user));
		else if ($method == "PUT")
			return new JsonResponse($this->putBasicInformations($content, $user, $em));
	}

	/**
	* @api {get} /V0.6/user/basicinformations/:token Request the basic informations of the connected user
	* @apiName getBasicInformations
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token token of the person connected
	*
	* @apiSuccess {String} first_name First name of the person
	* @apiSuccess {String} last_name Last name of the person
	* @apiSuccess {Datetime} birthday Birthday of the person
	* @apiSuccess {Text} avatar Avatr of the person
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {Number} phone Phone number of the person
	* @apiSuccess {String} country Country the person in living in
	* @apiSuccess {String} linkedin Linkedin of the person
	* @apiSuccess {String} viadeo Viadeo of the person
	* @apiSuccess {String} twitter Twitter of the person
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"avatar": "10001111001100110010101010",
	*		"email": "john.doe@gmail.com"
	*		"phone": "+33984231475",
	*		"country": "France",
	*		"linkedin": "linkedin.com/john.doe",
	*		"viadeo": "viadeo.com/john.doe",
	*		"twitter": "twitter.com/john.doe"
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	private function getBasicInformations($user)
	{
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
		$avatar = $user->getAvatar();
		$email = $user->getEmail();
		$phone = $user->getPhone();
		$country = $user->getCountry();
		$linkedin = $user->getLinkedin();
		$viadeo = $user->getViadeo();
		$twitter = $user->getTwitter();

		return new JsonResponse(array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday,
			"avatar" => $avatar, "email" => $email, "phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	* @api {put} /V0.6/user/basicinformations/:token Update the basic informations of the user connected
	* @apiName putBasicInformations
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} [first_name] First name of the person
	* @apiParam {String} [last_name] Last name of the person
	* @apiParam {Datetime} [birthday] Birthday of the person
	* @apiParam {Text} [avatar] Avatr of the person
	* @apiParam {String} [email] Email of the person
	* @apiParam {Number} [phone] Phone number of the person
	* @apiParam {String} [country] Country the person in living in
	* @apiParam {String} [linkedin] Linkedin of the person
	* @apiParam {String} [viadeo] Viadeo of the person
	* @apiParam {String} [twitter] Twitter of the person
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"avatar": "10001111001100110010101010",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33984231475,
	*		"country": "France",
	*		"linkedin": "linkedin.com/john.doe",
	*		"viadeo": "viadeo.com/john.doe",
	*		"twitter": "twitter.com/john.doe"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"message" : "User Basic Informations changed."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	private function putBasicInformations($content, $user, $em)
	{
		foreach ($content as $key => $value) {
			switch ($key) {
				case 'firstname':
					$user->setFirstname($content->firstname);
					break;
				case 'lastname':
					$user->setLastname($content->lastname);
					break;
				case 'birthday':
					$user->setBirthday($content->birthday);
					break;
				case 'avatar':
					$user->setAvatar($content->avatar);
					break;
				case 'email':
					$user->setEmail($content->email);
					break;
				case 'phone':
					$user->setPhone($content->phone);
					break;
				case 'country':
					$user->setCountry($content->country);
					break;
				case 'linkedin':
					$user->setLinkedin($content->linkedin);
					break;
				case 'viadeo':
					$user->setViadeo($content->viadeo);
					break;
				case 'twitter':
					$user->setTwitter($content->twitter);
					break;
				default:
					break;
			}
		}
		$em->flush();
		return new JsonResponse("User Basic Informations changed.");
	}

	public function passwordAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$method = $request->getMethod();
		$em = $this->getDoctrine()->getManager();

		if ($method == "GET")
			return new JsonResponse($this->getPassword($user));
		else if ($method == "PUT")
			return new JsonResponse($this->putPassword($request, $user, $em));
	}

	/**
	* @api {get} /V0.6/user/password/:token Request the password of the user connected
	* @apiName getPassword
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {String} password Person's password
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"password": "toto42"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	private function getPassword($user)
	{
		return array("password" => $user->getPassword());
	}

	/**
	* @api {put} /V0.6/user/password/:token Update the password of the user connected
	* @apiName putPassword
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} password The new password
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"password": "TarteAuxPommes"
	* 	}
	*
	* @apiSuccess message Password successfully changed.
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"message" : "Password successfully changed."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	private function putPassword($request, $user, $em)
	{
		$user->setPassword($request->request->get('password'));

		$em->flush();
		return new JsonResponse("Password successfully changed.");
	}

	/**
	* @api {get} /V0.6/user/getidbyname/:token/:firstName/:lastName Request the user Id with the first and last name
	* @apiName getIdByName
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {string} token user's authentication token
	* @apiParam {String} firstName first name of the user
	* @apiParam {String} lastName last name of the user
	*
	* @apiSuccess {Object[]} User array of n persons
	* @apiSuccess {Number} User.id id of the person
	* @apiSuccess {String} User.first_name First name of the person
	* @apiSuccess {String} User.last_name Last name of the person
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"User 1":
	*		{
	*			"id": 2,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		}
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function getIdByNameAction(Request $request, $token, $firstname, $lastname)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:User')->findUserByName($firstname, $lastname));
	}

	/**
	* @api {get} /V0.6/user/getnextmeetings/:token Request the next meetings of the connected user
	* @apiName getNextMeetings
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} Meeting array of n meeting
	* @apiSuccess {String} Meeting.project_name Name of the project
	* @apiSuccess {String} Meeting.project_logo Logo of the project
	* @apiSuccess {String} Meeting.event_type Type of meeting
	* @apiSuccess {String} Meeting.event_title Title of the meeting
	* @apiSuccess {String} Meeting.event_description Description of the event
	* @apiSuccess {Datetime} Meeting.event_begin_date Date of the begining of the meeting
	* @apiSuccess {Datetime} Meeting.event_end_date Date of meeting's ending
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"Meeting 1":
	*		{
	*			"project_name": "Grappbox",
	*			"project_logo": "Grappbox.com/logo.png",
	*			"event_type": "Client",
	*			"event_title": "Cahier Des Charges",
	*			"event_description": "Mise à jour du CDC avec le client",
	*			"event_begin_date": {"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"event_end_date": {"date": "1945-06-18 18:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		"Meeting 2":
	*		{
	*			"project_name": "",
	*			"project_logo": "",
	*			"event_type": "Personnel",
	*			"event_title": "Dentiste",
	*			"event_description": "Rdv avec le dentiste pour changer ma couronne",
	*			"event_begin_date": {"date": "1946-06-18 09:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"event_end_date": {"date": "1946-06-18 11:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		}
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Event')->findNextMeetings($user->getId()));
	}

	/**
	* @api {get} /V0.6/user/getprojects/:token Request the user connected projects
	* @apiName getProjects
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} Project array of n project
	* @apiSuccess {Number} Project.id id of the project
	* @apiSuccess {String} Project.name Name of the project
	* @apiSuccess {String} Project.description Description of the project
	* @apiSuccess {String} Project.logo Logo of the project
	* @apiSuccess {String} Project.contact_mail Mail for the project
	* @apiSuccess {String} Project.facebook Facebook of the project
	* @apiSuccess {String} Project.twitter Twitter of the project
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"Project 1":
	*		{
	*			"id": 2,
	*			"name": "Grappbox",
	*			"description": "Grappbox est une application de gestion de projet.",
	*			"logo": "Grappbox.com/logo.png",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "www.facebook.com/GrappBox",
	*			"twitter": "twitter.com/GrappBox"
	*		}
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function getProjectsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Project')->findUserProjects($user->getId()));
	}

	/**
	* @api {get} /V0.6/user/getalltasks/:token Request the user connected tasks
	* @apiName getAllTasks
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} Task array of n project
	* @apiSuccess {Number} Task.id id of the task
	* @apiSuccess {String} Task.title title of the task
	* @apiSuccess {String} Task.description Description of the task
	* @apiSuccess {Number} Task.project_id Project id link to the task
	* @apiSuccess {String} Task.project_name Project's name
	* @apiSuccess {Datetime} Task.due_date Due date for the task
	* @apiSuccess {Datetime} Task.started_at Begining of the task
	* @apiSuccess {Datetime} Task.finished_at Task finished date
	* @apiSuccess {Datetime} Task.created_at Date of creation of the task
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"Task 1":
	*		{
	*			"id": 2,
	*			"title": "Whiteboard API",
	*			"description": "Implémentation de la partie whiteboard de l'API",
	*			"project_id": 3,
	*			"project_name": "Grappbox",
	*			"due_date": {"date": "1947-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"started_at": {"date": "1945-06-18 18:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"finished_at": {"date": "1946-12-24 16:28:78", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"created_at": {"date": "1945-06-18 15:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function getAllTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Task')->findUserAllTasks($user->getId()));
	}

	/**
	* @api {get} /V0.6/user/getcurrentandnexttasks/:token Request the user connected current and next tasks
	* @apiName getCurrentAndNextTasks
	* @apiGroup Users
	* @apiVersion 0.6.0
	*
	* @apiParam {string} token user's authentication token
	*
	* @apiSuccess {Object[]} Task array of n project
	* @apiSuccess {Number} Task.id id of the task
	* @apiSuccess {String} Task.title title of the task
	* @apiSuccess {String} Task.description Description of the task
	* @apiSuccess {Number} Task.project_id Project id link to the task
	* @apiSuccess {String} Task.project_name Project's name
	* @apiSuccess {Datetime} Task.due_date Due date for the task
	* @apiSuccess {Datetime} Task.started_at Begining of the task
	* @apiSuccess {Datetime} Task.finished_at Task finished date
	* @apiSuccess {Datetime} Task.created_at Date of creation of the task
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"Task 1":
	*		{
	*			"id": 2,
	*			"title": "Whiteboard API",
	*			"description": "Implémentation de la partie whiteboard de l'API",
	*			"project_id": 3,
	*			"project_name": "Grappbox",
	*			"due_date": {"date": "1947-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"started_at": {"date": "1945-06-18 18:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"finished_at": {"date": "0000-00-00 00:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"created_at": {"date": "1945-06-18 15:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function getCurrentAndNextTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Task')->findUserCurrentAndNextTasks($user->getId()));
	}
}
