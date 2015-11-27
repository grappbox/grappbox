<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APIBundle\Controller\RolesAndTokenVerificationController;
use APIBundle\Entity\Whiteboard;
use DateTime;

// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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
			$content[] = $value->objectToArray();
		}
		return $content;
	}

	/**
	* @api {get} /V0.6/whiteboard/list/:token/:projectId Get the whiteboards' list of a project
	* @apiName listWhiteboard
	* @apiGroup Whiteboard
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token client authentification token
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
	public function listWhiteboardAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$projectId)
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $projectId, "whiteboard"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($rojectId);
		$whiteboards = $project->getWhiteboards();

		$response = new JsonResponse();
		$response->setData($this->serializeInArray($whiteboards));
		return $response;
	}

	/**
	* @api {post} /V0.6/whiteboard/new Create a new Whiteboard
	* @apiName createWhiteboard
	* @apiGroup Whiteboard
	* @apiVersion 0.6.0
	*
	* @apiParam {String} _token client authentification token
	* @apiParam {int} projectId id of the selected project
	* @apiParam {string} whiteboardName name of the new whiteboard
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"_token": "f1a3f1ea35fae31f",
	*		"projectId": 2,
	*		"whiteboardName": "Brainstorming #5"
	* 	}
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
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			 return ($this->setBadTokenError());
		if (!array_key_exists('projectId', $content) || !array_key_exists('whiteboardName', $content))
			 return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "whiteboard"))
			 return ($this->setNoRightsError());

		$whiteboard = new Whiteboard();
		$whiteboard->setProjectId($content->projectId);
		$whiteboard->setUserId($user->getId());
		$whiteboard->setUpdatorId($user->getId());
		$whiteboard->setName($content->whiteboardName);
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em = $this->getDoctrine()->getManager();
		$em->persist($whiteboard);
		$em->flush();

		$response = new JsonResponse();
		$response->setData(array('whiteboard' => $whiteboard->objectToArray(), 'content' => array()));
		return $response;
	}

	/**
	* @api {get} /V0.6/whiteboard/open/:token/:id Open a whiteboard
	* @apiName openWhiteboard
	* @apiGroup Whiteboard
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {Number} id Id of the whiteboard
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
	*			"id": 12,
	*			"name": "Brainstorming #5",
	*			"creator_id": 65,
	*			"updator_id": 54},
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
	public function openWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
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
		$response->setData(array('whiteboard' => $whiteboard->objectToArray(), 'content' => $this->serializeInArray($whiteboard->getObjects())));
		return $response;
	}

	/**
	* @api {put} V0.6/whiteboard/pushdraw/:id Push a whiteboard modification
	* @apiName pushDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiVersion 0.6.0
	*
	* @apiParam {int} id Id of the whiteboard
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
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjectId(), "whiteboard"))
			return ($this->setNoRightsError());
		if (!array_key_exists('modification', $content))
		 	return $this->setBadRequest("Missing Parameter");

		if ($content->modification == "add")
		{
			if (!array_key_exists('object', $content))
	 			return $this->setBadRequest("Missing Parameter");
			$object = new WhiteboardObject();
			$object->setWhiteboardId($id);
			$object->setObject($content->object);
			$object->setCreatedAt(new DateTime('now'));
		}
		else {
			if (!array_key_exists('objectId', $content))
	 			return $this->setBadRequest("Missing Parameter");
			$object = $em->getRepository('APIBundle:WhiteboardObject')->find($content->objectId);
			$object->setDelete(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		$response = new JsonResponse();
		$response->setData("Success");
		return $response;
	}

	/**
	* @api {get} /V0.6/whiteboard/pulldraw/:id Pull a whiteboard modification
	* @apiName pullDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiVersion 0.6.0
	*
	* @apiParam {int} id Id of the whiteboard
	* @apiParam {String} _token client authentification token
	* @apiParam {DateTime} lastUpdate date of the last update
	*
	* @apiSuccess {Object[]} add array of the objects added in the whiteboard
	* @apiSuccess {Object} data.add.object  the objects to add
	* @apiSuccess {Object[]} delete array of the objects deleted in the whiteboard
	* @apiSuccess {Object} data.delete.object  the objects to delete
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
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('APIBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjectId(), "whiteboard"))
			 return ($this->setNoRightsError());
		if (!array_key_exists('lastUpdate', $content))
 			 return $this->setBadRequest("Missing Parameter");

		$date = new \DateTime($content->lastUpdate);
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
	* @api {delete} /V0.6/whiteboard/delete/:token/:id Delete a Whiteboard
	* @apiName deleteWhiteboard
	* @apiGroup Whiteboard
	* @apiVersion 0.6.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id Id of the whiteboard
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
	public function delWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
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
