<?php
require_once 'data/MysqlManager.php';
/**
 * Controlador del endpoint /diary
 */
class diary
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
        else if (isset($urlSegments[0])) {
            switch ($urlSegments[0]) {
                case 'entrada':
                    if(isset($urlSegments[2])){
                        return self::retrieveEntryDate($urlSegments[1], $urlSegments[2]);
                    }
                    else{    
                        return self::retrieveEntry($urlSegments[1]);
                    }
                    break;
                default:
                    return self::retrievediary($urlSegments[0]);
                    break;
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
        //Hacer switch case para encontrar la URL tipo comments/respuesta/{id}
        // o URL tipo comments/{contexto}/{id}
        else if (isset($urlSegments[0])) {
            switch ($urlSegments[0]) {
                case 'entrada':
                     return self::newEntry($urlSegments[1]);
                    break;
                case 'borrar':
                    if($urlSegments[1] == "entrada"){
                        return self::deleteEntry($urlSegments[2]);
                    }
                    else{
                        return self::deleteDiary($urlSegments[1]);
                    }
                    break;
                case 'editar':
                    if($urlSegments[1] == "entrada"){
                        return self::editEntry($urlSegments[2]);
                    }
                    else{
                        return self::editDiary($urlSegments[1]);
                    }
                    break;
                default:
                    return self::newDiary($urlSegments[0]);
                    break;
            }
        }
    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }

    private static function retrievediary($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT *"
                        . " FROM diarios"
                        . " WHERE idUsuario = ? AND borrar = 0";
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $idUsuario);


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

    private static function retrieveEntry($idDiario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT *"
                        . " FROM entradas"
                        . " WHERE idDiario = ? AND borrar = 0";
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $idDiario);


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

    private static function retrieveEntryDate($idDiario, $fecha)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT *"
                        . " FROM entradas"
                        . " WHERE idDiario = ? AND fecha = ? AND borrar = 0";
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $idDiario);
                $sentencia->bindParam(2, $fecha);


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

    private static function newDiary($idUsuario) {
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
        $dbResult = self::insertNewDiary($decodedParameters, $idUsuario);

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 201, "message" => "Comentario registrado"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }

    private static function newEntry($idDiario) {
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
        $dbResult = self::insertNewEntry($decodedParameters, $idDiario);

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 201, "message" => "Comentario registrado"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }

    private static function insertNewDiary($decodedParameters, $idUsuario) {
        $titulo = $decodedParameters["titulo"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO diarios (idUsuario, titulo)" .
                " VALUES (?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $titulo);

            // Ejecutar sentencia
            return $preparedStament->execute();

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el usuario: " . $e->getMessage());
        }
    }

    private static function insertNewEntry($decodedParameters, $idDiario) {
        $titulo = $decodedParameters["titulo"];
        $texto = $decodedParameters["texto"];
        $fecha = $decodedParameters["fecha"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO entradas (idDiario, titulo, texto, fecha)" .
                " VALUES (?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idDiario);
            $preparedStament->bindParam(2, $titulo);
            $preparedStament->bindParam(3, $texto);
            $preparedStament->bindParam(4, $fecha);

            // Ejecutar sentencia
            return $preparedStament->execute();

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el usuario: " . $e->getMessage());
        }
    }

    private static function deleteDiary($idDiario){
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia UPDATE
            $sentence = "UPDATE diarios "
                    . "SET borrar = 1 "
                    . "WHERE idDiario = ?";

            // Preparar sentencia
            $preparedStatement = $pdo->prepare($sentence);
            
            $preparedStatement->bindParam(1, $idDiario);

            // Ejecutar sentencia
            if ($preparedStatement->execute()) {

                $rowCount = $preparedStatement->rowCount();
                $dbResult = self::findDiary($idDiario);

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

    private static function deleteEntry($idEntry){
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia UPDATE
            $sentence = "UPDATE entradas "
                    . "SET borrar = 1 "
                    . "WHERE idEntry = ?";

            // Preparar sentencia
            $preparedStatement = $pdo->prepare($sentence);
            
            $preparedStatement->bindParam(1, $idEntry);

            // Ejecutar sentencia
            if ($preparedStatement->execute()) {

                $rowCount = $preparedStatement->rowCount();
                $dbResult = self::findEntry($idEntry);

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

    private static function findDiary($idDiario) {    
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM diarios"
                        . " WHERE idDiario = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idDiario);

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

    private static function findEntry($idEntry) {    
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM entradas"
                        . " WHERE idEntry = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idEntry);

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

    public static function editDiary($idDiario){
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
        $titulo = $decodedParameters["titulo"];
        try {
            $pdo = MysqlManager::get()->getDb();

            // Verificar integridad de datos
            // TODO: Implementar restricciones de datos adicionales
            // Componer sentencia UPDATE
            $sentence = "UPDATE diarios "
                    . "SET titulo = ? "
                    . "WHERE idDiario = ?";

            // Preparar sentencia
            $preparedStatement = $pdo->prepare($sentence);
            $preparedStatement->bindParam(1, $titulo);
            $preparedStatement->bindParam(2, $idDiario);

            // Ejecutar sentencia
            if ($preparedStatement->execute()) {

                $rowCount = $preparedStatement->rowCount();
                $dbResult = self::findDiary($idDiario);

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
        public static function editEntry($idEntry){
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
        $titulo = $decodedParameters["titulo"];
        $texto = $decodedParameters["texto"];
        try {
            $pdo = MysqlManager::get()->getDb();

            // Verificar integridad de datos
            // TODO: Implementar restricciones de datos adicionales
            // Componer sentencia UPDATE
            $sentence = "UPDATE diarios "
                    . "SET titulo = ?, texto = ?, fecha = ? "
                    . "WHERE idDiario = ?";

            // Preparar sentencia
            $preparedStatement = $pdo->prepare($sentence);
            $preparedStatement->bindParam(1, $titulo);
            $preparedStatement->bindParam(2, $texto);
            $preparedStatement->bindParam(3, $idEntry);

            // Ejecutar sentencia
            if ($preparedStatement->execute()) {

                $rowCount = $preparedStatement->rowCount();
                $dbResult = self::findEntryd($idEntry);

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
}
