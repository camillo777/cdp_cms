<?php

class Tag
{
  // Properties
  public $id_tag = null;
  public $tag_name = null;
  public $tag_type = null;

  public function toArray()
  {
    return [
      "id_tag" => $this->id_tag,
      "tag_name" => $this->tag_name,
      "tag_type" => $this->tag_type,
    ];
  }

  /**
  * Sets the object's properties using the values in the supplied array
  *
  * @param assoc The property values
  */

  public function __construct( $data=array() ) {
    if ( isset( $data['id_tag'] ) ) $this->id_tag = (int) $data['id_tag'];
    if ( isset( $data['tag_name'] ) ) $this->tag_name = $data['tag_name'];
    if ( isset( $data['tag_type'] ) ) $this->tag_type = $data['tag_type'];
  }


  /**
  * Sets the object's properties using the edit form post values in the supplied array
  *
  * @param assoc The form post values
  */

  public function storeFormValues ( $params ) {
    // Store all the parameters
    $this->__construct( $params );
  }

} // end Tag

?>
