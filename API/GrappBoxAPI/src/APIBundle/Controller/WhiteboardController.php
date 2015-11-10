<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APIBundle\Controller\RolesAndTokenVerificationController;
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
class WhiteboardController extends RolesAndTokenVerificationController
{

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
	* @apiSuccess {Object[]} data list of whiteboards
	* @apiSuccess {int} data.id whiteboard id
	* @apiSuccess {string} data.name whiteboard name
	* @apiSuccess {int} data.creator_id id of the whiteboard's creator
	* @apiSuccess {int} data.updator_id id of the whiteboard's last updator
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"0": {
	*			"id": "12",
	*			"name": "Brainstorming #5",
	*			"creator_id": "65",
	*			"updator_id": "54"},
	*		"1": {
	*			"id": "12",
	*			"name": "Brainstorming #5",
	*			"creator_id": "65",
	*			"updator_id": "36"},
	*		...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
 	* 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
	*
	*/
	public function listWhiteboardAction(Request $request)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "whiteboard"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($request->request->get('projectId'));
		$whiteboards = $project->getWhiteboards();

		$response = new JsonResponse();
		$response->setData($this->serializeInArray($whiteboards));
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
 	* @apiSuccess {Object} whiteboard the new whiteboard informations
 	* @apiSuccess {int} whiteboard.id whiteboard id
 	* @apiSuccess {string} whiteboard.name whiteboard name
 	* @apiSuccess {int} whiteboard.creator_id id of the whiteboard's creator
 	* @apiSuccess {int} whiteboard.updator_id id of the whiteboard's last updator (creator)
	* @apiSuccess {Object[]} content the new whiteboard content (empty)
 	*
 	* @apiSuccessExample {json} Success-Response:
 	* 	{
 	*		"whiteboard": {
 	*			"id": "12",
 	*			"name": "Brainstorming #5",
 	*			"creator_id": "65",
 	*			"updator_id": "54"},
 	*		"content": [ ]
 	* 	}
 	*
	* @apiErrorExample Bad Authentication Token
 	* 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
 	*
 	*/
	public function newWhiteboardAction(Request $request)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			 return ($this->setBadTokenError());
		if (!$request->request->get('projectId') || !$request->request->get('whiteboardName'))
			 return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "whiteboard"))
			 return ($this->setNoRightsError());

		$whiteboard = new Whiteboard();
		$whiteboard->setProjectId($request->request->get('projectId'));
		$whiteboard->setUserId($user->getId());
		$whiteboard->setUpdatorId($user->getId());
		$whiteboard->setName($request->request->get('whiteboardName'));
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em = $this->getDoctrine()->getManager();
		$em->persist($whiteboard);
		$em->flush();

		$response = new JsonResponse();
		$response->setData(array('whiteboard' => $whiteboard->serialize(), 'content' => array()));
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
		* @apiSuccess {Object} whiteboard whiteboard information and content
		* @apiSuccess {int} whiteboard.id whiteboard id
		* @apiSuccess {string} whiteboard.name whiteboard name
		* @apiSuccess {int} whiteboard.creator_id id of the whiteboard's creator
		* @apiSuccess {int} whiteboard.updator_id id of the whiteboard's last updator
		* @apiSuccess {Object[]} content whiteboard content objects
		* @apiSuccess {object} content.object object whiteboard's object
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"whiteboard": {
		*			"id": "12",
		*			"name": "Brainstorming #5",
		*			"creator_id": "65",
		*			"updator_id": "54"},
		*		"content": [
		*			"0": {
		*				"id": 12,
		*				"type": "rectangle",
		*				"color": "125,25,65",
		*				"line": "1.5",
		*				"position": "15;63.3",
		*				...
		*			},
		*			"1": {
		*				"id": 12,
		*				"type": "circle",
		*				"color": "125,25,65",
		*				"line": "1.5",
		*				"position": "186.20;42.95",
		*				...
		*			},
		*			...
		*		]
		* 	}
		*
		* @apiErrorExample Bad Authentication Token
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Bad Authentication Token"
	  * 	}
		* @apiErrorExample Insufficient User Rights
	 	* 	HTTP/1.1 403 Forbidden
	  * 	{
	  * 		"Insufficient User Rights"
	  * 	}
		* @apiErrorExample Missing Parameter
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Bad Whiteboard Id
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Bad Whiteboard Id"
	  * 	}
		* @apiErrorExample Whiteboard Deleted
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Whiteboard Deleted"
	  * 	}
		*
		*/
	public function openWhiteboardAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjectId(), "whiteboard"))
			 return ($this->setNoRightsError());
		if ($whiteboard->getDeletedAt())
			 return $this->setBadRequest("Whiteboard Deleted");

		$response = new JsonResponse();
		$response->setData(array('whiteboard' => $whiteboard->serialize(), 'content' => $this->serializeInArray($whiteboard->getObjects())));
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
		* @apiParam {object} object IN CASE OF ADD: whiteboard's object (json array)
		*
		* @apiSuccess {String} data success message
		*
		* @apiSuccessExample {json} Success-Response:
		* 	HTTP/1.1 200 OK
	  * 	{
	  * 		"Success"
	  * 	}
		*
		* @apiErrorExample Bad Authentication Token
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Bad Authentication Token"
	  * 	}
		* @apiErrorExample Insufficient User Rights
	 	* 	HTTP/1.1 403 Forbidden
	  * 	{
	  * 		"Insufficient User Rights"
	  * 	}
		* @apiErrorExample Missing Parameter
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Bad Whiteboard Id
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Bad Whiteboard Id"
	  * 	}
		*
		*/
	public function pushDrawAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjectId(), "whiteboard"))
			 return ($this->setNoRightsError());
		if (!$request->request->get('modification'))
		 	 return $this->setBadRequest("Missing Parameter");

		if ($request->request->get('modification') == "add")
		{
			if (!$request->request->get('object'))
	 			 return $this->setBadRequest("Missing Parameter");
			$object = new WhiteboardObject();
			$object->setWhiteboardId($id);
			$object->setObject($request->request->get('object'));
			$object->setCreatedAt(new DateTime('now'));
		}
		else {
			if (!$request->request->get('object_id'))
	 			 return $this->setBadRequest("Missing Parameter");
			$object = $em->getRepository('APIBundle:WhiteboardObject')->find($request->request->get('object_id'));
			$object->setDelete(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		$response = new JsonResponse();
		$response->setData("Success");
		return $response;
	}

	 /**
		* @api {post} /Whiteboard/pullDraw/:id Request to pull a whiteboard modification
		* @apiName pullDrawOnWhiteboard
		* @apiGroup whiteboard
		* @apiVersion 1.0.0
		*
		* @apiParam {String} _token client authentification token
		* @apiParam {DateTime}  lastUpdate date of the last update
		*
		* @apiSuccess {Object[]} add array of the objects added  in the whiteboard
		* @apiSuccess {Object} data.add.object  the objects to add
		* @apiSuccess {Object[]} delete array of the objects deleted in the whiteboard
		* @apiSuccess {Object} data.delete.object  the objects to delete
		*
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"add":[
		*			"0": {
		*				"id": 22,
		*				"content": {"type": "rectangle", "color":"154,25,95", ... }
		*			},
		*			"1": {
		*				"id": 23,
		*				"content": {"type": "square", "color":"54,125,95", ...}
		*			},
		*			...
		*		],
		*		"delete":[
		*			"0": {
		*				"id": 2,
		*				"content": {"type": "line", "color":"14,85,105", ...}
		*			},
		*			...
		*		]
		* 	}
		*
		* @apiErrorExample Bad Authentication Token
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Bad Authentication Token"
	  * 	}
		* @apiErrorExample Insufficient User Rights
	 	* 	HTTP/1.1 403 Forbidden
	  * 	{
	  * 		"Insufficient User Rights"
	  * 	}
		* @apiErrorExample Missing Parameter
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Bad Whiteboard Id
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Bad Whiteboard Id"
	  * 	}
		*
		*/
	public function pullDrawAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjectId(), "whiteboard"))
			 return ($this->setNoRightsError());
		if (!$request->request->get('lastUpdate'))
 			 return $this->setBadRequest("Missing Parameter");

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

		$response = new JsonResponse();
		$response->setData(array('add' => $to_add, 'delete' => $to_del));
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
 	* @api {post} /Whiteboard/delete/:id Request the deletion of a Whiteboard
 	* @apiName deleteWhiteboard
 	* @apiGroup whiteboard
 	* @apiVersion 1.0.0
 	*
 	* @apiParam {String} _token client authentification token
 	*
 	* @apiSuccess {String} data success message
 	*
	* @apiSuccessExample {json} Success-Response:
	* 	HTTP/1.1 200 OK
	* 	{
	* 		"Success"
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample Bad Whiteboard Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Whiteboard Id"
	* 	}
 	*
 	*/
	public function delWhiteboardAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjectId(), "whiteboard"))
			 return ($this->setNoRightsError());

		if ($whiteboard)
		{
				$whiteboard->setDeletedAt(new DateTime('now'));
				$em->persist($whiteboard);
				// $em->remove($whiteboard);
				$em->flush();
		}

		$response = new JsonResponse();
		$response->setData('Success');
		return $response;
	}
}
