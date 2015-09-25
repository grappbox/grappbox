<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use APIBundle\Entity\CloudTransfer;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CloudController extends Controller
{
	private static $settingsDAV = null;
	//Return -1 if the use don't have authorization for Cloud
	//Else return the user_id

	public function __construct()
	{
			if (is_null(self::$settingsDAV))
				self::$settingsDAV = array(
				'baseUri' => 'http://cloud.grappbox.com/remote.php/webdav/',
				'userName' => 'grappbox',
				'password' => 'GolfBravo$$'
				);
	}

	private function getUserId($token)
	{
			$dbManager = $this->getDoctrine()->getManager();

			return (1);
	}

	private function checkTokenAuthorization($token, $idProject)
	{
			$dbManager = $this->getDoctrine()->getManager();

			return (1);
	}

	//This have to be a POST or DELETE request
	// POST : Open a stream
	// DELETE : Close a stream
	/* Requested json
	{
		session_infos: {
			token : "userToken"
		},
		stream_infos: {
			//ON POST REQUEST ONLY//
			project_id : 42,
			filename : "Awesomeness",
			path : "/LabEIP/TestUpload",
			password : "HashPasswordIfSecuredFileElseNullType"
			//ON DELETE REQUEST ONLY//
			stream_id : 21
		}
	}
	*/
	public function streamAction(Request $request)
	{
		//Check if request method is catched by the API
		$method = $request->getMethod();
		if ($method != "POST" && $method != "DELETE")
			return header("HTTP/1.0 404 Not Found", True, 404);
		//Check if user have authorization to modify cloud for this project
		$dbManager = $this->getDoctrine()->getManager();
		$token = $request->get("session_infos")["token"];
		$receivedData = $request->get("stream_infos");
		$idProject = $receivedData["project_id"];
		if ($method == "POST"
				&& ($user_id = $this->checkTokenAuthorization($token, $idProject)) < 0)
			return header("HTTP/1.0 403 Forbidden", True, 403);
		return ($method == "POST"
							? $this->openStream($receivedData, $user_id)
							: $this->closeStream($receivedData, $token));
	}

	private function openStream($receivedData, $user_id)
	{
		$em = $this->getDoctrine()->getManager();
		$stream = new CloudTransfer();
		$stream->setCreatorId($user_id)
					 ->setFilename($receivedData["filename"])
					 ->setPath($receivedData["path"])
					 ->setPassword($receivedData["password"])
					 ->setCreationDate(new DateTime("now"))
					 ->setDeletionDate(null);
		$em->persist($stream);
		$em->flush();
		return new JsonResponse(array("stream_id" => $stream->getId()));
	}

	private function closeStream($receivedData, $token){
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		$user_id = $this->getUserId($token);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			return header("HTTP/1.0 403 Forbidden", True, 403);

		//Here the user have the authorization to close this stream
	}

	//This have to be a PUT request
	//PUT : Register files chunk in order to upload large files
	/*
	  requested json :
	  {
			session_infos: {
				token : "ImAToken"
			},
			stream_infos: {
				stream_id : 21
				file_chunk : "ImAFileChunkAlreadyHashedWithThePassswordIfPassword"
			}
 	  }
	*/
	public function sendFileAction(Request $request)
	{
		//Check if request method is catched by the API
		$method = $request->getMethod();
		if ($method != "PUT")
			return header("HTTP/1.0 404 Not Found", True, 404);
		//Check Authorization to access cloud and to upload that file
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
		$token = $request->get("session_infos")["token"];
		$receivedData = $request->get("stream_infos");
		$user_id = $this->getUserId($token);
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			return header("HTTP/1.0 403 Forbidden", True, 403);

		//Here the user have the right authorization, so upload the file's chunk

		$client = new Sabre\DAV\Client(self::$settingsDAV);
		$adapter = new League\Flysystem\WebDAV\WebDAVAdapter($client);
		$flysystem = new League\Flysystem\Filesystem($adapter);
		if ($filesystem->has('/Grappbox Transfer/'.(string)$receivedData["stream_id"]))
		$flysystem->put('/Grappbox Transfer/'.(string)$receivedData["stream_id"].'.transfer', (string)$receivedData["file_chunk"]);
		return header("HTTP/1.0 203 Success", True, 203);
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a list of files and directories",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getListAction(Request $request)
	{
		return new Response('get File List Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a file",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getFileAction(Request $request)
	{
		return new Response('get File Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="push a file",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function pushFileAction(Request $request)
	{
		return new Response('push File Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="set the password of a file",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function setFilePassAction(Request $request)
	{
		return new Response('Set File Pass Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="set the password of a directory",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function setDirPassAction(Request $request)
	{
		return new Response('set Dir Pass Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a file",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function delFileAction(Request $request)
	{
		return new Response('del File Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a directory",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function delDirAction(Request $request)
	{
		return new Response('del Dir Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the metadatas of a file",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getFileMetadataAction(Request $request)
	{
		return new Response('get File Metadata Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the metadata of a directory",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getDirMetadataAction(Request $request)
	{
		return new Response('get Dir Metadata Success');
	}
}
