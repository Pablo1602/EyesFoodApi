<?php

/**
 * Controlador del endpoint /appointments
 */
class search
{
    public static function get($urlSegments)
    {
        if (isset($urlSegments[4])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else if (isset($urlSegments[3])) {
            switch ($urlSegments[0]){
                case "noallergy":
                    return self::retrieveSearchAllergy($urlSegments[1],$urlSegments[2],$urlSegments[3]);
                    break;
            }         
        }
        if (isset($urlSegments[1])) {
            switch ($urlSegments[0]){
                case "foods":
                    return self::retrieveSearchFoods($urlSegments[1]);
                    break;
                
                case "additives": //deprecado
                    return self::retrieveSearchAdditives($urlSegments[1]);
                    break;
            }         
        }
    }

    public static function post($urlSegments)
    {

    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }
    
    private static function retrieveSearchFoods($query)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            /*$comando = "SELECT codigoBarras AS codigo, nombre FROM alimentos"
                    . " WHERE nombre LIKE ? LIMIT 50";*/
            
                $comando = "SELECT * FROM alimentos WHERE nombreAlimento LIKE ? LIMIT 50";
                //'7802820701210' así queda al hacerle bind
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $queryFinal = '%' . $query . '%';
                $sentencia->bindParam(1, $queryFinal);

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                return $sentencia->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new ApiException(
                    500,
                    0,
                    "Error de base de datos en el servidor",
                    "http://localhost",
                    "Hubo un error ejecutando una sentencia SQL en la base de datos. Detalles:" . $pdo->errorInfo()[2]
                );
            }

        } catch (PDOException $e) {
        throw new ApiException(
            500,
            0,
            "Error de base de datos en el servidor",
            "http://localhost",
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrieveSearchAdditives($query)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT codigoE AS codigo, aditivo AS nombre FROM aditivos"
                    . " WHERE codigoE LIKE ? OR aditivo LIKE ? OR codigoEBuscador LIKE ? LIMIT 50";

            $comando = "SELECT codigoE, aditivo, peligro_aditivo.gradoPeligro, origen_aditivo.origen, "
                    . "clasificacion_aditivo.clasificacion, descripcionAditivo, usoAditivo, "
                    . "efectosSecundariosAditivo "
                    . "FROM aditivos LEFT JOIN peligro_aditivo "
                    . "ON aditivos.idPeligroAditivo = peligro_aditivo.idPeligroAditivo"
                    . " LEFT JOIN origen_aditivo ON aditivos.idOrigenAditivo = origen_aditivo.idOrigenAditivo"
                    . " LEFT JOIN clasificacion_aditivo ON aditivos.idClasificacionAditivo = clasificacion_aditivo.idClasificacionAditivo"
                    . " WHERE aditivo LIKE ? OR codigoEBuscador LIKE ? LIMIT 50";
                //'7802820701210' así queda al hacerle bind
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $queryFinal = '%' . $query . '%';
                $sentencia->bindParam(1, $queryFinal);
                $sentencia->bindParam(2, $queryFinal);
                $sentencia->bindParam(3, $queryFinal);

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                return $sentencia->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new ApiException(
                    500,
                    0,
                    "Error de base de datos en el servidor",
                    "http://localhost",
                    "Hubo un error ejecutando una sentencia SQL en la base de datos. Detalles:" . $pdo->errorInfo()[2]
                );
            }

        } catch (PDOException $e) {
        throw new ApiException(
            500,
            0,
            "Error de base de datos en el servidor",
            "http://localhost",
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }

    private static function retrieveSearchAllergy($leche,$gluten,$query)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
            $Limit = "LIMIT 50";
            $LecheFiltro = "AND alergenos NOT LIKE '%leche%' AND trazas NOT LIKE '%leche%' AND alergenos AND alergenos NOT LIKE '%lactosa%' AND trazas NOT LIKE '%lactosa%'";
            $glutenFiltro = "AND alergenos NOT LIKE '%gluten%' AND trazas NOT LIKE '%gluten%'";
            if($leche == "1" and $gluten == "1"){
                $comando = "SELECT * FROM  alimentos WHERE nombreAlimento LIKE ? ".$LecheFiltro." ".$glutenFiltro."".$Limit;
                // Preparar sentencia
            }
            else if($leche == "1"){
                $comando = "SELECT * FROM  alimentos WHERE nombreAlimento LIKE ? ".$LecheFiltro." ".$Limit;
            }
            else if($gluten == "1"){
                $comando = "SELECT * FROM  alimentos WHERE nombreAlimento LIKE ? ".$glutenFiltro."".$Limit;
            }else{
                $comando = "SELECT * FROM alimentos WHERE nombreAlimento LIKE ? LIMIT 50";

            }
            $sentencia = $pdo->prepare($comando);
            $queryFinal = '%' . $query . '%';
            $sentencia->bindParam(1, $queryFinal);

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                return $sentencia->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new ApiException(
                    500,
                    0,
                    "Error de base de datos en el servidor",
                    "http://localhost",
                    "Hubo un error ejecutando una sentencia SQL en la base de datos. Detalles:" . $pdo->errorInfo()[2]
                );
            }

        } catch (PDOException $e) {
        throw new ApiException(
            500,
            0,
            "Error de base de datos en el servidor",
            "http://localhost",
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
}