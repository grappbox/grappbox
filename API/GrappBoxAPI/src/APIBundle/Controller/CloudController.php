<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use \DateTime;

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
     public    $auth_name      = 'GrappBot';
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
             curl_setopt($curlRequest, CURLOPT_HTTPHEADER, array("Content-Type" => "multipart/form-data"));

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
			$userRepository = $this->getDoctrine()->getRepository("APIBundle:User");
      $user = $userRepository->findOneByToken($token);
			return (is_null($user) ? -1 : $user->getId());
	}

	private function checkUserCloudAuthorization($userId, $idProject)
	{
			$db = $this->getDoctrine();
      $role = $db->getRepository("APIBundle:ProjectUserRole")->findOneBy(array("projectId" => $idProject, "userId" => $userId));
      if (is_null($role))
        return (-1);
      $roleTable = $db->getRepository("APIBundle:Role")->findOneById($role->getId());
			return (is_null($roleTable) ? -1 : $roleTable->getCloud());
	}

	//This have to be a POST or DELETE request
	// POST : Open a stream
	// DELETE : Close a stream
	/* Requested json
	{
		session_infos: {
			token : "userToken"
      safe_password: "mustache"
		},
		stream_infos: {
      project_id : 42,
			//ON POST REQUEST ONLY//
			filename : "Awesomeness",
			path : "/LabEIP/TestUpload",
			password : "HashPasswordIfSecuredFileElseNullType"
			//ON DELETE REQUEST ONLY//
			stream_id : 21
		}
	}
	*/
	public function streamAction(Request $request){
		$method = $request->getMethod();
		$dbManager = $this->getDoctrine()->getManager();
    $json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$userId = $this->getUserId($token);
    $receivedData = $json["stream_infos"];
    $isSafe = ($method == "DELETE" ? false : preg_match("/Safe/", $receivedData["path"]));
    if ($isSafe){
      $project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
      $passwordEncrypted = (isset($json["session_infos"]["safe_password"]) ? $json["session_infos"]["safe_password"] : NULL); // TODO : SHA-1 512 Hashing when algo created!
    }
    else{
      $project = null;
      $passwordEncrypted = null;
    }
    if ($method == "POST")
    {
      $receivedData["filename"] = str_replace(" ", "|", $receivedData["filename"]);
      $receivedData["path"] = str_replace(" ", "|", $receivedData["path"]);
		  $idProject = $receivedData["project_id"];
    }
		if (($method == "POST" && $this->checkUserCloudAuthorization($userId, $idProject) <= 0) || ($isSafe && $passwordEncrypted != $project->getSafePassword()))
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
					 ->setPath('/GrappBox|Projects/'.(string)$idProject.$receivedData["path"])
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

		$stream->setDeletionDate(new DateTime("now"));
		$shareRequest = new CurlRequest();
		$shareRequest->setPost(array(
			"path" => (string)($stream->getPath()."/".$stream->getFilename()),
			"shareType" => (int)3,
			"publicUpload" => (bool)false,
			"permissions" => (int)1
		));
		var_dump($shareRequest->createCurl("http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares"));
		$em->persist($stream);
		$em->flush();
		return new JsonResponse(Array("infos" => "OK"));
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
        project_id : 42
				file_chunk : "ImAFileChunkAlreadyHashedWithThePassswordIfPassword",
        chunk_numbers: 2
        current_chunk : 1,
			}
 	  }
	*/
	public function sendFileAction(Request $request){
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
    $json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$receivedData = $json["stream_infos"];
		$user_id = $this->getUserId($token);
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
    if ($user_id < 0 || $user_id != $stream->getCreatorId())
			throw $this->createAccessDeniedException();

		//Here the user have the right authorization, so upload the file's chunk

		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$flysystem->put('/GrappBox|Projects/'.(string)$receivedData["project_id"]."/".$stream->getFilename().'-chunking-'.(string)$receivedData["stream_id"].'-'.$receivedData["chunk_numbers"].'-'.$receivedData["current_chunk"], (string)base64_decode($receivedData["file_chunk"]));
		return new JsonResponse(Array("infos" => "OK"));
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
	public function getListAction($token, $idProject, $path, $password, Request $request)
	{
    $userId = $this->getUserId($token);
    $isSafe = preg_match("/Safe/", $path);
    if ($isSafe){
      $project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
      $passwordEncrypted = $password; // TODO : SHA-1 512 Hashing when algo created!

    }
    else{
      $project = null;
      $passwordEncrypted = null;
    }
    if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
      throw $this->createAccessDeniedException();

		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
    $prepath = str_replace(" ", "|", str_replace(",", "/", $path));
		$rpath = "/GrappBox|Projects/".(string)($idProject).$prepath;

		$content = str_replace("|", " ", $adapter->listContents($rpath));
    foreach ($content as $i => $row)
    {
      $content[$i]["path"] = str_replace("remote.php/webdav/GrappBox%7cProjects/".(string)$idProject.$prepath.($prepath == "/" ? "": "/"), "", $content[$i]["path"]);
    }
		return new JsonResponse(array("data" => $content));
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
	public function getFileAction($cloudPath, $token, $idProject, $password = null, $passwordSafe = null, Request $request){
    $userId = $this->getUserId($token);
    $passwordEncrypted = $password; //TODO : sha-1 512 hashing Here in password
    $cloudPathArray = explode(',', $cloudPath);
    $filename = $cloudPathArray[count($cloudPathArray) - 1];
    unset($cloudPathArray[count($cloudPathArray) - 1]);
    $cloudBasePath = implode('/', $cloudPathArray);
    if ($cloudBasePath == "")
      $cloudBasePath = "/";
    $filePassword = $this->getDoctrine()->getRepository("APIBundle:CloudSecuredFileMetadata")->findOneBy(array("cloudPath" => "/GrappBox|Projects/".(string)$idProject.$cloudBasePath, "filename" => $filename));

    $isSafe = preg_match("/Safe/", $cloudPath);
    if ($isSafe)
    {
      $project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
      $passwordEncrypted = $password; // TODO : SHA-1 Hashing
    }
    else {
      $project == NULL;
      $passwordEncrypted = NULL;
    }
    if ($userId < 0 || (!is_null($filePassword) && $filePassword->getPassword() != $passwordEncrypted) || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
      throw $this->createAccessDeniedException();

		//Here we have authorization to get the encrypted file, Client have to decrypt it after reception, if it's a secured file
    $cloudPath = str_replace(',', '/', $cloudPath);
		$path = "http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares?path=".urlencode("/GrappBox|Projects/".(string)($idProject).$cloudPath);
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
   /*
    {
      session_infos: {
        token: "48q98d"
      },
      safe_infos: {
        project_id: 42,
        password: "6q8d4zq68d"
      }
    }
   */
	public function setSafePassAction(Request $request)
	{
    $dbManager = $this->getDoctrine()->getManager();
    $json = json_decode($request->getContent(), true);
    $token = $json["session_infos"]["token"];
    $userId = $this->getUserId($token);
    $idProject = (int)$json["safe_infos"]["project_id"];
    $project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
    if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || is_null($project))
      throw $this->createAccessDeniedException();

    $project->setSafePassword($json["safe_infos"]["password"]);
    $dbManager->persist($project);
    $dbManager->flush();
		return new JsonResponse(Array("infos" => "OK"));
	}

  public function createCloudAction($projectId, Request $request)
  {
    $client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$rpathSafe = "GrappBox|Projects/".(string)($projectId)."/Safe";
    $rpath = "GrappBox|Projects/".(string)($projectId);
		//HERE Create the dir in the cloud
    var_dump($flysystem->createDir($rpath));
    var_dump($flysystem->createDir($rpathSafe));
		return new JsonResponse(Array("infos" => $rpathSafe, "infos2" => $rpath));
  }

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="Delete something in the cloud, directory or file.",
	 * views = { "cloud" },
  	 * requirements={
     *      {
     *          "name"="Data",
     *          "dataType"="JSON",
     *          "description"="<a href='http://api.grappbox.locale/json/cloud/delete.json'>The following json</a>"
     *      }
     * }
     * )
	 *
	 */
 	/*

 	*/
	public function delAction(Request $request)
	{
    $json = json_decode($request->getContent(), true);
    $token = $json["session_infos"]["token"];
    $userId = $this->getUserId($token);
    $idProject = $json["deletion_infos"]["project_id"];
    if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || preg_match("/Safe/", $json["deletion_infos"]["path"]))
      throw $this->createAccessDeniedException();

    //Now we can delete the file or the directory
		$path = "/GrappBox|Projects/".(string)($idProject).str_replace(' ', '|', $json["deletion_infos"]["path"]);
    $client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
    $flysystem->delete($path);
		return new JsonResponse(Array("infos" => "OK"));
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
		$json = json_decode($request->getContent(), true);
    $token = $json["session_infos"]["token"];
    $userId = $this->getUserId($token);
		$idProject = $json["creation_infos"]["project_id"];
    if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0)
      return  $this->createAccessDeniedException();

		//Now we can create the directory at the proper place
		$path = $json["creation_infos"]["path"];
		$dirName = $json["creation_infos"]["dir_name"];
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
    $dirName = str_replace(' ', '|', $dirName);
		$rpath = "/GrappBox|Projects/".(string)($idProject).(string)($path)."/".$dirName;
		//HERE Create the dir in the cloud
    $flysystem->createDir($rpath);
		return new JsonResponse(Array("infos" => "OK"));
	}

}
