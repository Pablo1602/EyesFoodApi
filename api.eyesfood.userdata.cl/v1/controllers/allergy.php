<?php
require_once 'data/MysqlManager.php';

/**
 * Controlador del endpoint /allergy
 */
class allergy
{
    //URL: /measures/idUsuario/medida
    //[0]: idUsuario
    //[1]: medida
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
        else {
            if(isset($urlSegments[0])){
                return self::getAllergyUser($urlSegments[0]);
            }
        }
    }

    public static function post($urlSegments)
    {
        //URL: /measures/medida para insertar /measures/medida/edit para editar
        if (isset($urlSegments[0])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else{
            return self::modifyAllergyUser();               
        }
    }
    
    private static function getAllergyUser($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT *"
                    . " FROM usuario_alergia"
                    . " WHERE idUsuario=?"
                    . " ORDER BY fecha DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);

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
            "Ocurrió el siguiente error al consultar las alergias: " . $e->getMessage());
        }
    }

    private static function modifyAllergyUser() {
        // Obtener parámetros de la petición
        $parameters = file_get_contents('php://input');
        $decodedParameters = json_decode($parameters, true);

        // Controlar posible error de parsing JSON
        if (json_last_error() != JSON_ERROR_NONE) {
            $internalServerError = new ApiException(
                500,
                0,
                "Error interno en el servidor. Contacte al administrador",
                "http://localhost",
                "Error de parsing JSON. Causa: " . json_last_error_msg());
            throw $internalServerError;
        }

        // Verificar integridad de datos
        // TODO: Implementar restricciones de datos adicionales

        // Insertar usuario
        $dbResult = self::modifyAllergy($decodedParameters, $idUsuario);

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 201, "message" => "Alimento registrado"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
 
    private static function modifyAllergy($decodedParameters) {
        //Extraer datos del usuario
        $leche = $decodedParameters["leche"];
        $gluten = $decodedParameters["gluten"];
        $idUsuario = $decodedParameters["idUsuario"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia UPDATE
            $sentence = "UPDATE usuario_alergia "
                    . "SET leche = ? AND gluten = ?"
                    . "WHERE idUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $leche);
            $preparedStament->bindParam(2, $gluten);
            $preparedStament->bindParam(3, $idUsuario);

            // Ejecutar sentencia
            return $preparedStament->execute();

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar actualizar alergias: " . $e->getMessage());
        }
    }
}