<?php

/**
 * Controlador del endpoint /stores
 */
class stores
{
    public static function get($urlSegments)
    {
        // TODO: 2. Verificaciones, restricciones, defensas
        //?????????????????????????????????????????????????
        if (isset($urlSegments[1])){
            return self::retrieveStore($urlSegments[1]);
        }else{
            if (isset($urlSegments[0])) {
               return self::retrieveProducts($urlSegments[0]);
            }else{
                return self::retrieveStores();
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
    
    private static function retrieveStores()
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT * "
                        . "FROM tiendas ";

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
    
    private static function retrieveProducts($id)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT alimento_tienda.codigoBarras, precio, alimentos.nombreAlimento, alimentos.peligroAlimento "
                        . "FROM alimento_tienda LEFT JOIN alimentos ON alimento_tienda.codigoBarras = alimentos.codigoBarras "
                        . "WHERE alimento_tienda.idTienda = ? ORDER BY fecha DESC";
                        
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $id, PDO::PARAM_INT);

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
    
    private static function retrieveStore($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT tiendas.nombre, tiendas.email, tiendas.direccion, tiendas.descripcion ,tiendas.paginaWeb, tiendas.telefono, tiendas.foto, alimento_tienda.idTienda "
                        ."FROM alimento_tienda LEFT JOIN tiendas ON alimento_tienda.idTienda = tiendas.idTienda " 
                        ."WHERE alimento_tienda.codigoBarras = ? ORDER BY fecha DESC";
                        
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $codigoBarras, PDO::PARAM_INT);

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