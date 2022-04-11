<?php

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\FcmOptions;

class MsgController
{

    var $factory = null;
    var $messaging = null;
    var $connect = null;

    public function __construct() {
        $this->factory = (new Factory())
            //->withServiceAccount('./cdp-club-3a7a8-firebase-adminsdk-8djfy-007960c77c.json')
            ->withServiceAccount(GOOGLE_SERVICE_ACCOUNT)
            ->withDisabledAutoDiscovery();
            
        $this->messaging = $this->factory->createMessaging();

        $this->connect = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function infoToken($token, $infotype) {

        if (is_null($token)) throw new Exception("specify token");
        if (is_null($this->messaging)) throw new Exception("messaging is NULL");
        
        $appInstance = $this->messaging->getAppInstance($token);
        // Return the full information as provided by the Firebase API
            
        if ($infotype == "info") {
            return $appInstance->rawData();
        }
        else {
            return $appInstance->topicSubscriptions();
        }
    }


    /*

    SELECT usto.userid,usto.email,name,usto.topics,usto.token FROM 
    camillo.user_tokens usto,
    parrucchiere_db.ps_customer cust, 
    parrucchiere_db.ps_customer_group cugr, 
    parrucchiere_db.ps_group_lang  grla WHERE
    usto.userid=cust.id_customer
    AND cust.id_customer=cugr.id_customer
    AND cugr.id_group=grla.id_group
    AND grla.id_lang=1




    */

    public function sendToTopic($msg){
        $this->send($msg);

        return new Result(ST_OK, "Message sent to topic", null, $msg->toArray());
    }

    public function sendToToken($msg){
        $this->send($msg);

        return new Result(ST_OK, "Message sent to token", null, $msg->toArray());
    }

    private function send($msg) {
        
            //$topic = 'cdpnews';
            $notification = [
                "title" => $msg->title, 
                "body" => $msg->body
            ];

            $notif_data = [];

            if (isset($msg->data_view)){
                $notif_data = [
                    "data_view" => $msg->data_view, // category, product, post
                    "data_id" => $msg->data_id,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ];
            }
            $fcmOptions = [
                'analytics_label' => 'notification1'
            ];
            
            isset($msg->topic)?
            $message = CloudMessage::withTarget('topic', $msg->topic)
                ->withNotification($notification)
                ->withData($notif_data)
                ->withFcmOptions($fcmOptions) 
            :
            $message = CloudMessage::withTarget('token', $msg->token)
                ->withNotification($notification)
                ->withData($notif_data)
                ->withFcmOptions($fcmOptions) 
            ;

            // $message = CloudMessage::fromArray([
            //     'token' => $deviceToken,
            //     'notification' => [/* Notification data as array */], // optional
            //     'data' => [/* data array */], // optional
            // ]);

            // ***
            $this->messaging->send($message);

            $sql = "INSERT INTO sent (title, body, topic, token, data_view, data_id) VALUES (:title,:body,:topic,:token,:data_view,:data_id)";
            $st = $this->connect->prepare($sql);
            $st->bindValue("title", $msg->title, PDO::PARAM_STR);
            $st->bindValue("body", $msg->body, PDO::PARAM_STR);
            $st->bindValue("topic", isset($msg->topic)?$msg->topic:null, PDO::PARAM_STR);
            $st->bindValue("token", isset($msg->token)?$msg->token:null, PDO::PARAM_STR);
            $st->bindValue("data_view", isset($msg->data_view)?$msg->data_view:null, PDO::PARAM_STR);
            $st->bindValue("data_id", isset($msg->data_id)?$msg->data_id:null, PDO::PARAM_STR);
            $st->execute();
    }


    private function isTokenAlreadyIn($token) {

        $sql = "SELECT * FROM user_tokens WHERE token=:token";
        $st = $this->connect->prepare($sql);
        $st->bindValue("token", $token, PDO::PARAM_STR);
        $st->execute();
        $result = $st->fetchAll();
        if (count($result)>0) return true;
        return false;

    }

    public function addToken($data) {
        error_log("addToken");

        $token = $data["token"];

        //$sql = "SELECT count(*) AS ntoken FROM user_tokens WHERE token=:token";
        if ($this->isTokenAlreadyIn($token)) {
            $this->updateToken($data); // esiste già nel DB
            return new Result(ST_OK, "Updated token: $token");
        }
        else {
            $this->insertToken($data); // nuovo token
            return new Result(ST_OK, "Added token: $token");
        }
    }



    private function insertToken($data){
        error_log("insertToken");
        $sql = "INSERT INTO user_tokens (userid, email, token, topics, env) VALUES (:userid,:email,:token,:topics,:env)";
        $st = $this->connect->prepare($sql);
        $st->bindValue("userid", $data["userid"], PDO::PARAM_INT);
        $st->bindValue("email", $data["email"], PDO::PARAM_STR);
        $st->bindValue("token", $data["token"], PDO::PARAM_STR);
        $st->bindValue("topics", $data["topics"], PDO::PARAM_STR);
        $st->bindValue("env", $data["env"], PDO::PARAM_STR);
        $st->execute();

    }


    private function updateToken($data){
        error_log("updateToken");
        $sql = "UPDATE user_tokens SET topics=:topics, env=:env, userid=:userid, email=:email where token=:token limit 1";
        $st = $this->connect->prepare($sql);
        $st->bindValue("userid", $data["userid"], PDO::PARAM_INT);
        $st->bindValue("email", $data["email"], PDO::PARAM_STR);
        $st->bindValue("token", $data["token"], PDO::PARAM_STR);
        $st->bindValue("topics", $data["topics"], PDO::PARAM_STR);
        $st->bindValue("env", $data["env"], PDO::PARAM_STR);
        $st->execute();

    }


    // function insert($d, $conn){

    //     $sql = "INSERT INTO sent (title, body, created, topic, token) VALUES (:title,:body,now(),:topic,:token)";
    //     $st = $conn->prepare($sql);
    //     $st->bind_param("title",$d["title"],
    //         $d["body"],
    //         $d["topic"],
    //         $d["token"]
    //     );
    //     $st->execute();
        
    //     $result = new Result();
    //     $result->status = ST_OK;
    //     $result->msg = "insert ".time();
    //     return $result;
    // }

}

?>