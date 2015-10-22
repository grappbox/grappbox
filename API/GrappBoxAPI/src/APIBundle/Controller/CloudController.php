<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use APIBundle\Entity\CloudTransfer;
use APIBundle\Entity\CloudSecuredFileMetadata;

use Sabre\DAV\Client;
use League\Flysystem\WebDAV\WebDAVAdapter;
use League\Flysystem\Filesystem;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CurlRequest {
     protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
     protected $_url;
     protected $_timeout;
     protected $_cookieFileLocation = './cookie.txt';
     protected $_post;
     protected $_postFields;
     protected $_referer ="http://cloud.grappbox.com/";

     protected $_session;
     protected $_webpage;
     protected $_status;
     public    $authentication = 1;
     public    $auth_name      = 'grappbox';
     public    $auth_pass      = 'GolfBravo$$';

     public function __construct($timeOut = 30)
     {
			 	 $this->_post = false;
         $this->_timeout = $timeOut;
         $this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';

     }

     public function setReferer($referer){
       $this->_referer = $referer;
     }

     public function setPost($postFields)
     {
        $this->_post = true;
        $this->_postFields = $postFields;
     }

     public function setUserAgent($userAgent)
     {
         $this->_useragent = $userAgent;
     }

     public function createCurl($url = 'nul')
     {
        if($url != 'nul'){
          $this->_url = $url;
        }

         $curlRequest = curl_init();

         curl_setopt($curlRequest,CURLOPT_URL,$this->_url);
         curl_setopt($curlRequest,CURLOPT_TIMEOUT,$this->_timeout);
         curl_setopt($curlRequest,CURLOPT_RETURNTRANSFER,true);

         if($this->authentication == 1){
           curl_setopt($curlRequest, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
         }
         if($this->_post)
         {
             curl_setopt($curlRequest,CURLOPT_POST,true);
             curl_setopt($curlRequest,CURLOPT_POSTFIELDS,$this->_postFields);

         }
				 else {
				 	curl_setopt($curlRequest,CURLOPT_HTTPGET,true);
				}

         $this->_webpage = curl_exec($curlRequest);
         $this->_status = curl_getinfo($curlRequest,CURLINFO_HTTP_CODE);
         curl_close($curlRequest);
				 return $this->_webpage;
     }

   public function getHttpStatus()
   {
       return $this->_status;
   }

   public function __tostring(){
      return $this->_webpage;
   }
}

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
		$token = $request->request->get("session_infos")["token"];
		$userId = $this->getUserId($token);
		$receivedData = $request->request->get("stream_infos");
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
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		//Copy & rename the file in the right folder
		$filesystem->copy('/Grappbox Transfer/'.(string)$stream->getId().'.transfer', (string)$stream->getPath().(string)$stream->getFilename());
		//Delete the transfer file
		$filesystem->delete('/Grappbox Transfer/'.(string)$stream->getId().'.transfer');
		$stream->setDeletionDate(new DateTime("now"));
		$shareRequest = new CurlRequest();
		$shareRequest->setPost(array(
			"path" => $stream->getPath()."/".$stream->getFilename(),
			"shareType" => 3,
			"publicUpload" => False,
			"permissions" => 1
		));
		$shareRequest->createCurl("http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/shares");
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
		$receivedData = $request->request->get("stream_infos");
		$user_id = $this->getUserId($token);
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			throw $this->createAccessDeniedException();

		//Here the user have the right authorization, so upload the file's chunk

		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
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
	public function getFileAction($cloudPath, $token, $idProject, Request $request){
		//Check if request method is catched by the API
		$method = $request->getMethod();
		if ($method != "GET")
			throw $this->createNotFoundException('The method does not exist');

		//Check if user have authorization to modify cloud for this project
		//if ($this->checkTokenAuthorization($token, $idProject) < 0)
		//	throw $this->createAccessDeniedException();
		//Here we have authorization to get the encrypted file, Client have to decrypt it after reception, if it's a secured file
		$cloudPath = str_replace(',','/', $cloudPath);
		$path = "http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares?path=".urlencode("/GrappBox Projects/".(string)($idProject).$cloudPath);
		$searchRequest = new CurlRequest();
		$searchResult = simplexml_load_string($searchRequest->createCurl($path));
		if ($searchResult->meta->statuscode != 100 ||
			$searchResult->data->element->share_type != "3")
			throw $this->createNotFoundException('file not found');
		return $this->redirect("http://cloud.grappbox.com/index.php/s/".(string)($searchResult->data->element->token)."/download");
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
	public function delAction(Request $request)
	{
		return new Response('del File Success');
	}
	//THIS HAVE TO BE A POST Request
	/*
		{
			session_infos: {
				token : "42bas684"
			},
			creation_infos: {
				path: "/InsideThisDir",
				dirName: "ThisIsADirName",
				project_id: 42
			}
		}
	*/
	public function createDirAction(Request $request)
	{
		$json = json_decode($request->getContent());
		//HERE DO THE authentication
		//Now we can create the directory at the proper place
		$idProject = $json["creation_infos"]["project_id"];
		$path = $json["creation_infos"]["path"];
		$dirName = $json["creation_infos"]["dirName"];
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$rpath = "/GrappBox Projects/".(string)($idProject).(string)($path)."/".$dirName;
		//HERE Create the dir in the cloud
		return new Response('create Dir Success')
	}

}
