<?php

// error_reporting(E_ALL);
// ini_set( "display_errors", false );

require __DIR__.'/../vendor/autoload.php';

require( "../config.php" );
require( "../utils.php" );
require( "../errors.php" );

require( "Article.php" );
require( "Tag.php" );
require( "Msg.php" );
require( "ArticleController.php" );
require( "MsgController.php" );

/*
session_start();
$username = isset( $_SESSION['username'] ) ? $_SESSION['username'] : "";

if (($action!='getbyid')&&($action!='getbytag')){
  // CHECK LOGIN
  if ( $action != "login" && $action != "logout" && !$username ) {
    login();
    exit;
  }  
}
*/

$api = isset($_GET['api']) ? $_GET['api'] : "api";
$action = isset($_GET['action']) ? $_GET['action'] : "action";
//echo $action;


$result = null;

try {
  switch($api){
    case "article":
      $result = articleAction($action);
    break;
    case "messaging":
      $result = messagingAction($action);
    break;
  }
}
catch(Exception $e){
  http_response_code(400);
  $result = new Result(ST_ERR, $e->__toString());
  header('Content-Type: application/json');
  echo json_encode($result);
  exit(EXIT_ERR);
}

if (!is_null($result)){
  header('Content-Type: application/json');
  echo json_encode($result);
  exit(EXIT_OK);
}

// if no result, then show index

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Page Title</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' href='https://cdnjs.cloudflare.com/ajax/libs/bulma/0.8.0/css/bulma.min.css'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate' />
  <meta http-equiv='Pragma' content='no-cache' />
  <meta http-equiv='Expires' content='0' />
</head>
<body>
<div class="section">
<div class="container">
<div class="title">Test API</div>
<div class="list">
  <a class="list-item" href="./?api=article&action=list&active=1">Lista Post attivi</a>
  <a class="list-item" href="./?api=article&action=list">Lista tutti i Post</a>
  <a class="list-item" href="./?api=article&action=tag_list">Lista tutti i Tag</a>
  <a class="list-item" href="./?api=article&action=listbytag&id_tag=3,10,11">List by tags</a>
  <a class="list-item" href="./?api=article&action=getbyid&id_article=4">Post by id</a>
  <a class="list-item" href="./?api=article&action=update&id=3&title=ciao">Update post</a>
  <a class="list-item" href="./?api=article&action=toggleactive&id_article=3">Attiva/Disattiva post</a>
</div>
</div>
</div>
</div>
</body>
</html>
<?php

function articleAction($action){

  $result = null;

  $ac = new ArticleController(DB_DSN, DB_USERNAME, DB_PASSWORD);

  switch($action){
    case "list":
      $active = false;
      if (isset($_REQUEST['active'])) $active = $_REQUEST['active']==1?true:false;
      $result = $ac->getList(100,$active);
      break;
    case "listbytag":
      if (!isset($_REQUEST['id_tag'])) throw new Exception("'id_tag' not found");
      $tagIDs = explode(",", $_REQUEST['id_tag']);
      $result = $ac->getByTags($tagIDs);
      break;
    case "tag_list":
      $result = $ac->getAllTags();
      break;
    case "new":
      $article = new Article;
      $article->storeFormValues( $_POST );
      $id_article = $ac->new($article);
      $result = new Result(ST_OK, "Nuovo articolo inserito con ID: $id_article");
      break;
    case "update":
      if (!isset($_REQUEST['id_article'])) throw new Exception("article ID not found");
      $article = new Article;
      $article->storeFormValues( $_REQUEST );
      $ac->update($article);
      $result = new Result(ST_OK, "article_update for ID: [$article->id_article]");
      break;
    case "toggleactive":
      if (!isset($_REQUEST['id_article'])) throw new Exception("article ID not found");
      $id_article = $_REQUEST['id_article'];
      $ac->toggleArticle($id_article);
      $result = new Result(ST_OK, "article_toggleactive for ID: [$id_article]");
      break;
    case "uploadimg":
      if (!isset($_REQUEST['id_article'])) throw new Exception("article ID not found");
      $id_article = $_REQUEST['id_article'];
      $ac->uploadMedia($id_article, $_FILES);
      $result = new Result(ST_OK, "article_uploadimg for ID: [$id_article]");
      break;
    case "toggletag":
      if (!isset($_REQUEST['id_article'])) throw new Exception("id_article not found");
      if (!isset($_REQUEST['id_tag'])) throw new Exception("id_tag not found");
      $id_article = $_REQUEST['id_article'];
      $id_tag = $_REQUEST['id_tag'];
      $ac->toggleTag($id_tag, $id_article);
      $result = new Result(ST_OK, "article_toggletag for ID: [$id_article]");
      break;
    case "getbyid":
      if (!isset($_REQUEST['id_article'])) throw new Exception("article ID not found");
      $id_article = $_REQUEST['id_article'];
      $result = $ac->getById($id_article);
      break;
    }
    return $result;
}

function messagingAction($action){

  $result = null;

  $msgController = new MsgController();

  switch($action){
    case "send_to_topic":
      if (!isset($_REQUEST["title"])) throw new Exception("specify title"); 
      if (!isset($_REQUEST["body"])) throw new Exception("specify body"); 
      if (!isset($_REQUEST["topic"])) throw new Exception("specify topic");
      $msg = new Msg;
      $msg->storeFormValues( $_REQUEST );
      $result = $msgController->sendToTopic($msg);
      break;
    case "send_to_token":
      if (!isset($_REQUEST["title"])) throw new Exception("specify title"); 
      if (!isset($_REQUEST["body"])) throw new Exception("specify body"); 
      if (!isset($_REQUEST["token"])) throw new Exception("specify token");
      $msg = new Msg;
      $msg->storeFormValues( $_REQUEST );
      $result = $msgController->sendToToken();
      break;
    case "token_info":
      if (!isset($_REQUEST['token'])) throw new Exception("'token' not found");
      if (!isset($_REQUEST['infotype'])) throw new Exception("'infotype' not found");
      $token = $_REQUEST['token'];
      $infotype = $_REQUEST['infotype'];
      $result = $msgController->infoToken($token, $infotype);
      break;
    case "add_token":
      if (!isset($_REQUEST['token'])) throw new Exception("'token' not found");

      $token = urldecode($_REQUEST['token']);
      $userid = !isset($_REQUEST['userid']) ? "0" : $_REQUEST['userid'];
      $email = !isset($_REQUEST['email']) ? "" : $_REQUEST['email'];
      $topics = !isset($_REQUEST['topics']) ? "" : $_REQUEST['topics'];
      $env = !isset($_REQUEST['env']) ? "" : urldecode($_REQUEST['env']);

      $data = [
        "userid" => $userid,
        "token" => $token,
        "email" => $email,
        "topics" => $topics,
        "env" => $env
      ];
      
      $result = $msgController->addToken($data);
      break;
  }
  return $result;
}

?>
