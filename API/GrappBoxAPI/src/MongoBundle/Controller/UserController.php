<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\User;

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
		$em = $this->get('doctrine_mongodb')->getManager();

		if ($method == "GET")
			return $this->getBasicInformations($user);
		else if ($method == "PUT")
			return $this->putBasicInformations($content, $user, $em);
	}

	/**
	* @api {get} /mongo/user/basicinformations/:token Request the basic informations of the connected user
	*
	* @apiParam {String} token token of the person connected
	*/
	private function getBasicInformations($user)
	{
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday()->format('Y-m-d');
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
	* @api {get} /mongo/user/getuserbasicinformations/:token/:userId Request the basic informations for a user
	*
	* @apiParam {String} token token of the person connected
	* @apiParam {Number} userId id of the user you want some informations
	*/
	public function getUserBasicInformationsAction(Request $request, $token, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$userInfos = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:User')->find($userId);
		if ($userInfos === null)
		{
			throw new NotFoundHttpException("The user with id ".$userId." doesn't exist");
		}

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

		return new JsonResponse(array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday,
			"avatar" => $avatar, "email" => $email, "phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	* @api {put} /mongo/user/basicinformations/:token Update the basic informations of the user connected
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} [first_name] First name of the person
	* @apiParam {String} [last_name] Last name of the person
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
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday": "1945-06-18"
	*		"avatar": "10001111001100110010101010",
	*		"email": "john.doe@gmail.com",
	*		"password": "azertyuiop",
	*		"phone": +33984231475,
	*		"country": "France",
	*		"linkedin": "linkedin.com/john.doe",
	*		"viadeo": "viadeo.com/john.doe",
	*		"twitter": "twitter.com/john.doe"
	* 	}
	*/
	private function putBasicInformations($content, $user, $em)
	{
		if (array_key_exists('first_name', $content))
			$user->setFirstname($content->first_name);
		if (array_key_exists('last_name', $content))
			$user->setLastname($content->last_name);
		if (array_key_exists('birthday', $content))
		{
			$birthday = date_create($content->birthday);
			$user->setBirthday($birthday);
		}
		if (array_key_exists('avatar', $content))
			$user->setAvatar($content->avatar);
		if (array_key_exists('email', $content) && $user->getEmail() != $content->email)
		{
			if ($em->getRepository('MongoBundle:User')->findOneBy(array('email' => $content->email)))
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
		return new JsonResponse("User Basic Informations changed.");
	}

	/**
	* @api {get} /mongo/user/getidbyname/:token/:firstName/:lastName Request the user Id with the first and last name
	*
	* @apiParam {string} token user's authentication token
	* @apiParam {String} firstName first name of the user
	* @apiParam {String} lastName last name of the user
	*/
	public function getIdByNameAction(Request $request, $token, $firstname, $lastname)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:User')->findUserByName($firstname, $lastname));
	}

	/**
	* @api {get} /mongo/user/getidbyemail/:token/:email Request the user Id with the email
	*
	* @apiParam {string} token user's authentication token
	* @apiParam {String} email email of the user
	*/
	public function getIdByEmailAction(Request $request, $token, $email)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$userEmail = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:User')->findOneByEmail($email);

		if ($userEmail === null)
		{
			throw new NotFoundHttpException("The user with email ".$email." doesn't exist");
		}

		$id = $userEmail->getId();
		$firstname = $userEmail->getFirstname();
		$lastname = $userEmail->getLastname();

		return new JsonResponse(array("id" => $id, "first_name" => $firstname, "last_name" => $lastname));
	}

	/**
	* @api {get} /mongo/user/getnextmeetings/:token Request the next meetings of the connected user
	*
	* @apiParam {string} token user's authentication token
	*/
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Event')->findNextMeetings($user->getId()));
	}

	/**
	* @api {get} /mongo/user/getprojects/:token Request the user connected projects
	*
	* @apiParam {string} token user's authentication token
	*/
	public function getProjectsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->findUserProjects($user->getId()));
	}

	/**
	* @api {get} /mongo/user/getalltasks/:token Request the user connected tasks
	*
	* @apiParam {string} token user's authentication token
	*/
	public function getAllTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Task')->findUserAllTasks($user->getId()));
	}

	/**
	* @api {get} /mongo/user/getcurrentandnexttasks/:token Request the user connected current and next tasks
	*
	* @apiParam {string} token user's authentication token
	*/
	public function getCurrentAndNextTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Task')->findUserCurrentAndNextTasks($user->getId()));
	}
}
