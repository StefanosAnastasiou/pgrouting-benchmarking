<?php

require_once('model.php'); 
 
$connection = new Connection;

// Capture the requests coordinates
$connection->IncomingCoords();     


if($connection->keyX == 'x1' && $connection->keyY == 'y1'){
     
//Retrieve the closest node to the first click
    $connection->query('SELECT  
           ST_X(d.geom),      
           ST_Y(d.geom)
           FROM (SELECT 
              (ST_DumpPoints(
                 (SELECT r.geom as geom
                   FROM public.roads_noded r
                   ORDER BY 
                   r.geom <-> ST_SetSRID(ST_MakePoint( :x1, :y1), 2100)
                    ASC
                    LIMIT 1
               ))).geom as geom FROM public.roads_noded r
              ) AS d
          ORDER BY ST_Distance(d.geom, ST_SetSRID(ST_MakePoint(:x1, :y1), 2100)) ASC
         LIMIT 1;');   



       $connection->bind(':x1', $connection->x1);

       $connection->bind(':y1', $connection->y1);

       $connection->executeQuery();

       $firstResult = $connection->fetchResults();

    
       //pass the values to the temporary database
       $connection->tempDB('x1','y1', $firstResult[0]['st_x'], $firstResult[0]['st_y']); 
      

 

 }elseif($connection->keyX == 'x2' && $connection->keyY == 'y2'){

   //retrieve the closest node next to the second click
      $connection->query('SELECT    
           ST_X(d.geom),      
           ST_Y(d.geom)
           FROM (SELECT 
              (ST_DumpPoints(
                 (SELECT r.geom as geom
                   FROM public.roads_noded r
                   ORDER BY 
                   r.geom <-> ST_SetSRID(ST_MakePoint( :x2, :y2), 2100)
                    ASC
                    LIMIT 1
               ))).geom as geom FROM public.roads_noded r
              ) AS d
          ORDER BY ST_Distance(d.geom, ST_SetSRID(ST_MakePoint(:x2, :y2), 2100)) ASC
         LIMIT 1;');



      $connection->bind(':x2', $connection->x2);

      $connection->bind(':y2', $connection->y2);

      $connection->executeQuery();

      $secondResult = $connection->fetchResults();

      //pass the value to the temporary database
      $connection->tempDB('x2', 'y2', $secondResult[0]['st_x'], $secondResult[0]['st_y']);

      //Query the stored function as fetch the results as XML
      $connection->queryGeometry(); 

      //clear the temporary database
      $connection->clearTempDb();

 }else{

    exit();
 }


?>
