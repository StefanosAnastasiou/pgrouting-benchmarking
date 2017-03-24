# pgrouting-benchmarking
Comparison between client-side shortest path and MapServer rendering

This is a simple application in Web Mapping and shorest path routing using Open Source Software and Technologies. Two different methods are developed and tested. In the first approach the shortest path is computed in the database with the use of Dijkstra's algorithm, having as input from the user a start point and an end point. The shortest path is rendered as a WMS service directly in MapServer with Run-time Substitution mechanism.

In the second approach the shortest path is also computed in the database having as input a start point and an end poitn from the user. The input are sent (via AJAX) to a php script, which calculates the closest node of the road network next to the user's input. The new nodes are sent to the database which calculates the shortest path and finally php sends the result as XML to the client-side for rendering

Developed and tested with:
   
   Web Μapping Εngine:   Compiled MapServer 7.0.1 
   Client-side       :   OpenLayers 3.2
   Server-side       :   php 5.5.38
   Database          :   PostGIS 2.3.2
   Routing           :   pgRouting 2.3.1
