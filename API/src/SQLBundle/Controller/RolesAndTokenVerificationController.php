<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use SQLBundle\Entity\Role;
use SQLBundle\Entity\ProjectUserRole;
use DateTime;
use DateInterval;

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
class RolesAndTokenVerificationController extends Controller
{
	// return user if token is correct
	// return null if token is incorrect
	protected function checkToken($token)
	{
		if (!$token)
			return NULL;
		$em = $this->getDoctrine()->getManager();
		$auth = $em->getRepository('SQLBundle:Authentication')->findOneBy(array('token' => $token));

		if (!$auth)
			return $auth;

		$now = new DateTime('now');
		if ($auth->getToken() && $auth->getTokenValidity() && $auth->getTokenValidity() < $now)
		{
			$auth->setToken(null);

			$em->persist($auth);
			$em->flush();

			return null;
		}
		else if ($auth->getToken() && $auth->getTokenValidity())
		{
			$auth->setTokenValidity($now->add(new DateInterval("P1D")));

			$em->persist($auth);
			$em->flush();
		}

		return $auth->getUser();
	}

	// return 0 if user has no rigths on this role
	// return 1 if user has readOnly rights
	// return 2 if user has read and writte rights
	protected function checkRoles($user, $projectId, $role)
	{
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT roles.'.$role.'
			FROM SQLBundle:Role roles
			JOIN SQLBundle:ProjectUserRole projectUser WITH roles.id = projectUser.roleId
			WHERE projectUser.projectId = '.$projectId.' AND projectUser.userId = '.$user->getId());
		$result = $query->setMaxResults(1)->getOneOrNullResult();
		return $result[$role];
	}

	protected function setBadTokenError($code, $part, $function)
	{
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - Bad Token");
		$response = new JsonResponse($ret);
		$response->setStatusCode(JsonResponse::HTTP_UNAUTHORIZED);

		return $response;
	}

	protected function setNoRightsError($code, $part, $function)
	{
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - Insufficient Rights");
		$response = new JsonResponse($ret);
		$response->setStatusCode(JsonResponse::HTTP_FORBIDDEN);

		return $response;
	}

	protected function setBadRequest($code, $part, $function, $message)
	{
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - ".$message);
		$response = new JsonResponse($ret);
		$response->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);

		return $response;
	}

	protected function setNoDataSuccess($code, $part, $function)
	{
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - "."No Data Success");
		$ret["data"] = array("array" => array());
		$response = new JsonResponse($ret);
		$response->setStatusCode(JsonResponse::HTTP_PARTIAL_CONTENT);

		return $response;
	}

	protected function setSuccess($code, $part, $function, $message, $data)
	{
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - ".$message);
		$ret["data"] = $data;
		$response = new JsonResponse($ret);
		$response->setStatusCode(JsonResponse::HTTP_OK);

		return $response;
	}

	protected function setCreated($code, $part, $function, $message, $data)
	{
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - ".$message);
		$ret["data"] = $data;
		$response = new JsonResponse($ret);
		$response->setStatusCode(JsonResponse::HTTP_CREATED);

		return $response;
	}

}
