<?php

namespace APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APIBundle\Entity\User;
use DateTime;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class AccountAdministrationController extends Controller
{

	public function loginFormAction()
    {
        return $this->render('APIBundle:AccountAdministrationController:login.html.twig', array(
                //
            ));
    }
	public function signInFormAction()
    {
        return $this->render('APIBundle:AccountAdministrationController:createUser.html.twig', array(
                //
            ));
    }

	 /**
 	* @api {post} /AccountAdministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 1.0.0
 	*
 	* @apiParam {email} login login
 	* @apiParam {string} password password
 	*
 	* @apiSuccess {Object[]} data the user
 	* @apiSuccess {int} data.id whiteboard id
 	* @apiSuccess {string} data.firstname user's firstname
 	* @apiSuccess {string} data.lastname user's lastname
 	* @apiSuccess {string} data.email user's email
	* @apiSuccess {string} data.token user's authentication token
 	*
 	* @apiSuccessExample {json} Success-Response:
 	* 	{
 	*		"data": [
 	*			"user": {
	*				"id": 12,
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"email": "john.doe@gmail.com",
	*				"token": "fkE35dcDneOjF...."
	*			}
 	*		]
 	* 	}
 	*
 	* @apiErrorExample Bad Email
 	*     HTTP/1.1 400 Bad Request
 	*     {
 	*       "data": "bad user"
 	*     }
	* @apiErrorExample Bad Password
 	*			HTTP/1.1 400 Bad Request
  * 		{
  *    		"data": "bad password"
  * 		}
 	*
 	*/
	 public function loginAction(Request $request)
	 {
		 	$response = new JsonResponse();
		  $em = $this->getDoctrine()->getManager();
		  $user = $em->getRepository('APIBundle:User')->findOneBy(array('email' => $request->request->get('login')));
			if (!$user)
			{
				$response->setData(array('status' => 'error', 'data' => 'bad user'));
				return $response;
			}

			if ($this->container->get('security.password_encoder')->isPasswordValid($user, $request->request->get('password')))
			{
					$secureUtils = $this->get('security.secure_random');
					$tmpToken = $secureUtils->nextBytes(25);
					$token = md5($tmpToken);
					$user->setToken($token);
					$em->persist($user);
		      $em->flush();
					$response->setData(array('status' => 'success', 'data' => array('user' => $user->serialize())));
					return $response;
			}
			else
			{
				$response->setData(array('status' => 'error', 'data' => 'bad password'));
				return $response;
			}
	 }

	 /**
 	* @api {any} /AccountAdministration/logout Request logout
 	* @apiName logout
 	* @apiGroup AccountAdministration
 	* @apiVersion 1.0.0
 	*
 	* @apiParam {string} _token user's authentication token
 	*
 	* @apiSuccess {string} data
 	*
 	* @apiSuccessExample {json} Success-Response:
 	* 	{
 	*		"data": "logout"
 	* 	}
 	*
 	* @apiErrorExample Bad Token
 	*     HTTP/1.1 400 Bad Request
 	*     {
 	*       "data": "bad token"
 	*     }
 	*
 	*/
 	public function logoutAction(Request $request)
 	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		$user->setToken(null);
		$em->persist($user);
		$em->flush();
		$response->setData(array('status' => 'success', 'data' => 'logout'));
		return $response;
 	}

	 /**
		* @api {post} /AccountAdministration/signin Request user creation and login
		* @apiName signin
		* @apiGroup AccountAdministration
		* @apiVersion 1.0.0
		*
		* @apiParam {string} firstname user's firstname
		* @apiParam {string} lastname user's lastname
		* @apiParam {DateTime} birthday user's birthday
		* @apiParam {file} avatar user's avatar
		* @apiParam {string} password user's password
		* @apiParam {email} email user's email
		* @apiParam {string} phone user's phone
		* @apiParam {string} country user's country
		* @apiParam {url} linkedin user's linkedin
		* @apiParam {url} viadeo user's viadeo
		* @apiParam {url} twitter user's twitter
		*
		* @apiSuccess {Object[]} data the user
		* @apiSuccess {int} data.id whiteboard id
		* @apiSuccess {string} data.firstname user's firstname
		* @apiSuccess {string} data.lastname user's lastname
		* @apiSuccess {string} data.email user's email
		* @apiSuccess {string} data.token user's authentication token
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"data": [
		*			"user": {
		*				"id": 12,
		*				"firstname": "John",
		*				"lastname": "Doe",
		*				"email": "john.doe@gmail.com",
		*				"token": "fkE35dcDneOjF...."
		*			}
		*		]
		* 	}
		*
		*
		*/
	public function signInAction(Request $request)
	{
			$user = new User();
      $user->setFirstname($request->request->get('firstname'));
      $user->setLastname($request->request->get('lastname'));
			if ($request->request->get('birthday'))
				$user->setBirthday(new Datetime($request->request->get('birthday')));

			if ($request->files->get('avatar'))
			{
				$generator = $this->get('security.secure_random');
	      $random = $generator->nextBytes(10);
	      $fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/avatars';
	      $fileName= md5($random).'.'.$request->files->get('avatar')->guessExtension();
	      $avatar = $request->files->get('avatar')->move($fileDir, $fileName);

	      $user->setAvatar($fileDir.'/'.$fileName);
			}

      $encoder = $this->container->get('security.password_encoder');
      $encoded = $encoder->encodePassword($user, $request->request->get('password'));
      $user->setPassword($encoded);

			$user->setEmail($request->request->get('email'));
			if ($request->request->get('phone'))
      	$user->setPhone($request->request->get('phone'));
			if ($request->request->get('country'))
      	$user->setCountry($request->request->get('country'));
			if ($request->request->get('linkedin'))
      	$user->setLinkedin($request->request->get('linkedin'));
			if ($request->request->get('viadeo'))
      	$user->setViadeo($request->request->get('viadeo'));
			if ($request->request->get('twitter'))
      	$user->setTwitter($request->request->get('twitter'));

			$secureUtils = $this->get('security.secure_random');
			$tmpToken = $secureUtils->nextBytes(25);
			$token = md5($tmpToken);
			$user->setToken($token);

      $em = $this->getDoctrine()->getManager();
      $em->persist($user);
      $em->flush();


			$response = new JsonResponse();
			$response->setData(array('status' => 'success', 'data' => array('user' => $user->serialize())));
			return $response;
      // $providerKey = 'default'; // your firewall name
      // $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
      // $this->container->get('security.context')->setToken($token);

      // return $this->render('AppBundle:UserController:homeUser.html.twig',
      //     array(
      //         'avatar' => $user->getAvatar()
      //     ));
	}

}
