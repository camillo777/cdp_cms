<?php

/**
 * Class to handle articles
 */

class Article
{
  // Properties
  public $id_article = null;
  public $publicationDate = null;
  public $title = null;
  public $summary = null;
  public $content = null;
  public $active = 0;
  public $img = null;
  public $imgurl = null;
  public $created;
  public $updated;

  public $tags;
  public $link;

  public function toArray()
  {
    return [
      "id_article" => $this->id_article,
      "publicationDate" => $this->publicationDate,
      "title" => $this->title,
      "summary" => $this->summary,
      "content" => $this->content,
      "tagIDs" => $this->tagIDs,
      "active" => $this->active,
      "img" => $this->img,
      "created" => $this->created,
      "updated" => $this->updated,
      "link" => $this->link
    ];
  }

  /**
  * Sets the object's properties using the values in the supplied array
  *
  * @param assoc The property values
  */

  public function __construct( $data=array() ) {
    if ( isset( $data['id_article'] ) ) $this->id_article = (int) $data['id_article'];
    if ( isset( $data['publicationDate'] ) ) $this->publicationDate = (int) $data['publicationDate'];
    if ( isset( $data['title'] ) ) $this->title = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['title'] );
    //if ( isset( $data['summary'] ) ) $this->summary = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['summary'] );
    if ( isset( $data['content'] ) ) $this->content = $data['content'];
    if ( isset( $data['tags'] ) ) $this->tags = $data['tags'];
    if ( isset( $data['active'] ) ) $this->active = $data['active'];
    if ( isset( $data['img'] ) ) {
      $this->img = $data['img'];
      $this->imgurl = SITE_URL .'/'.MEDIA_PATH.'/'.$this->img;
    }
    if ( isset( $data['created'] ) ) $this->created = (int) $data['created'];
    if ( isset( $data['updated'] ) ) $this->updated = (int) $data['updated'];
    if ( isset( $data['link'] ) ) $this->link = $data['link'];
  }


  /**
  * Sets the object's properties using the edit form post values in the supplied array
  *
  * @param assoc The form post values
  */

  public function storeFormValues ( $params ) {

    // Store all the parameters
    $this->__construct( $params );

    //echo $params["tagIDs"][0];
    //exit;

    // Parse and store the publication date
    if ( isset($params['publicationDate']) ) {
      $publicationDate = explode ( '-', $params['publicationDate'] );

      if ( count($publicationDate) == 3 ) {
        list ( $y, $m, $d ) = $publicationDate;
        $this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
      }
    }
  }

} // end Article

?>
