<?php

/**
 * Description: The point-in-polygon algorithm allows you to check if a point is
 * inside a polygon or outside of it.
 * Author: Michaël Niessen (2009)
 * Website: http://AssemblySys.com
 */

namespace HbgEventImporter\Helper;

class PointInPolygon
{
    public static function pointInPolygon($point, $polygon, $pointOnVertex = true)
    {
        // Transform string coordinates into arrays with x and y values
        $point = self::pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = self::pointStringToCoordinates($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($pointOnVertex == true && self::pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            $vertex2 = $vertices[$i];
            // Check if point is on an horizontal polygon boundary
            if ($vertex1['y'] == $vertex2['y'] && $vertex1['y'] == $point['y'] && $point['x'] > min($vertex1['x'], $vertex2['x']) && $point['x'] < max($vertex1['x'], $vertex2['x'])) {
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) && $point['y'] <= max($vertex1['y'], $vertex2['y']) && $point['x'] <= max($vertex1['x'], $vertex2['x']) && $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                // Check if point is on the polygon boundary (other than horizontal)
                if ($xinters == $point['x']) {
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    public static function pointOnVertex($point, $vertices)
    {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }

    public static function pointStringToCoordinates($pointString)
    {
        $coordinates = explode(",", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
}