CREATE OR REPLACE FUNCTION routing_function(
    IN x1 numeric,
    IN y1 numeric,
    IN x2 numeric,
    IN y2 numeric)
  RETURNS TABLE(seq integer, path_seq integer, node bigint, edge bigint, cost double precision, agg_cost double precision, street text, geom text) AS
$BODY$
   WITH dijkstra AS(
	SELECT * FROM pgr_dijkstra('SELECT id, source, target, time AS cost FROM roads_noded',
	--source
	(SELECT source FROM roads_noded ORDER BY geom <-> ST_SetSrid(ST_MakePoint($1, $2), 2100) ASC LIMIT 1),
	--target
	(SELECT target from roads_noded ORDER BY geom <-> ST_SetSrid(ST_MakePoint($3, $4), 2100) ASC LIMIT 1),
	-- undirected
	false)	
   ),
    the_geom AS(
	SELECT dijkstra.*, b.street, ST_AsText(b.geom) FROM dijkstra INNER JOIN roads_noded AS b on dijkstra.edge = b.id
    )    
      SELECT * FROM the_geom WHERE CASE WHEN 'source' != 'target' THEN true ELSE false END;
$BODY$
  LANGUAGE sql VOLATILE
