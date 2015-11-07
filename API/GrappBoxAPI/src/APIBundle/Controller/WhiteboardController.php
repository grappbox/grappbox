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
	* @api {get} /Whiteboard/list Request the list of whitebaord for a project
	* @apiName listWhiteboard
	* @apiGroup whiteboard
	* @apiVersion 1.0.0
	*
	* @apiParam {String} _token client authentification token
	* @apiParam {int} projectId id of the selected project
	*
	* @apiSuccess {String} status status of the request (error or success)
	* @apiSuccess {Object[]} data list of whiteboards informations
	* @apiSuccess {int} data.id whiteboard id
	* @apiSuccess {string} data.name whiteboard name
	* @apiSuccess {int} data.creator_id id of the whiteboard's creator
	* @apiSuccess {int} data.updator_id id of the whiteboard's last updator
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			"data": [
	*					"0": {
	*						"id": "12",
	*						"name": "Brainstorming #5",
	*						"creator_id": "65",
	*						"updator_id": "54"},
	*					"1": {
	*						"id": "12",
	*						"name": "Brainstorming #5",
	*						"creator_id": "65",
	*						"updator_id": "36"}
	*				]
	* 	}
	*
	* @apiErrorExample Bad Authentification Token
	* 	HTTP/1.1 400 Bad Request
	*   {
	*     "data": "bad token"
	*   }
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"data": "no rights"
  * 	}
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
 	* @api {post} /Whiteboard/new Request the creation of a new Whiteboard
 	* @apiName createWhiteboard
 	* @apiGroup whiteboard
 	* @apiVersion 1.0.0
 	*
 	* @apiParam {String} _token client authentification token
 	* @apiParam {int} projectId id of the selected project
	* @apiParam {string} whiteboardName name of the new whiteboard
 	*
 	* @apiSuccess {String} status status of the request (error or success)
 	* @apiSuccess {Object[]} data the new whiteboard informations and a content array (empty)
 	* @apiSuccess {int} data.id whiteboard id
 	* @apiSuccess {string} data.name whiteboard name
 	* @apiSuccess {int} data.creator_id id of the whiteboard's creator
 	* @apiSuccess {int} data.updator_id id of the whiteboard's last updator (creator)
 	*
 	* @apiSuccessExample {json} Success-Response:
 	* 	{
 	*		"data": [
 	*			"whiteboard": {
 	*				"id": "12",
 	*				"name": "Brainstorming #5",
 	*				"creator_id": "65",
 	*				"updator_id": "54"},
 	*			"content": [ ]
 	*		]
 	* 	}
 	*
 	* @apiErrorExample Bad Authentification Token
 	*     HTTP/1.1 400 Bad Request
 	*     {
 	*       "data": "bad token"
 	*     }
	* @apiErrorExample Insufficient User Rights
 	*			HTTP/1.1 400 Bad Request
  * 		{
  *    		"data": "no rights"
  * 		}
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
		* @api {post} /Whiteboard/open/:id Request open a whiteboard
		* @apiName openWhiteboard
		* @apiGroup whiteboard
		* @apiVersion 1.0.0
		*
		* @apiParam {String} _token client authentification token
		*
		* @apiSuccess {String} status status of the request (error or success)
		* @apiSuccess {Object[]} data the new whiteboard informations and a content array (empty)
		* @apiSuccess {Object} data.whiteboard whiteboard information and content
		* @apiSuccess {int} data.whiteboard.id whiteboard id
		* @apiSuccess {string} data.whiteboard.name whiteboard name
		* @apiSuccess {int} data.whiteboard.creator_id id of the whiteboard's creator
		* @apiSuccess {int} data.whiteboard.updator_id id of the whiteboard's last updator
		* @apiSuccess {Object[]} data.content content whiteboard content objects
		* @apiSuccess {object} data.content.object object object
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"data": [
		*			"whiteboard": {
		*				"id": "12",
		*				"name": "Brainstorming #5",
		*				"creator_id": "65",
		*				"updator_id": "54"},
		*			"content": ["0": {
		*										"id": 12,
		*										"type": rectangle,
		*										"color": "125,25,65",
		*										"line": "1.5",
		*										"position": "15;63.3",
		*										"..."	},
		*									"1": {
		*										"id": 12,
		*										"type": circle,
		*										"color": "125,25,65",
		*										"line": "1.5",
		*										"position": "186.20;42.95",
		*										"..."
		*									},
		*									...
		*			]
		*		]
		* 	}
		*
		* @apiErrorExample Bad Authentification Token
		*     HTTP/1.1 400 Bad Request
		*     {
		*       "data": "bad token"
		*     }
		* @apiErrorExample Insufficient User Rights
		*			HTTP/1.1 400 Bad Request
	 	* 		{
	 	*    		"data": "no rights"
	 	* 		}
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
		* @api {post} /Whiteboard/pushDraw/:id Request to push a whiteboard modification
		* @apiName pushDrawOnWhiteboard
		* @apiGroup whiteboard
		* @apiVersion 1.0.0
		*
		* @apiParam {String} _token client authentification token
		* @apiParam {String}  modification type of modification ("add" or "del")
		* @apiParam {int}  object_id IN CASE OF DEL: object's id
		* @apiParam {object} object IN CASE OF ADD: object content (json array)
		*
		* @apiSuccess {String} status status of the request (error or success)
		* @apiSuccess {string} data success message
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"data": "success"
		* 	}
		*
		* @apiErrorExample Bad Authentification Token
		*     HTTP/1.1 400 Bad Request
		*     {
		*       "data": "bad token"
		*     }
		* @apiErrorExample Insufficient User Rights
		*			HTTP/1.1 400 Bad Request
	 	* 		{
	 	*    		"data": "no rights"
	 	* 		}
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
			$object = $em->getRepository('APIBundle:WhiteboardObject')->find($request->request->get('object_id'));
			$object->setDelete(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		$response->setData(array('status' => 'success', 'data' => "success"));
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

		$date = new \DateTime($request->request->get('lastUpdate'));
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
