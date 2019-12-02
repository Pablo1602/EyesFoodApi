<?php

/**
 * Controlador del endpoint /foods
 */
class help
{
    public static function get($urlSegments)
    {
        // TODO: 2. Verificaciones, restricciones, defensas
        //?????????????????????????????????????????????????
        if (isset($urlSegments[0])) {
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
        else {
            return self::retrieveHelp();
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
    
    private static function retrieveHelp()
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT * "
                        . "FROM ayuda";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);

            // Ejecutar sentencia preparada, si pongo fetchAll muere el historial
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