<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APIBundle\Entity\Whiteboard;
use DateTime;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class WhiteboardController extends Controller
{

	private function checkUserAutorisation($em, $user, $projectId)
	{
		$query = $em->createQuery(
									    'SELECT roles.whiteboard
									    FROM APIBundle:Role roles
											JOIN APIBundle:ProjectUserRole projectUser WITH roles.id = projectUser.roleId
									    WHERE projectUser.projectId = '.$projectId.' AND projectUser.userId = '.$user->getId());
		$result = $query->setMaxResults(1)->getOneOrNullResult();
		return $result['whiteboard'];
	}

	private function serializeInArray($objects)
	{
		$content = array();
		foreach ($objects as $key => $value) {
			$content[] = $value->serialize();
		}
		return $content;
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="list all whiteboard of a project",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * },
		 * parameters={
		 *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
	   *      {"name"="projectId", "dataType"="int(11)", "required"=true, "description"="related project id"}
		 *  }
     * )
	 *
	 */
	 public function listWhiteboardAction(Request $request)
	 {
			 $response = new JsonResponse();
			 $em = $this->getDoctrine()->getManager();
			 $user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
			 if (!$user)
			 {
			 	$response->setData(array('status' => 'error', 'data' => 'bad token'));
			 	return $response;
			 }
			 if (!$this->checkUserAutorisation($em, $user, $request->request->get('projectId')))
			 {
			 	$response->setData(array('status' => 'error', 'data' => 'no rights'));
			 	return $response;
			 }
			 $project = $em->getRepository('APIBundle:Project')->find($request->request->get('projectId'));
			 $whiteboards = $project->getWhiteboards();
			 $response->setData(array('status' => 'success', 'data' => $this->serializeInArray($whiteboards)));
			 return $response;
	 }

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add a new whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * },
		 * parameters={
		 *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
	   *      {"name"="projectId", "dataType"="int(11)", "required"=true, "description"="related project id"},
		 *      {"name"="whiteboardName", "dataType"="varchar(255)", "required"=true, "description"="whiteboard name"},
		 *  }
     * )
	 *
	 */
	public function newWhiteboardAction(Request $request)
	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
	  $user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		if (!$this->checkUserAutorisation($em, $user, $request->request->get('projectId')))
		{
			$response->setData(array('status' => 'error', 'data' => 'no rights'));
			return $response;
		}

		$whiteboard = new Whiteboard();
		$whiteboard->setProjectId($request->request->get('projectId'));
		$whiteboard->setUserId($user->getId());
		$whiteboard->setUpdatorId($user->getId());
		$whiteboard->setName($request->request->get('whiteboardName'));
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em->persist($whiteboard);
		$em->flush();

		$response->setData(array('status' => 'success', 'data' => array('whiteboard' => $whiteboard->serialize(), 'content' => array())));
		return $response;
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="open an already existing whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  },
		 * parameters={
		 *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"}
		 *  }
	 * )
	 *
	 */
	public function openWhiteboardAction(Request $request, $id)
	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if ($whiteboard->getDeletedAt())
		{
			$response->setData(array('status' => 'error', 'data' => 'deleted'));
			return $response;
		}
		if (!$this->checkUserAutorisation($em, $user, $whiteboard->getProjectId()))
		{
			$response->setData(array('status' => 'error', 'data' => 'no rights'));
			return $response;
		}
		$response->setData(array('status' => 'success', 'data' => array('whiteboard' => $whiteboard->serialize(), 'content' => $this->serializeInArray($whiteboard->getObjects()))));
		return $response;
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="push a draw on a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  },
		 * parameters={
		 *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
		 * 			{"name"="modification", "dataType"="varchar(255)", "required"=true, "description"="'add' or 'delete'"},
		 *			{"name"="object", "dataType"="varchar(255)", "required"=true, "description"="object to add (json array) if addition or object_id if deletion"}
		 *  }
	 * )
	 *
	 */
	public function pushDrawAction(Request $request, $id)
	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$this->checkUserAutorisation($em, $user, $whiteboard->getProjectId()))
		{
			$response->setData(array('status' => 'error', 'data' => 'no rights'));
			return $response;
		}
		if ($request->request->get('modification') == "add")
		{
			$object = new WhiteboardObject();
			$object->setWhiteboardId($id);
			$object->setObject($request->request->get('object'));
			$object->setCreatedAt(new DateTime('now'));
		}
		else {
			$object = $em->getRepository('APIBundle:WhiteboardObject')->find($request->request->get('object'));
			$object->setDelete(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		$response->setData(array('status' => 'succes', 'data' => 'success'));
		return $response;
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="pull a draw on a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  },
		 * parameters={
		 *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
		 *			{"name"="lastUpdate", "dataType"="varchar(255)", "required"=true, "description"="date of the last update format 'Y-m-d H:i:s'"}
		 *  }
	 * )
	 *
	 */
	public function pullDrawAction(Request $request, $id)
	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		if (!$this->checkUserAutorisation($em, $user, $whiteboard->getProjectId()))
		{
			$response->setData(array('status' => 'error', 'data' => 'no rights'));
			return $response;
		}

		$date = new \DateTime($request->request->get('lastUpdate'))
		$toAddQuery = $em->createQuery(
									    'SELECT objects.object
									    FROM APIBundle:WhiteboardObject objects
									    WHERE objects.whiteboardId = '.$id.' AND objects.createdAt > :date AND objects.deletedAt IS NULL')
											->setParameter('date', $date);
		$to_add = $toAddQuery->getResult();
		$toDelQuery = $em->createQuery(
									    'SELECT objects.object
									    FROM APIBundle:WhiteboardObject objects
									    WHERE objects.whiteboardId = '.$id.' AND objects.deletedAt > :date AND objects.deletedAt IS NOT NULL')
											->setParameter('date', $date);
		$to_del = $toDelQuery->getResult();

		$response->setData(array('status' => 'succes', 'data' => array('add' => $to_add, 'delete' => $to_del)));
		return $response;
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="exit a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  }
	 * )
	 *
	 */
	// public function exitWhiteboardAction(Request $request, $id)
	// {
	// 	$em = $this->getDoctrine()->getManager();
	// 	$user = $em->findBy('User', array('token' => $request->request->get('_token')));
	// 	if (!$user)
	// 		return new Response('Error, you\'re not login or have no right on this action');
	//
	// 	return new Response('exit Whiteboard '.$id.' Success');
	// }

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  },
		 * parameters={
	   *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
		 *  }
	 * )
	 *
	 */
	public function delWhiteboardAction(Request $request, $id)
	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		if (!$this->checkUserAutorisation($em, $user, $request->request->get('projectId')))
		{
			$response->setData(array('status' => 'error', 'data' => 'no rights'));
			return $response;
		}

		$whiteboard = $em->getRepository('APIBundle:Whiteboard')->find($id);
		if ($whiteboard)
		{
				$whiteboard->setDeletedAt(new DateTime('now'));
				$em->persist($whiteboard);
				// $em->remove($whiteboard);
				$em->flush();
		}
		$response->setData(array('status' => 'success', 'data' => 'success'));
		return $response;
	}
}
