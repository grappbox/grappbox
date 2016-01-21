<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappboxBundle\Controller\RolesAndTokenVerificationController;
use GrappboxBundle\Entity\Whiteboard;
use GrappboxBundle\Entity\WhiteboardObject;
use DateTime;

// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiVersion")
*  @IgnoreAnnotation("apiSuccess")
*  @IgnoreAnnotation("apiSuccessExample")
*  @IgnoreAnnotation("apiError")
*  @IgnoreAnnotation("apiErrorExample")
*  @IgnoreAnnotation("apiParam")
*  @IgnoreAnnotation("apiDescription")
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
	* @api {get} /V0.2/whiteboard/list/:token/:projectId Get the whiteboards' list of a project
	* @apiName listWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Get a list of whiteboards for the given project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client Authentification token
	* @apiParam {int} projectId Id of the selected project
	*
	* @apiSuccess {Object[]} array Array of whiteboards informations
	* @apiSuccess {int} array.id Whiteboard id
	* @apiSuccess {int} array.userId User creator id
	* @apiSuccess {string} array.name Whiteboard name
	* @apiSuccess {int} array.updatorId User who update last the whiteboard id
	* @apiSuccess {DateTime} array.updatedAt Update date
	* @apiSuccess {DateTime} array.createdAt Creation date
	* @apiSuccess {DateTime} array.deledtedAt Deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.10.1",
	*			"return_message": "Whiteboard - list - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 12,
	*					"userId": 13,
	*					"name": "Brainstorming #5",
	*					"updatorId": 54,
	*					"updatedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"deletedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*				},
	*				{
	*					"id": "12",
	*					"userId": 13,
	*					"name": "Brainstorming #5",
	*					"updatorId": 54,
	*					"updatedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"deletedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.10.3",
	*			"return_message": "Whiteboard - list - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "10.1.3",
	*			"return_message": "Whiteboard - list - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "10.1.9",
	*			"return_message": "Whiteboard - list - Insufficient Rights"
	*		}
	*	}
	*/
	public function listWhiteboardAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("10.1.3", "Whiteboard", "list"));

		if (!$this->checkRoles($user, $projectId, "whiteboard"))
			return ($this->setNoRightsError("10.1.9", "Whiteboard", "list"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($projectId);
		$whiteboards = $project->getWhiteboards();

		if (count($whiteboards) == 0)
			return $this->setNoDataSuccess("1.10.3", "Whiteboard", "list");

		return $this->setSuccess("1.10.1", "Whiteboard", "list", "Complete Success", $this->serializeInArray($whiteboards));
	}

	/**
	* @api {post} /V0.2/whiteboard/new Create a new Whiteboard
	* @apiName createWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Create a new whiteboard
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} projectId id of the selected project
	* @apiParam {string} whiteboardName name of the new whiteboard
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "f1a3f1ea35fae31f",
	*			"projectId": 2,
	*			"whiteboardName": "Brainstorming #5"
	*		}
	*	}
	*
	* @apiSuccess {int} id whiteboard id
	* @apiSuccess {int} userId user creator id
	* @apiSuccess {string} name whiteboard name
	* @apiSuccess {int} updatorId id of the whiteboard's last updator (creator)
	* @apiSuccess {DateTime} updatedAt update date (creation date)
	* @apiSuccess {DateTime} createdAt creation date
	* @apiSuccess {DateTime} deledtedAt deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.10.1",
	*			"return_message": "Whiteboard - new - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 12,
	*			"userId": 13,
	*			"name": "Brainstorming #5",
	*			"updator_id": 54,
	*			"updatedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "10.2.3",
	*			"return_message": "Whiteboard - new - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "10.2.9",
	*			"return_message": "Whiteboard - new - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.2.6",
	*			"return_message": "Whiteboard - new - Missing Parameter"
	*		}
	*	}
	*/
	public function newWhiteboardAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('whiteboardName', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("10.2.6", "Whiteboard", "new", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.2.3", "Whiteboard", "new"));

		if (!$this->checkRoles($user, $content->projectId, "whiteboard"))
			return ($this->setNoRightsError("10.2.9", "Whiteboard", "new"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("GrappboxBundle:Project")->find($content->projectId);

		$whiteboard = new Whiteboard();
		$whiteboard->setProjects($project);
		$whiteboard->setUserId($user->getId());
		$whiteboard->setUpdatorId($user->getId());
		$whiteboard->setName($content->whiteboardName);
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em->persist($whiteboard);
		$em->flush();

		return $this->setCreated("1.10.1", "Whiteboard", "list", "Complete Success", $whiteboard->objectToArray());
	}

	/**
	* @api {get} /V0.2/whiteboard/open/:token/:id Open a whiteboard
	* @apiName openWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Open the given whiteboard
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Client authentification token
	* @apiParam {Number} id Id of the whiteboard
	*
	* @apiSuccess {int} id Whiteboard id
	* @apiSuccess {int} userId User creator id
	* @apiSuccess {string} name Whiteboard name
	* @apiSuccess {int} updatorId Id of the whiteboard's last updator (creator)
	* @apiSuccess {DateTime} updatedAt Update date (creation date)
	* @apiSuccess {DateTime} createdAt Creation date
	* @apiSuccess {DateTime} deledtedAt Deletion date
	* @apiSuccess {Object[]} content Whiteboard content objects
	* @apiSuccess {object} content.object Object whiteboard's object
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.10.1",
	*			"return_message": "Whiteboard - open - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 12,
	*			"userId": 13,
	*			"name": "Brainstorming #5",
	*			"updator_id": 54,
	*			"updatedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*			"content": [
	*				{
	*					"id": 5,
	*					"whiteboardId": "2",
	*					"object": "{'type':'rectangle', 'position':'14,51;25,06', 'color': 'rgb(25,125,65)', ...}",
	*					"createdAt": {"date": "2015-11-27 11:31:24", "timezone_type": 3, "timezone": "Europe/Paris"},
	*					"deletedAt": null
	*				},
	*				{
	*					"id": 5,
	*					"whiteboardId": "2",
	*					"object": "{'type':'rectangle', 'position':'14,51;25,06', 'color': 'rgb(25,125,65)', ...}",
	*					"createdAt": {"date": "2015-11-27 11:31:24", "timezone_type": 3, "timezone": "Europe/Paris"},
	*					"deletedAt": null
	*				}
	*			]
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "10.3.3",
	*			"return_message": "Whiteboard - open - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "10.3.9",
	*			"return_message": "Whiteboard - open - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.3.4",
	*			"return_message": "Whiteboard - open - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: Whiteboard deleted
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.3.4",
	*			"return_message": "Whiteboard - open - Bad Parameter: Whiteboard deleted"
	*		}
	*	}
	*/
	public function openWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("10.3.3", "Whiteboard", "open"));

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('GrappboxBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.3.4", "Whiteboard", "open", "Bad Parameter: id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			return ($this->setNoRightsError("10.3.9", "Whiteboard", "open"));

		if ($whiteboard->getDeletedAt())
			return $this->setBadRequest("Whiteboard Deleted");

		$arr = $whiteboard->objectToArray();
		$arr["content"] =  $this->serializeInArray($whiteboard->getObjects());

		return $this->setSuccess("1.10.1", "Whiteboard", "open", "Complete Success", $arr);
	}

	/**
	* @api {put} /V0.2/whiteboard/pushdraw/:id Push a whiteboard modification
	* @apiName pushDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Push a whiteboard modification
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id Id of the whiteboard
	* @apiParam {String} token Client authentification token
	* @apiParam {String}  modification Type of modification ("add" or "del")
	* @apiParam {int}  objectId IN CASE OF DEL: object's id
	* @apiParam {object} object IN CASE OF ADD: whiteboard's object (json array)
	*
	* @apiParamExample {json} Request-Delete-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"modification": "del",
	*			"objectId": 3
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Add-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"modification": "add",
	*			"object": {
	*				"type": "rectangle",
	*				"position": "14,51;25,06",
	*				"color": "rbg(25,125,65)"
	*			}
	*		}
	*	}
	*
	* @apiSuccess {int} id object id
	* @apiSuccess {int} whiteboardId whiteboard id
	* @apiSuccess {String} object the object caracterictics
	* @apiSuccess {DateTime} createdAt object creation date
	* @apiSuccess {DateTime} deletedAt object deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.10.1",
	*			"return_message": "Whiteboard - push - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 5,
	*			"whiteboardId": "2",
	*			"object": "{'type':'rectangle', 'position':'14,51;25,06', 'color': 'rgb(25,125,65)', ...}",
	*			"createdAt": {"date": "2015-11-27 11:31:24", "timezone_type": 3, "timezone": "Europe/Paris"},
	*			"deletedAt": null
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "10.4.3",
	*			"return_message": "Whiteboard - push - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "10.4.9",
	*			"return_message": "Whiteboard - push - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.4.6",
	*			"return_message": "Whiteboard - push - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.4.4",
	*			"return_message": "Whiteboard - push - Bad Parameter: id"
	*		}
	*	}
	*/
	public function pushDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('modification', $content) || !array_key_exists('token', $content))
		 	return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.4.3", "Whiteboard", "push"));

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('GrappboxBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.4.4", "Whiteboard", "push", "Bad Parameter: id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			return ($this->setNoRightsError("10.4.9", "Whiteboard", "push"));
		
		if ($content->modification == "add")
		{
			if (!array_key_exists('object', $content))
	 			return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");
			$object = new WhiteboardObject();
			$object->setWhiteboardId($id);
			$object->setWhiteboard($whiteboard);
			$object->setObject($content->object);
			$object->setCreatedAt(new DateTime('now'));
		}
		else {
			if (!array_key_exists('objectId', $content))
	 			return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");
			$object = $em->getRepository('GrappboxBundle:WhiteboardObject')->find($content->objectId);
			$object->setDeletedAt(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		return $this->setSuccess("1.10.1", "Whiteboard", "push", "Complete Success", $object->objectToArray());
	}

	/**
	* @api {post} /V0.2/whiteboard/pulldraw/:id Pull a whiteboard modification
	* @apiName pullDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Pull a whiteboard modification
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id Id of the whiteboard
	* @apiParam {String} token Client authentification token
	* @apiParam {DateTime} lastUpdate Date of the last update
	*
	* @apiParamExample {json} Request-Delete-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"lastUpdate": "2015-11-27 11:31:24"
	*		}
	*	}
	*
	* @apiSuccess {Object[]} add Array of the objects added in the whiteboard
	* @apiSuccess {Object} add.object  The objects to add
	* @apiSuccess {Object[]} delete Array of the objects deleted in the whiteboard
	* @apiSuccess {Object} delete.object  the objects to delete
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.10.1",
	*			"return_message": "Whiteboard - pull - Complete Success"
	*		},
	*		"data":
	*		{
	*			"add":[
	*				{
	*					"id": 5,
	*					"whiteboardId": "2",
	*					"object": "{'type':'rectangle', 'position':'14,51;25,06', 'color': 'rgb(25,125,65)', ...}",
	*					"createdAt": {"date": "2015-11-27 11:31:24", "timezone_type": 3, "timezone": "Europe/Paris"},
	*					"deletedAt": null
	*				},
	*				{
	*					"id": 5,
	*					"whiteboardId": "2",
	*					"object": "{'type':'rectangle', 'position':'14,51;25,06', 'color': 'rgb(25,125,65)', ...}",
	*					"createdAt": {"date": "2015-11-27 11:31:24", "timezone_type": 3, "timezone": "Europe/Paris"},
	*					"deletedAt": null
	*				},
	*				...
	*			],
	*			"delete":[
	*				0: {
	*					"id": 5,
	*					"whiteboardId": "2",
	*					"object": "{'type':'rectangle', 'position':'14,51;25,06', 'color': 'rgb(25,125,65)', ...}",
	*					"createdAt": {"date": "2015-11-27 11:31:24", "timezone_type": 3, "timezone": "Europe/Paris"},
	*					"deletedAt": null
	*				}
	*			]
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "10.5.3",
	*			"return_message": "Whiteboard - pull - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "10.5.9",
	*			"return_message": "Whiteboard - pull - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.5.6",
	*			"return_message": "Whiteboard - pull - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.5.4",
	*			"return_message": "Whiteboard - pull - Bad Parameter: id"
	*		}
	*	}
	*/
	public function pullDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('lastUpdate', $content) || !array_key_exists('token', $content))
 			return $this->setBadRequest("10.5.6", "Whiteboard", "pull", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.5.3", "Whiteboard", "pull"));

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('GrappboxBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.5.4", "Whiteboard", "pull", "Bad Parameter: id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			return ($this->setNoRightsError("10.5.9", "Whiteboard", "pull"));
		;

		$date = new \DateTime($content->lastUpdate);

		$toAddQuery = $em->createQuery('SELECT objects FROM GrappboxBundle\Entity\WhiteboardObject objects
										WHERE objects.whiteboardId = :id AND objects.createdAt > :date AND objects.deletedAt IS NULL')
										->setParameters(array('date' => $date, 'id' => $id));
		$to_add = $toAddQuery->getResult();
		$toAdd = array();
		foreach ($to_add as $key => $value) {
			$toAdd[] = $value->objectToArray();
		}
		$toDelQuery = $em->createQuery('SELECT objects FROM GrappboxBundle\Entity\WhiteboardObject objects
									    WHERE objects.whiteboardId = :id AND objects.deletedAt > :date AND objects.deletedAt IS NOT NULL')
										->setParameters(array('date' => $date, 'id' => $id));
		$to_del = $toDelQuery->getResult();
		$toDel = array();
		foreach ($to_del as $key => $value) {
			$toDel[] = $value->objectToArray();
		}

		return $this->setSuccess("1.10.1", "Whiteboard", "push", "Complete Success", array('add' => $toAdd, 'delete' => $toDel));
	}

	/**
	* @api {delete} /V0.2/whiteboard/delete/:token/:id Delete a Whiteboard
	* @apiName deleteWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Delete a whiteboard
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id Id of the whiteboard
	*
	* @apiSuccess {String} id Id of the whiteboard deleted
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.10.1",
	*			"return_message": "Whiteboard - delete - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "10.6.3",
	*			"return_message": "Whiteboard - delete - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "10.6.9",
	*			"return_message": "Whiteboard - delete - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "10.6.4",
	*			"return_message": "Whiteboard - delete - Bad Parameter: id"
	*		}
	*	}
	*/
	public function delWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("10.6.3", "Whiteboard", "delete"));

		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('GrappboxBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.6.4", "Whiteboard", "delete", "Bad Parameter: id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			return ($this->setNoRightsError("10.6.9", "Whiteboard", "delete"));

		if ($whiteboard)
		{
			$whiteboard->setDeletedAt(new DateTime('now'));
			$em->persist($whiteboard);
			// $em->remove($whiteboard);
			$em->flush();
		}

		return $this->setSuccess("1.10.1", "Whiteboard", "delete", "Complete Success", array('id' => $id));
	}
}
