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
	public function passwordEncryptAction(Request $request, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();
		$user = $em->getRepository("MongoBundle:User")->findOneBy(array('id' => $id));

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
		$em = $this->get('doctrine_mongodb')->getManager();

		if ($method == "GET")
			return $this->getBasicInformations($user);
		else if ($method == "PUT")
			return $this->putBasicInformations($content, $user, $em);
	}

	/**
	* @-api {get} /0.3/user Request the basic informations of the connected user
	* @apiName getBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations of the connected user
	* @apiVersion 0.3.0
	*
	*/
	private function getBasicInformations($user)
	{
		return $this->setSuccess("1.7.1", "User", "getbasicinformations", "Complete Success", $user->fullObjectToArray());
	}

	/**
	* @-api {get} /0.3/user/:userId Request the basic informations for a user
	* @apiName getUserBasicInformations
	* @apiGroup Users
	* @apiDescription Request the basic informations for the given user
	* @apiVersion 0.3.0
	*
	*/
	public function getUserBasicInformationsAction(Request $request, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.2.3", "User", "getuserbasicinformations"));

		$userInfos = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:User')->find($userId);
		if ($userInfos === null)
			return $this->setBadRequest("7.2.4", "User", "getuserbasicinformations", "Bad Parameter: userId");

		return $this->setSuccess("1.7.1", "User", "getuserbasicinformations", "Complete Success", $userInfos->fullObjectToArray());
	}

	/**
	* @-api {put} /0.3/user Update the basic informations of the user connected
	* @apiName putBasicInformations
	* @apiGroup Users
	* @apiDescription Update the basic informations of the user connected
	* @apiVersion 0.3.0
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
		{
			$filepath = "/var/www/static/app/user/".$id;

			$file = base64_decode($content->avatar);
			if ($file == false)
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: avatar");

			$image = imagecreatefromstring($file);
			if ($image == false)
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: avatar");

			if (!imagejpeg($image, $filepath, 80))
				return $this->setBadRequest("7.1.4", "User", "putbasicinformations", "Bad Parameter: avatar");

			imagedestroy($image);

			$fileurl = 'https://static.grappbox.com/app/user/'.$id;

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
				$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
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
	* @-api {get} /0.3/user/id/:firstName/:lastName Request the user Id with the first and last name
	* @apiName getIdByName
	* @apiGroup Users
	* @apiDescription Request the user Id with the first name and the last name
	* @apiVersion 0.3.0
	*
	*/
	public function getIdByNameAction(Request $request, $firstname, $lastname)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.4.3", "User", "getidbyname"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:User');
		$users = $repository->findBy(array('firstname' => $firstname, 'lastname' => $lastname));
		//$qb = $repository->createQueryBuilder('u')->where('u.firstname = :firstname', 'u.lastname = :lastname')->setParameter('firstname', $firstname)->setParameter('lastname', $lastname);
		//$users = $qb->getQuery()->execute();
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
	* @-api {get} /0.3/user/id/:email Request the user Id with the email
	* @apiName getIdByEmail
	* @apiGroup Users
	* @apiDescription Request the user Id with the email
	* @apiVersion 0.3.0
	*
	*/
	public function getIdByEmailAction(Request $request, $email)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
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
	* @-api {get} /0.3/user/avatar/:userId Get user avatar
	* @apiName getUserAvatar
	* @apiGroup Users
	* @apiDescription Get the avatar of the given user
	* @apiVersion 0.3.0
	*
	*/
	public function getUserAvatarAction(Request $request, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("7.9.3", "User", "getUserAvatar"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$requestedUser = $em->getRepository('MongoBundle:User')->find($userId);

		if ($requestedUser === null)
			return $this->setBadRequest("7.9.4", "User", "getUserAvatar", "Bad Parameter: userId");

		return $this->setSuccess("1.7.1", "User", "getUserAvatar", "Complete Success", array("avatar" => $requestedUser->getAvatar()));
	}

	/**
	* @-api {get} /0.3/user/project/avatars/:projectId Get all project user avatar
	* @apiName getAllProjectUserAvatar
	* @apiGroup Users
	* @apiDescription Get the avatar of all the users of the given project
	* @apiVersion 0.
	*
	*/
	public function getAllProjectUserAvatarAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
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
