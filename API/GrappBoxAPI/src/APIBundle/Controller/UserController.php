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
	public function basicInformationsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		$method = $request->getMethod();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->find($id);
		$dataReceived = $request->request->get("basicInfos");

		if ($user === null)
		{
			throw new NotFoundHttpException("The user with id ".$id." doesn't exist");
		}

		if ($method == "GET")
			return new JsonResponse($this->getBasicInformations($user));
		else if ($method == "PUT")
				return new JsonResponse($this->putBasicInformations($dataReceived, $user, $em));
		else
			return header("HTTP/1.0 404 Not Found", True, 404);
	}

	/**
	* @api {get} /V1/API/User/basicInformations/:id Request the basic informations of a user
	* @apiName getBasicInformations
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {String} _token token of the person connected
	*
	* @apiSuccess {String} first_name First name of the person
	* @apiSuccess {String} last_name Last name of the person
	* @apiSuccess {Datetime} [birthday] Birthday of the person
	* @apiSuccess {Text} [avatar] Avatr of the person
	* @apiSuccess {String} email Email of the person
	* @apiSuccess {Number} [phone] Phone number of the person
	* @apiSuccess {String} [country] Country the person in living in
	* @apiSuccess {String} [linkedin] Linkedin of the person
	* @apiSuccess {String} [viadeo] Viadeo of the person
	* @apiSuccess {String} [twitter] Twitter of the person
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
	* @apiError message The user with id $id doesn't exist.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
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

		return array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday,
			"avatar" => $avatar, "email" => $email, "phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter);
	}

	/**
	* @api {put} /V1/API/User/basicInformations/:id Update the basic informations of a user
	* @apiName putBasicInformations
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {String} _token Token of the person connected
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
	*		"_token": "f1a3f1ea35fae31f"
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
	* @apiSuccess message User Basic Informations changed.
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"message" : "User Basic Informations changed."
	*	  }
	*
	* @apiError message The user with id $id doesn't exist.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	private function putBasicInformations($dataReceived, $user, $em)
	{
		foreach ($dataReceived as $key => $value) {
			switch ($key) {
				case 'firstname':
					$user->setFirstname($dataReceived["firstname"]);
					break;
				case 'lastname':
					$user->setLastname($dataReceived["lastname"]);
					break;
				case 'birthday':
					$user->setBirthday($dataReceived["birthday"]);
					break;
				case 'avatar':
					$user->setAvatar($dataReceived["avatar"]);
					break;
				case 'email':
					$user->setEmail($dataReceived["email"]);
					break;
				case 'phone':
					$user->setPhone($dataReceived["phone"]);
					break;
				case 'country':
					$user->setCountry($dataReceived["country"]);
					break;
				case 'linkedin':
					$user->setLinkedin($dataReceived["linkedin"]);
					break;
				case 'viadeo':
					$user->setViadeo($dataReceived["viadeo"]);
					break;
				case 'twitter':
					$user->setTwitter($dataReceived["twitter"]);
					break;
				default:
					break;
			}
		}
		$em->flush();
		return "User Basic Informations changed.";
	}

	public function passwordAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		$method = $request->getMethod();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->find($id);

		if ($user === null)
		{
			throw new NotFoundHttpException("The user with id ".$id." doesn't exist");
		}

		if ($method == "GET")
			return new JsonResponse($this->getPassword($user));
		else if ($method == "PUT")
			return new JsonResponse($this->putPassword($request, $user, $em));
		else
			return header("HTTP/1.0 404 Not Found", True, 404);
	}

	/**
	* @api {get} /V1/API/User/password/:id Request the password of a user
	* @apiName getPassword
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {String} _token Token of the person connected
	*
	* @apiSuccess {String} password Person's password
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"password": "toto42"
	* 	}
	*
	* @apiError message The user with id $id doesn't exist.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	private function getPassword($user)
	{
		return array("password" => $user->getPassword());
	}

	/**
	* @api {put} /V1/API/User/password/:id Update the password of a user
	* @apiName putPassword
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {String} _token Token of the person connected
	* @apiParam {String} password The new password
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"_token": "12f3qef13eqf1",
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
	* @apiError message The user with id $id doesn't exist.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	private function putPassword($request, $user, $em)
	{
		$user->setPassword($request->request->get('password'));

		$em->flush();
		return "Password successfully changed.";
	}

	/**
	* @api {get} /V1/API/User/getIdByName/:firstName.:lastName Request the user Id with the first and last name
	* @apiName getIdByName
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {string} _token user's authentication token
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
	* @apiError message 404 not found.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	public function getIdByNameAction(Request $request, $firstname, $lastname)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:User')->findUserByName($firstname, $lastname));
	}

	/**
	* @api {get} /V1/API/User/getNextMeetings/:id Request the next meetings of a user
	* @apiName getNextMeetings
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {string} _token user's authentication token
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
	* @apiError message The user with id $id doesn't exist.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	public function getNextMeetingsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Event')->findNextMeetings($id));
	}

	/**
	* @api {get} /V1/API/User/getProjects/:id Request the user's projects with the user's id
	* @apiName getProjects
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {string} _token user's authentication token
	*
	* @apiSuccess {Object[]} Project array of n project
	* @apiSuccess {Number} Project.project_id id of the project
	* @apiSuccess {String} Project.project_name Name of the project
	* @apiSuccess {String} Project.project_description Description of the project
	* @apiSuccess {String} Project.project_logo Logo of the project
	* @apiSuccess {String} Project.contact_mail Mail for the project
	* @apiSuccess {String} Project.facebook Facebook of the project
	* @apiSuccess {String} Project.twitter Twitter of the project
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"Project 1":
	*		{
	*			"project_id": 2,
	*			"project_name": "Grappbox",
	*			"project_description": "Grappbox est une application de gestion de projet.",
	*			"project_logo": "Grappbox.com/logo.png",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "www.facebook.com/GrappBox",
	*			"twitter": "twitter.com/GrappBox"
	*		}
	* 	}
	*
	* @apiError message 404 not found.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	public function getProjectsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Project')->findUserProjects($id));
	}

	/**
	* @api {get} /V1/API/User/getAllTasks/:id Request the user's tasks with the user's id
	* @apiName getAllTasks
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {string} _token user's authentication token
	*
	* @apiSuccess {Object[]} Task array of n project
	* @apiSuccess {Number} Task.task_id id of the task
	* @apiSuccess {String} Task.task_title title of the task
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
	*			"task_id": 2,
	*			"task_title": "Whiteboard API",
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
	* @apiError message 404 not found.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	public function getAllTasksAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Task')->findUserAllTasks($id));
	}

	/**
	* @api {get} /V1/API/User/getCurrentAndNextTasks/:id Request the user's current and next tasks with the user's id
	* @apiName getCurrentAndNextTasks
	* @apiGroup Users
	* @apiVersion 0.0.1
	*
	* @apiParam {string} _token user's authentication token
	* 
	* @apiSuccess {Object[]} Task array of n project
	* @apiSuccess {Number} Task.task_id id of the task
	* @apiSuccess {String} Task.task_title title of the task
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
	*			"task_id": 2,
	*			"task_title": "Whiteboard API",
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
	* @apiError message 404 not found.
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
	public function getCurrentAndNextTasksAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Task')->findUserCurrentAndNextTasks($id));
	}
}
