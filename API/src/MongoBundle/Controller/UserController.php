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
 *  @IgnoreAnnotation("apiDescription")
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
		$em = $this->get('doctrine_mongodb')->getManager();

		if ($method == "GET")
			return $this->getBasicInformations($user);
		else if ($method == "PUT")
			return $this->putBasicInformations($content, $user, $em);
	}

	/**
	* @api {get} /mongo/user/basicinformations/:token Request the basic informations of the connected user
	* @apiName getBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations of the connected user
	* @apiVersion 0.2.0
	*
	*/
	private function getBasicInformations($user)
	{
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
		if ($birthday != null)
			$birthday = $birthday->format('Y-m-d');
		$avatar = $user->getAvatarDate();
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
	* @api {get} /mongo/user/getuserbasicinformations/:token/:userId Request the basic informations for a user
	* @apiName getUserBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations for the given user
	* @apiVersion 0.2.0
	*
	*/
	public function getUserBasicInformationsAction(Request $request, $token, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.2.3", "User", "getuserbasicinformations"));

		$userInfos = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:User')->find($userId);
		if ($userInfos === null)
			return $this->setBadRequest("7.2.4", "User", "getuserbasicinformations", "Bad Parameter: userId");

		$firstName = $userInfos->getFirstname();
		$lastName = $userInfos->getLastname();
		$birthday = $user->getBirthday();
		if ($birthday!= null)
			$birthday = $birthday->format('Y-m-d');
		$avatar = $userInfos->getAvatarDate();
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
	* @api {put} /mongo/user/basicinformations/:token Update the basic informations of the user connected
	* @apiName putBasicInformations
	* @apiGroup Users
	* @apiDescription Update the basic informations of the user connected
	* @apiVersion 0.2.0
	*
	*/
	private function putBasicInformations($content, $user, $em)
	{
		$content = $content->data;

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
			if ($this->container->get('security.password_encoder')->isPasswordValid($user, $content->oldPassword))
			{
				//print("op = password\n");
				$encoder = $this->container->get('security.password_encoder');
				$encoded = $encoder->encodePassword($user, $content->password);
				$user->setPassword($encoded);
			}
		}

		$em->flush();

		$id = $user->getId();
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
		if ($birthday!= null)
			$birthday = $birthday->format('Y-m-d');
		$avatar = $user->getAvatarDate();
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
	* @api {get} /mongo/user/getidbyname/:token/:firstName/:lastName Request the user Id with the first and last name
	* @apiName getIdByName
	* @apiGroup Users
	* @apiDescription Request the user Id with the first name and the last name
	* @apiVersion 0.2.0
	*
	*/
	public function getIdByNameAction(Request $request, $token, $firstname, $lastname)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.4.3", "User", "getidbyname"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:User');
		$qb = $repository->createQueryBuilder('u')->where('u.firstname = :firstname', 'u.lastname = :lastname')->setParameter('firstname', $firstname)->setParameter('lastname', $lastname);
		$users = $qb->getQuery()->execute();
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
	* @api {get} /mongo/user/getidbyemail/:token/:email Request the user Id with the email
	* @apiName getIdByEmail
	* @apiGroup Users
	* @apiDescription Request the user Id with the email
	* @apiVersion 0.2.0
	*
	*/
	public function getIdByEmailAction(Request $request, $token, $email)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.5.3", "User", "getidbyemail"));

		$userEmail = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:User')->findOneByEmail($email);

		if ($userEmail === null)
			return $this->setBadRequest("7.5.4", "User", "getidbyemail", "Bad Parameter: email");

		$id = $userEmail->getId();
		$firstname = $userEmail->getFirstname();
		$lastname = $userEmail->getLastname();

		return $this->setSuccess("1.7.1", "User", "getidbyemail", "Complete Success", array("id" => $id, "firstname" => $firstname, "lastname" => $lastname));
	}

	/**
	* @api {get} /mongo/user/getnextmeetings/:token Request the next meetings of the connected user
	* @apiName getNextMeetings
	* @apiGroup Users
	* @apiDescription Request the next meetings of the connected user
	* @apiVersion 0.2.0
	*
	*/
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.6.3", "User", "getnextmeetings"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Event')->findNextMeetingsV2($user->getId(), "7", "User", "getnextmeetings");
	}

	/**
	* @api {get} /mongo/user/getprojects/:token Request the user connected projects
	* @apiName getProjects
	* @apiGroup Users
	* @apiDescription Request all the user's connected projects
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.7.3", "User", "getprojects"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->findUserProjectsV2($user->getId(), "7", "User", "getprojects");
	}

	/**
	* @api {get} /mongo/user/getalltasks/:token Request the user connected tasks
	* @apiName getAllTasks
	* @apiGroup Users
	* @apiDescription Request the user connected tasks
	* @apiVersion 0.2.0
	*
	*/
	public function getAllTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.8.3", "User", "getalltasks"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Task')->findUserAllTasksV2($user->getId(), "7", "User", "getalltasks");
	}

	/**
	* @api {get} /mongo/user/getcurrentandnexttasks/:token Request the user connected current and next tasks
	* @apiName getCurrentAndNextTasks
	* @apiGroup Users
	* @apiDescription Request the user connected current and next tasks
	* @apiVersion 0.2.0
	*
	*/
	public function getCurrentAndNextTasksAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.9.3", "User", "getcurrentandnexttasks"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Task')->findUserCurrentAndNextTasksV2($user->getId(), "7", "User", "getcurrentandnexttasks");
	}

	/**
	* @api {get} /V0.2/user/getuseravatar/:token/:userId Get user avatar
	* @apiName getUserAvatar
	* @apiGroup Users
	* @apiDescription Get the avatar of the given user
	* @apiVersion 0.2.0
	*
	*/
	public function getUserAvatarAction(Request $request, $token, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.9.3", "User", "getUserAvatar"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$requestedUser = $em->getRepository('MongoBundle:User')->find($userId);

		if ($requestedUser === null)
			return $this->setBadRequest("7.9.4", "User", "getUserAvatar", "Bad Parameter: userId");

		return $this->setSuccess("1.7.1", "User", "getUserAvatar", "Complete Success", array("avatar" => $requestedUser->getAvatar()));
	}

	/**
	* @api {get} /V0.2/user/getallprojectuseravatar/:token/:projectId Get all project user avatar
	* @apiName getAllProjectUserAvatar
	* @apiGroup Users
	* @apiDescription Get the avatar of all the users of the given project
	* @apiVersion 0.2.0
	*
	*/
	public function getAllProjectUserAvatarAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("7.10.3", "User", "getAllProjectUserAvatar"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("7.10.4", "User", "getAllProjectUserAvatar", "Bad Parameter: projectId");

			foreach ($project->getUsers() as $key => $user) {
				$data[] = array("userId" => $user->getId(), "avatar" => $user->getAvatar());
			}

		return $this->setSuccess("1.7.1", "User", "getAllProjectUserAvatar", "Complete Success", array("array" => $data));
	}
}
