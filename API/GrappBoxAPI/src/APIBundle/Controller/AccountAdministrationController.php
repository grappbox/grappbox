<?php

namespace APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APIBundle\Controller\RolesAndTokenVerificationController;
use APIBundle\Entity\User;
use DateTime;

//use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
class AccountAdministrationController extends RolesAndTokenVerificationController
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
 	* @api {post} V0.6/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.6.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
 	*
 	* @apiSuccess {Object} user user's information
 	* @apiSuccess {int} user.id whiteboard id
 	* @apiSuccess {string} user.firstname user's firstname
 	* @apiSuccess {string} user.lastname user's lastname
 	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
 	*
 	* @apiSuccessExample {json} Success-Response:
 	* 	{
 	*			"user": {
	*				"id": 12,
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"email": "john.doe@gmail.com",
	*				"token": "fkE35dcDneOjF...."
	*			}
 	* 	}
 	*
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/
	public function loginAction(Request $request)
	{
		  $em = $this->getDoctrine()->getManager();
		  $user = $em->getRepository('APIBundle:User')->findOneBy(array('email' => $request->request->get('login')));
			if (!$user)
			{
				$response = new JsonResponse('Bad Login', JsonResponse::HTTP_BAD_REQUEST);
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

					$response = new JsonResponse();
					$response->setData(array('user' => $user->objectToArray()));
					return $response;
			}
			else
			{
				$response = new JsonResponse('Bad Password', JsonResponse::HTTP_BAD_REQUEST);
				return $response;
			}
	}

	 /**
 	* @api {post} V0.6/accountadministration/logout Request logout
 	* @apiName logout
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.6.0
 	*
 	* @apiParam {string} _token user's authentication token
 	*
 	* @apiSuccess {string} data	success message
 	*
	* @apiSuccessExample {json} Success-Response:
	* 	HTTP/1.1 200 OK
	* 	{
	* 		"Logout Successfully"
	* 	}
 	*
	* @apiErrorExample Bad Authentication Token
 	* 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
 	*
 	*/
 	public function logoutAction(Request $request)
 	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		$user->setToken(null);

		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

		$response = new JsonResponse();
		$response->setData('Logout Successfully');
		return $response;
 	}

	 /**
		* @api {post} V0.6/accountadministration/signin Request user creation and login
		* @apiName signin
		* @apiGroup AccountAdministration
		* @apiVersion 0.6.0
		*
		* @apiParam {string} firstname user's firstname
		* @apiParam {string} lastname user's lastname
		* @apiParam {DateTime} [birthday] user's birthday
		* @apiParam {file} [avatar] user's avatar
		* @apiParam {string} password user's password
		* @apiParam {email} email user's email
		* @apiParam {string} [phone] user's phone
		* @apiParam {string} [country] user's country
		* @apiParam {url} [linkedin] user's linkedin
		* @apiParam {url} [viadeo] user's viadeo
		* @apiParam {url} [twitter] user's twitter
		*
		* @apiSuccess {Object} user user's informations
		* @apiSuccess {int} user.id whiteboard id
		* @apiSuccess {string} user.firstname user's firstname
		* @apiSuccess {string} user.lastname user's lastname
		* @apiSuccess {string} user.email user's email
		* @apiSuccess {string} user.token user's authentication token
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"user": {
		*			"id": 12,
		*			"firstname": "John",
		*			"lastname": "Doe",
		*			"email": "john.doe@gmail.com",
		*			"token": "fkE35dcDneOjF...."
		*		}
		* 	}
		*
		* @apiErrorExample Missing Parameter
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		*
		*/
	public function signInAction(Request $request)
	{
		if (!$request->request->get('firstname') || !$request->request->get('lastname') || !$request->request->get('password') || !$request->request->get('email'))
			return $this->setBadRequest("Missing Parameter");
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
		$response->setData(array('user' => $user->objectToArray()));
		return $response;
	}
}
