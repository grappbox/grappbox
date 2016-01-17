<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\Whiteboard;
use MongoBundle\Document\WhiteboardObject;
use DateTime;

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
	* @api {get} /mongo/whiteboard/list/:token/:projectId Get the whiteboards' list of a project
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} projectId id of the selected project
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		$whiteboards = $project->getWhiteboards();

		$response = new JsonResponse();
		$response->setData($this->serializeInArray($whiteboards));
		return $response;
	}

	/**
	* @api {post} /mongo/whiteboard/new Create a new Whiteboard
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} projectId id of the selected project
	* @apiParam {string} whiteboardName name of the new whiteboard
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "f1a3f1ea35fae31f",
	*		"projectId": 2,
	*		"whiteboardName": "Brainstorming #5"
	* 	}
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);

		$whiteboard = new Whiteboard();
		$whiteboard->setProjects($project);
		$whiteboard->setUserId($user->getId());
		$whiteboard->setUpdatorId($user->getId());
		$whiteboard->setName($content->whiteboardName);
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em->persist($whiteboard);
		$em->flush();

		$response = new JsonResponse();
		$response->setData(array('whiteboard' => $whiteboard->objectToArray(), 'content' => array()));
		return $response;
	}

	/**
	* @api {get} /mongo/whiteboard/open/:token/:id Open a whiteboard
	*
	* @apiParam {String} token client authentification token
	* @apiParam {Number} id Id of the whiteboard
	*/
	public function openWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			 return ($this->setNoRightsError());
		if ($whiteboard->getDeletedAt())
			 return $this->setBadRequest("Whiteboard Deleted");

		$response = new JsonResponse();
		$response->setData(array('whiteboard' => $whiteboard->objectToArray(), 'content' => $this->serializeInArray($whiteboard->getObjects())));
		return $response;
	}

	/**
	* @api {put} mongo/whiteboard/pushdraw/:id Push a whiteboard modification
	*
	* @apiParam {int} id Id of the whiteboard
	* @apiParam {String} _token client authentification token
	* @apiParam {String}  modification type of modification ("add" or "del")
	* @apiParam {int}  object_id IN CASE OF DEL: object's id
	* @apiParam {object} object IN CASE OF ADD: whiteboard's object (json array)
	*/
	public function pushDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			return ($this->setNoRightsError());
		if (!array_key_exists('modification', $content))
		 	return $this->setBadRequest("Missing Parameter");

		if ($content->modification == "add")
		{
			if (!array_key_exists('object', $content))
	 			return $this->setBadRequest("Missing Parameter");
			$object = new WhiteboardObject();
			$object->setWhiteboardId($id);
			$object->setWhiteboard($whiteboard);
			$object->setObject($content->object);
			$object->setCreatedAt(new DateTime('now'));
		}
		else {
			if (!array_key_exists('objectId', $content))
	 			return $this->setBadRequest("Missing Parameter");
			$object = $em->getRepository('MongoBundle:WhiteboardObject')->find($content->objectId);
			$object->setDeletedAt(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		$response = new JsonResponse();
		$response->setData($object->objectToArray());
		return $response;
	}

	/**
	* @api {get} /mongo/whiteboard/pulldraw/:id Pull a whiteboard modification
	*
	* @apiParam {int} id Id of the whiteboard
	* @apiParam {String} token client authentification token
	* @apiParam {DateTime} lastUpdate date of the last update
	*/
	public function pullDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
			 return ($this->setNoRightsError());
		if (!array_key_exists('lastUpdate', $content))
 			 return $this->setBadRequest("Missing Parameter");

		$date = new \DateTime($content->lastUpdate);

		$toAddQuery = $em->createQuery(
									    'SELECT objects
									    FROM MongoBundle\Document\WhiteboardObject objects
									    WHERE objects.whiteboardId = :id AND objects.createdAt > :date AND objects.deletedAt IS NULL')
											->setParameters(array('date' => $date, 'id' => $id));
		$to_add = $toAddQuery->getResult();
		$toAdd = array();
		foreach ($to_add as $key => $value) {
			$toAdd[] = $value->objectToArray();
		}
		$toDelQuery = $em->createQuery(
									    'SELECT objects
									    FROM MongoBundle\Document\WhiteboardObject objects
									    WHERE objects.whiteboardId = :id AND objects.deletedAt > :date AND objects.deletedAt IS NOT NULL')
											->setParameters(array('date' => $date, 'id' => $id));
		$to_del = $toDelQuery->getResult();
		$toDel = array();
		foreach ($to_del as $key => $value) {
			$toDel[] = $value->objectToArray();
		}

		$response = new JsonResponse();
		$response->setData(array('add' => $toAdd, 'delete' => $toDel));
		return $response;
	}

	/**
	* @api {delete} /mongo/whiteboard/delete/:token/:id Delete a Whiteboard
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id Id of the whiteboard
	*/
	public function delWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			 return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

		if (!$this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard"))
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
