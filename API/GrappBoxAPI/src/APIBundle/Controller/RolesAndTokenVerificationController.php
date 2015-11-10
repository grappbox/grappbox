<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RolesAndTokenVerificationController extends Controller
{
  // return user if token is correct
  // return null if token is incorrect
  protected function checkToken($token)
  {
    if (!$token)
      return NULL;
    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $token));
    return $user;
  }

  // return 0 if user has no rigths on this role
  // return 1 if user has rights
  protected function checkRoles($user, $projectId, $role)
  {
    $em = $this->getDoctrine()->getManager();
    $query = $em->createQuery(
                      'SELECT roles.'.$role.'
                      FROM APIBundle:Role roles
                      JOIN APIBundle:ProjectUserRole projectUser WITH roles.id = projectUser.roleId
                      WHERE projectUser.projectId = '.$projectId.' AND projectUser.userId = '.$user->getId());
    $result = $query->setMaxResults(1)->getOneOrNullResult();
    return $result[$role];
  }

  protected function setBadTokenError()
  {
    $response = new JsonResponse('Bad Authentication Token', JsonResponse::HTTP_BAD_REQUEST);

    return $response;
  }

  protected function setNoRightsError()
  {
    $response = new JsonResponse('Insufficient User Rights', JsonResponse::HTTP_FORBIDDEN);

    return $response;
  }

  protected function setBadRequest($message)
  {
    $response = new JsonResponse($message, JsonResponse::HTTP_BAD_REQUEST);
    
    return $response;
  }

}
