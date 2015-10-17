<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use APIBundle\Entity\CloudTransfer;
use APIBundle\Entity\CloudSecuredFileMetadata;

use Sabre\DAV\Client;
use League\Flysystem\WebDAV\WebDAVAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Plugin\ListFiles;

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
	public function streamAction(Request $request){
		//Check if request method is catched by the API
		$method = $request->getMethod();
		if ($method != "POST" && $method != "DELETE")
			throw $this->createNotFoundException('The method does not exist');
		//Check if user have authorization to modify cloud for this project
		$dbManager = $this->getDoctrine()->getManager();
		$token = $request->get("session_infos")["token"];
		$userId = $this->getUserId($token);
		$receivedData = $request->get("stream_infos");
		$idProject = $receivedData["project_id"];
		if ($method == "POST" && $this->checkTokenAuthorization($token, $idProject) < 0)
			throw $this->createAccessDeniedException();
		return ($method == "POST"
							? $this->openStream($receivedData, $userId, $idProject)
							: $this->closeStream($receivedData, $token));
	}

	private function openStream($receivedData, $userId, $idProject){
		if ($receivedData["path"][0] != "/")
			return header("HTTP1.0 400 Bad Request", True, 400);
		$em = $this->getDoctrine()->getManager();
		$stream = new CloudTransfer();
		$stream->setCreatorId($userId)
					 ->setFilename($receivedData["filename"])
					 ->setPath('/GrappBox Projects/'.(string)$idProject.$receivedData["path"])
					 ->setPassword($receivedData["password"])
					 ->setCreationDate(new DateTime("now"))
					 ->setDeletionDate(null);
		$em->persist($stream);
		$em->flush();
		return new JsonResponse(array("stream_id" => $stream->getId()));
	}

	private function closeStream($receivedData, $token){
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
		$em = $this->getDoctrine()->getManager();
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		$user_id = $this->getUserId($token);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			throw $this->createAccessDeniedException();

		//Here the user have the authorization to close this stream
		if (!is_null($stream->getPassword()))
		{
			//Here add the CloudSecuredFileMetadata infos
			$meta = new CloudSecuredFileMetadata();
			$meta->setFilename($stream->getFilename())
					 ->setPassword($stream->getPassword())
					 ->setCreationDate(new DateTime("now"))
					 ->setCloudPath($stream->getPath());
			$em->persist($meta);
		}

		//Open cloud connection
		$client = new Sabre\DAV\Client(self::$settingsDAV);
		$adapter = new League\Flysystem\WebDAV\WebDAVAdapter($client);
		$flysystem = new League\Flysystem\Filesystem($adapter);
		//Copy & rename the file in the right folder
		$filesystem->copy('/Grappbox Transfer/'.(string)$stream->getId().'.transfer', (string)$stream->getPath().(string)$stream->getFilename());
		//Delete the transfer file
		$filesystem->delete('/Grappbox Transfer/'.(string)$stream->getId().'.transfer');
		$stream->setDeletionDate(new DateTime("now"));
		$em->persist($stream);
		$em->flush();
		return header("HTTP/1.0 200 OK", True, 203);
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
	public function sendFileAction(Request $request){
		//Check if request method is catched by the API
		$method = $request->getMethod();
		if ($method != "PUT")
			throw $this->createNotFoundException('The method does not exist');
		//Check Authorization to access cloud and to upload that file
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
		$token = $request->get("session_infos")["token"];
		$receivedData = $request->get("stream_infos");
		$user_id = $this->getUserId($token);
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			throw $this->createAccessDeniedException();

		//Here the user have the right authorization, so upload the file's chunk

		$client = new Sabre\DAV\Client(self::$settingsDAV);
		$adapter = new League\Flysystem\WebDAV\WebDAVAdapter($client);
		$flysystem = new League\Flysystem\Filesystem($adapter);
		$flysystem->put('/Grappbox Transfer/'.(string)$receivedData["stream_id"].'.transfer', (string)$receivedData["file_chunk"]);
		return header("HTTP/1.0 200 OK", True, 203);
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
	public function getListAction($token, $idProject, $path, Request $request)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			throw $this->createNotFoundException('The method does not exist');
		if ($this->checkTokenAuthorization($token, $idProject) < 0)
			throw $this->createAccessDeniedException();
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$rpath = "/GrappBox Projects/".(string)($idProject).str_replace(",", "/", $path);

		$content = $adapter->listContents($rpath);
		return new JsonResponse(array("path" => $rpath,
																	"data" => $content));
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
	public function getFileAction(Request $request){
		//Check if request method is catched by the API
		$method = $request->getMethod();
		if ($method != "POST")
			throw $this->createNotFoundException('The method does not exist');
		//Check if user have authorization to modify cloud for this project
		$dbManager = $this->getDoctrine()->getManager();
		$token = $request->get("session_infos")["token"];
		$userId = $this->getUserId($token);
		$receivedData = $request->get("stream_infos");
		$idProject = $receivedData["project_id"];
		//if ($this->checkTokenAuthorization($token, $idProject) < 0)
		//	throw $this->createAccessDeniedException();

		//Here we have authorization to get the encrypted file, Client have to decrypt it after reception, if it's a secured file
		return new BinaryFileResponse("http://cloud.grappbox.com/index.php/s/pwAcLbcTs2Ccing");
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
