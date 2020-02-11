<?php

/**
 * Controlador del endpoint /ratings
 */
class ratings
{
    public static function get($urlSegments)
    {
        
        if (isset($urlSegments[3])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        
        else{
            // $urlSegments[0] = idExperto     $urlSegments[1] = idUsuario
            if (isset($urlSegments[1])){
                return self::findRating($urlSegments[0], $urlSegments[1]);
            }
            // url: /ratings/idExperto
            else if (isset($urlSegments[0])) {
                return self::retrieveRatings($urlSegments[0]);
            }
        }
    }

    public static function post($urlSegments)
    {
        //Si se manda algo mas que la url
        if (isset($urlSegments[3])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else{
            if(isset($urlSegments[2])){            
                return self::modifyRating($urlSegments[0], $urlSegments[1], $urlSegments[2]);
            }
            else if (isset($urlSegments[0])){
                return self::saveNewRating();
            }
            
        }        
    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }
    
    private static function retrieveRatings($idExperto){
        try {
            $pdo = MysqlManager::get()->getDb();
            
            // $userId = NULL
                $comando = "SELECT *"
                        . " FROM experto_valorar" 
                        . " WHERE idExperto = ?";

                    // Preparar sentencia
                    $sentencia = $pdo->prepare($comando);
                    //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                    $sentencia->bindParam(1, $idExperto, PDO::PARAM_INT);

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
            }

        catch (PDOException $e) {
        throw new ApiException(
            500,
            0,
            "Error de base de datos en el servidor",
            "http://localhost",
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }


    private static function saveNewRating() {
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
        $dbResult = self::insertNewRating($decodedParameters);

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

        private static function insertNewRating($decodedParameters) {
        //Extraer datos del rating
        $idUsuario = $decodedParameters["idUsuario"];
        $idExperto = $decodedParameters["idExperto"];
        $valoracion = $decodedParameters["valoracion"];
        


        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO experto_valorar (idUsuario, idExperto, valoracion)".
                " VALUES (?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $idExperto);
            $preparedStament->bindParam(3, $valoracion);


            // Ejecutar sentencia
            return $preparedStament->execute();

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el Rating: " . $e->getMessage());
        }
    }
private static function modifyRating($idExperto, $userId, $reputacion)
{
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE experto_valorar "
                . "SET valoracion = ? "
                . "WHERE idExperto = ? AND idUsuario = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $reputacion, PDO::PARAM_INT);
        $preparedStatement->bindParam(2, $idExperto, PDO::PARAM_INT);
        $preparedStatement->bindParam(3, $userId, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findRating($idExperto, $userId);

        // Procesar resultado de la consulta
        // El de la derecha es la columna de la base de datos, case sensitive
        if ($dbResult != NULL) {
            return $dbResult;
        } else {
            throw new ApiException(
                400,
                0,
                "Número de identificación o contraseña inválidos",
                "http://localhost",
                "Puede que no exista un usuario creado con el correo \"$userId\" o que la contraseña \"$password\" sea incorrecta."
            );
        }
            }

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el usuario: " . $e->getMessage());
        }
    }
private static function findRating($idExperto, $userId) {
        
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM experto_valorar"
                        . " WHERE idExperto = ? AND idUsuario = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idExperto, PDO::PARAM_INT);
            $preparedSentence->bindParam(2, $userId, PDO::PARAM_INT);

            // Ejecutar sentencia
            if ($preparedSentence->execute()) {
                return $preparedSentence->fetch(PDO::FETCH_ASSOC);
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
                "Ocurrió el siguiente error al consultar el usuario: " . $e->getMessage());
        }
    }
}