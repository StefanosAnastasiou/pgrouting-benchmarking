<?php

class Connection{

  private $host = 'your_host';        
  private $user = 'userName';         
  private $password = 'dbPassword';  
  private $dbname = 'dbName';     
  private $dbh;
  private $stmt;
  public $keyX;
  public $keyY;
  public $x1;
  public $y1;
  public $x2;
  public $y2;
  public $remote_user;       
 

  public function __construct(){

    $this->remote_user = $_SERVER['REMOTE_ADDR'];

    $options = array(
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,   //Minimizes SQL injections
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      );

       $this->dbh = new PDO('pgsql:host='.$this->host.';dbname='.$this->dbname, $this->user, $this->password, $options); 

   }



  public function IncomingCoords(){

      $var = $_GET;

      if (isset(array_values($var)[0]) && isset(array_values($var)[1])){
            
          $this->keyX = array_keys($var)[0];
          $this->keyY = array_keys($var)[1]; 
                  
             if($this->keyX == 'x1' && $this->keyY == 'y1'){

                  $this->x1 = array_values($var)[0];
                  $this->y1 = array_values($var)[1];

              }elseif($this->keyX == 'x2' && $this->keyY == 'y2'){

                   $this->x2 = array_values($var)[0];
                   $this->y2 = array_values($var)[1];

              }

        }else{
              exit();
      }
        
    }


  public function query($sql){ 

    $this->stmt = $this->dbh->prepare($sql);

  } 
  

   public function bind($param, $value, $type=null){ 

      if (is_null($type)) {
         switch (true) {
            case is_int($value):
            $type = PDO::PARAM_INT;
            break;
         case is_string($value):
            $type = PDO::PARAM_STR;
            break;
         case is_bool($value):
            $type = PDO::PARAM_BOOL;
            break;
         case is_float($value):
            $type = PDO::PARAM_STR;
         default:
            exit();
          }           

        }

         $this->stmt->bindValue($param, $value, $type);
    }

  

   public function executeQuery(){
    
      $this->stmt->execute();

   }


     public function fetchResults(){   

        $this->executeQuery();

        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
     }


   //The results are coming asynchronouysly and are stored in a temporary database. # To be revised. May cause trouble if the user selects start point and closes page without end point.
    public function tempDB($keyXval, $keyYval, $xValue, $yValue){
        
      
      if($keyXval == 'x1' && $keyYval == 'y1'){

              $this->query('INSERT INTO temporary (x1, y1, user_id) VALUES (:xValue, :yValue, :user_id)');
              $this->bind(':xValue', $xValue);
              $this->bind(':yValue', $yValue);
              $this->bind(':user_id', $this->remote_user);              
              $this->executeQuery();

      }elseif($keyXval == 'x2' && $keyYval == 'y2'){

              $this->query('UPDATE temporary SET (x2, y2) = (:xValue, :yValue) WHERE user_id = :user_id');
              $this->bind(':xValue', $xValue);
              $this->bind(':yValue', $yValue);
              $this->bind(':user_id', $this->remote_user);
              $this->executeQuery();

      }

    } 


     public function queryGeometry(){
      
       $this->query('SELECT * FROM routing_function(
              (SELECT x1 FROM temporary WHERE user_id = :user_id),
              (SELECT y1 FROM temporary WHERE user_id = :user_id),
              (SELECT x2 FROM temporary WHERE user_id = :user_id),
              (SELECT y2 FROM temporary WHERE user_id = :user_id)
         );'); 
        
        $this->bind(':user_id', $this->remote_user);

        $this->executeQuery();

        $dbResult = $this->fetchResults();
        

        header("Content-type:text/xml");
 
        $xml = new SimpleXMLElement('<xml/>');

        foreach ($dbResult as $result) {      
 
           $xml->addChild('street', $result['street']);
           $xml->addChild('geometry', $result['geom']); 

        }

        json_encode($xml);
        ob_clean();
        print($xml->asXML()); 
    
    }    

     public function clearTempDb(){      

        $this->query('DELETE FROM temporary WHERE user_id = :user_id');
        $this->bind(':user_id', $this->remote_user);
        $this->executeQuery();

     }

}

?> 
