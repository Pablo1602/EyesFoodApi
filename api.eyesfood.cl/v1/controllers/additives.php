<?php

/**
 * Controlador del endpoint /appointments
 */
class additives
{
    public static function get($urlSegments)
    {
        // TODO: 2. Verificaciones, restricciones, defensas
        //?????????????????????????????????????????????????
        if (isset($urlSegments[1])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        //Hacer switch case para encontrar la URL tipo foods/codigodeBarra/aditivos
        //barcode=urlSegments[0], aditivos e ingredientes=urlSegments[1]
        if (isset($urlSegments[0])) {
            return self::retrieveAdditive($urlSegments[0]);
        } else {
            return self::retrieveAdditives();
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
    
    private static function retrieveAdditive($codigoE)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT codigoE, aditivo, peligro_aditivo.gradoPeligro, origen_aditivo.origen, "
                    . "clasificacion_aditivo.clasificacion, descripcionAditivo, usoAditivo, "
                    . "efectosSecundariosAditivo "
                    . "FROM aditivos LEFT JOIN peligro_aditivo "
                    . "ON aditivos.idPeligroAditivo = peligro_aditivo.idPeligroAditivo"
                    . " LEFT JOIN origen_aditivo ON aditivos.idOrigenAditivo = origen_aditivo.idOrigenAditivo"
                    . " LEFT JOIN clasificacion_aditivo ON aditivos.idClasificacionAditivo = clasificacion_aditivo.idClasificacionAditivo"
                    . " WHERE codigoE = ?";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                $sentencia->bindParam(1, $codigoE, PDO::PARAM_INT);

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
    
    private static function retrieveAdditives()
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT codigoE, aditivo, peligro_aditivo.gradoPeligro, origen_aditivo.origen, "
                    . "clasificacion_aditivo.clasificacion, descripcionAditivo, usoAditivo, "
                    . "efectosSecundariosAditivo "
                    . "FROM aditivos LEFT JOIN peligro_aditivo "
                    . "ON aditivos.idPeligroAditivo = peligro_aditivo.idPeligroAditivo "
                    . "LEFT JOIN origen_aditivo ON aditivos.idOrigenAditivo = origen_aditivo.idOrigenAditivo "
                    . "LEFT JOIN clasificacion_aditivo ON aditivos.idClasificacionAditivo = clasificacion_aditivo.idClasificacionAditivo";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

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