<?php

class ArticleController
{
    var $connect = null;

    public function __construct($dsn, $user, $pass) {
        $this->connect = new PDO( $dsn, $user, $pass );
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }



    public function getList( 
        $numRows=1000000, 
        $onlyActive = true 
        ) {
      if ($this->connect == null) throw new Exception("DB connection error");
      $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) AS publicationDate, UNIX_TIMESTAMP(created) AS created, UNIX_TIMESTAMP(updated) AS updated"
        ." FROM articles"
        . ($onlyActive?" WHERE active=1":"")
        ." ORDER BY created DESC LIMIT :numRows";
      //echo $sql;
      $st = $this->connect->prepare( $sql );
      $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
      //if (!is_null($tagID)) $st->bindValue( ":tagID", $tagID, PDO::PARAM_INT );
      $st->execute();
      $result = $st->fetchAll();
  
      $list = array();
      foreach ( $result as $row ) {
        $article = new Article( $row );
        //$tags = $conn
        //  ->exec("SELECT * FROM mm_article_tags WHERE id_article='".$article->id."'")
        //  ->fetchAll();
        $sql = "SELECT * FROM mm_article_tag LEFT JOIN tags ON (mm_article_tag.id_tag=tags.id_tag) WHERE id_article='".$article->id_article."'";
        $result_tags = $this->connect->query($sql)->fetchAll();
        //$article->tagIDs = [];
        //if ($tags){
          foreach($result_tags as $result_tag) {
            $tag = new Tag($result_tag);
            $article->tags[] = $tag;
              //echo $user['username'] . '<br />';
              // array_push($article->tagIDs, [
              //   'id' => $tag['id_tag'],
              //   'name' => $tag['tag_name']
              // ]);
          }
          //echo json_encode($article->tagIDs);
        //}
        //else $article->tagIDs = [];
  
        $list[] = $article;
        }
    return $list;
    }



    // function _getTagAnd($tagIDs){
    //   $s = "";
    //   foreach($tagIDs as $tagID){
    //     $s .= " AND tags.id_tag=$tagID";
    //   }
    //   return $s;
    // }

    

    function _getTagListForSql($tagIDs){
      $s = "(";
      $n = 0;
      foreach($tagIDs as $tagID){
        if ($n!=0) $s .= ",";
        $s .= "$tagID";
        $n++;
      }
      $s .= ")";
      return $s;
    }

    public function getByTags($tagIDs) {
      if ($this->connect == null) throw new Exception("DB connection error");
      $sql = "SELECT a.id_article"
        ." FROM articles AS a, mm_article_tag AS at, tags AS t"
        //." JOIN ITEM_TAGS it ON it.id_local = i.uid"
        ." WHERE a.id_article=at.id_article" 
        ." AND t.id_tag=at.id_tag"
        ." AND t.id_tag IN "
        //." (1, 2)"
        .$this->_getTagListForSql($tagIDs)
        ." GROUP BY a.id_article"
        ." HAVING COUNT(DISTINCT t.id_tag) = ".count($tagIDs);
      $result_articles = $this->connect->query($sql)->fetchAll();
      $list = [];
      if (count($result_articles)>0){
        foreach($result_articles as $result_article){
          $id_article = $result_article["id_article"];
          $list[] = $this->getById($id_article);
        }
      }
      return $list;
    }

    

    public function new($a) {
      if (is_null($this->connect)) throw new Exception("DB connection error");

      $sql = "INSERT INTO articles ( title, created ) VALUES ( :title, now() )";
      $st = $this->connect->prepare ( $sql );
      //$st->bindValue( ":publicationDate", $a->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":title", $a->title, PDO::PARAM_STR );
      //$st->bindValue( ":summary", $a->summary, PDO::PARAM_STR );
      //$st->bindValue( ":content", $a->content, PDO::PARAM_STR );
      //$st->bindValue( ":img", $a->content, PDO::PARAM_STR );
      $st->execute();
      $id_article = $this->connect->lastInsertId();
      if ( is_null( $id_article ) )
        throw new Exception("Article::insert(): Cannot get inserted ID");
      return $id_article;
    }

    public function insert($a) {
      // Does the Article object already have an ID?
      if (is_null($this->connect)) throw new Exception("DB connection error");

      $this->connect->beginTransaction();

      try{
  
      $sql = "INSERT INTO articles ( title, content, img ) VALUES ( :title, :content, :img )";
      $st = $this->connect->prepare ( $sql );
      //$st->bindValue( ":publicationDate", $a->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":title", $a->title, PDO::PARAM_STR );
      //$st->bindValue( ":summary", $a->summary, PDO::PARAM_STR );
      $st->bindValue( ":content", $a->content, PDO::PARAM_STR );
      $st->bindValue( ":img", $a->content, PDO::PARAM_STR );
      $st->execute();
      $id_article = $this->connect->lastInsertId();
      if ( is_null( $id_article ) )
        throw new Exception("Article::insert(): Cannot get inserted ID");
  
      //echo $this->tagIDs[0];
      foreach($this->tagIDs as $tagID){
        $this->connect->exec("INSERT INTO mm_article_tag (id_article, id_tag) VALUES ('".$id_article."','".$tagID."')");
      }
      $this->connect->commit();
      return $id_article;

      }
      catch(Exception $e){
        $this->connect->rollback();
        throw new Exception($e->getMessage());
      }
    } // end insert

    function toggleArticle($id_article) {
      if (is_null($this->connect)) throw new Exception("DB connection error");
      if (is_null($id_article)) throw new Exception("Specify param 'id'");
      $sql = "UPDATE articles SET active = 1 - active WHERE id_article = :id_article";
      $st = $this->connect->prepare ( $sql );
      $st->bindValue( ":id_article", $id_article, PDO::PARAM_INT );
      $st->execute();
    } // end toggleArticle

    public function update($a) {
      if ( is_null( $a->id_article ) ) 
        throw new Exception("Article::update(): Attempt to update an Article object that does not have its ID property set.");
      try {
        $this->connect->beginTransaction();
  
        $sql = "UPDATE articles SET title=:title, content=:content, img=:img, link=:link WHERE id_article = :id_article";
        $st = $this->connect->prepare ( $sql );
        //$st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
        $st->bindValue( ":title", $a->title, PDO::PARAM_STR );
        //$st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
        $st->bindValue( ":content", $a->content, PDO::PARAM_STR );
        $st->bindValue( ":img", $a->img, PDO::PARAM_STR );
        $st->bindValue( ":id_article", $a->id_article, PDO::PARAM_INT );
        $st->bindValue( ":link", $a->link, PDO::PARAM_STR );
        $st->execute();

        //$st->debugDumpParams();
  
/*
        if (isset($a->tagIDs)){
          $this->connect->exec("DELETE FROM mm_article_tag WHERE id_article='".$a->id."'");
          foreach($a->tagIDs as $tagID){
            $sql = "INSERT INTO mm_article_tag (id_article, id_tag) VALUES ( '".$a->id."' , '".$tagID."')";
            $this->connect->exec($sql);
          }
        }
*/
        $this->connect->commit();
      }
      catch(Exception $e){
        $this->connect->rollBack();
        throw new Exception($e->__toString());
      }
    }

    function updateImage($id_article, $imagefilename) {
      if (is_null($this->connect)) throw new Exception("DB connection error");
      if (is_null($id_article)) throw new Exception("Specify param 'id_article'");
      if (is_null($imagefilename)) throw new Exception("Specify param 'imagefilename'");

      $sql = "UPDATE articles SET img=:img WHERE id_article = :id_article LIMIT 1";
      $st = $this->connect->prepare ( $sql );
      $st->bindValue( ":img", $imagefilename, PDO::PARAM_STR );
      $st->bindValue( ":id_article", $id_article, PDO::PARAM_INT );
      $st->execute();
    }

    // $_FILES
    function uploadMedia($id_article, $_files){
       
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($_files['upfile']['error']) ||
            is_array($_files['upfile']['error'])
        ) 
        throw new RuntimeException('Invalid parameters.');
    
        // Check $_FILES['upfile']['error'] value.
        switch ($_files['upfile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }
    
        // You should also check filesize here.
        if ($_files['upfile']['size'] > 100000000)
            throw new RuntimeException('Exceeded filesize limit.');
    
        // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
        // Check MIME Type by yourself.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_files['upfile']['tmp_name']),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ),
            true
        )) throw new RuntimeException('Invalid file format.');
    
        // You should name it uniquely.
        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
        // On this example, obtain safe unique name from its binary data.
        $newfilename = sha1_file($_files['upfile']['tmp_name']).".".$ext;
        if (!move_uploaded_file(
            $_files['upfile']['tmp_name'],
            sprintf('../'.MEDIA_PATH.'/%s',
              $newfilename
            )
        )) throw new RuntimeException('Failed to move uploaded file.');
  
        $this->updateImage($id_article, $newfilename);
    }

    

    public function getAllTags() {
      if ($this->connect == null) throw new Exception("DB connection error");
      $sql = "SELECT * FROM tags ORDER BY tag_type";
      $result = $this->connect->query($sql)->fetchAll();

      $list = array();

      foreach ( $result as $row ) {
        $tag = new Tag( $row );
        $list[] = $tag;
      }
      return $list;
    }



    public function toggleTag($id_tag, $id_article) {
      if ($this->connect == null) throw new Exception("DB connection error");
      $sql = "SELECT * FROM mm_article_tag WHERE id_tag='$id_tag' AND id_article='$id_article'";
      //echo $sql;
      $result = $this->connect->query($sql)->fetchAll();
      if (count($result)>0){
        // delete
        $sql = "DELETE FROM mm_article_tag WHERE id_tag=$id_tag AND id_article=$id_article LIMIT 1";
        $this->connect->exec($sql);
      }
      else{
        // insert
        $sql = "INSERT INTO mm_article_tag (id_tag,id_article) VALUES ('$id_tag', '$id_article')";
        $this->connect->exec($sql);
      }
    }





    public function getById( $id_article ) {
      if ($this->connect == null) throw new Exception("DB connection error");
      $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate, UNIX_TIMESTAMP(created) AS created, UNIX_TIMESTAMP(updated) AS updated"
        ." FROM articles"
        ." WHERE id_article=:id_article";
        //. $onlyActive?" AND active=1":"";
      $st = $this->connect->prepare( $sql );
      $st->bindValue( ":id_article", $id_article, PDO::PARAM_INT );
      $st->execute();
      $row = $st->fetch();
      if ( $row ) $article = new Article( $row );
      else throw new Exception("Error retrieving ID $id_article");
      
      $sql = "SELECT * FROM mm_article_tag LEFT JOIN tags ON (mm_article_tag.id_tag=tags.id_tag) WHERE id_article='".$article->id_article."'";
      $result_tags = $this->connect->query($sql)->fetchAll();
      $article->tags = [];
      if ($result_tags){
        foreach($result_tags as $result_tag) {
            $tag = new Tag( $result_tag );
            $article->tags[] = $tag;
        }
      }
      return $article;
    }


} // end ArticleController

?>