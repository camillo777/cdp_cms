<?php

class Msg
{
  // Properties
  public $topic = null;
  public $title = null;
  public $body = null;
  public $token = null;
  public $data_view = null;
  public $data_id = null;
  
  
  public function toArray()
  {
    return [
      "topic" => $this->topic,
      "title" => $this->title,
      "body" => $this->body,
      "token" => $this->token,
      "data_view" => $this->data_view,
      "data_id" => $this->data_id,
    ];
  }

  public function __construct( $data=array() ) {
    if ( isset( $data['topic'] ) ) $this->topic = $data['topic'];
    if ( isset( $data['title'] ) ) $this->title = $data['title'];
    if ( isset( $data['body'] ) ) $this->body = $data['body'];
    if ( isset( $data['token'] ) ) $this->token = $data['token'];
    if ( isset( $data['data_view'] ) ) $this->data_view = $data['data_view'];
    if ( isset( $data['data_id'] ) ) $this->data_id = $data['data_id'];
  }

  public function storeFormValues ( $params ) {

    // Store all the parameters
    $this->__construct( $params );

  }

} // end Msg

?>
