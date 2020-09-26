<?php
require_once 'data/MysqlManager.php';
/**
 * Controlador del endpoint /comments
 */
class comments
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
        //Hacer switch case para encontrar la URL tipo comments/respuesta/{id}
        // o URL tipo comments/{contexto}/{id}
        else if (isset($urlSegments[2])) {
            switch ($urlSegments[0]) {
                case 'web':
                    return self::retrieveAllCommentsWeb($urlSegments[1], $urlSegments[2]);
                    break;
            }
        }
        else if (isset($urlSegments[1])) {
            switch ($urlSegments[0]) {
                case 'respuesta':
                     return self::retrieveResponses($urlSegments[1]);
                    break;
                case 'countHistory':
                    return self::retrieveHistoryComments($urlSegments[1]);
                    break;
                default:
                    //$urlSegments[1] = barcode
                    //$urlSegments[0] = 1 (alimentos) 
                    return self::retrieveAllComments($urlSegments[0], $urlSegments[1]);
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
        else if (isset($urlSegments[1])) {
            switch ($urlSegments[0]) {
                case 'respuesta':
                     return self::saveNewResponse($urlSegments[1]);
                    break;
                case 'borrar':
                    if($urlSegments[1] == "respuesta"){
                        return self::deleteResponse($urlSegments[2]);
                    }
                    else{
                        return self::deleteComment($urlSegments[1]);
                    }
                    break;
                case 'editar':
                    if($urlSegments[1] == "respuesta"){
                        return self::editResponse($urlSegments[2]);
                    }
                    else{
                        return self::editComment($urlSegments[1]);
                    }
                    break;
                default:
                    //$urlSegments[0] = 1 (alimentos) 
                    //$urlSegments[1] = barcode
                    return self::saveNewComment($urlSegments[0], $urlSegments[1]);
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
    
    private static function retrieveAllComments($idContextos, $referencia)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT * "
                        . "FROM comentarios "
                        . "WHERE idContextos = ? AND referencia = ? AND borrar = 0 ORDER BY fecha DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $idContextos, PDO::PARAM_INT);
                $sentencia->bindParam(2, $referencia, PDO::PARAM_INT);

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

    private static function retrieveAllCommentsWeb($idContextos, $referencia)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT * "
                        . "FROM comentarios "
                        . "WHERE idContextos = ? AND referencia = ? ORDER BY fecha DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $idContextos, PDO::PARAM_INT);
                $sentencia->bindParam(2, $referencia, PDO::PARAM_INT);

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
    
    private static function retrieveResponses($idComentario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT *"
                        . " FROM respuesta"
                        . " WHERE idComentario = ? AND borrar = 0 ORDER BY fecha DESC";
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                // Ligar idContacto e idUsuario
                $sentencia->bindParam(1, $idComentario, PDO::PARAM_INT);


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

    private static function saveNewComment($idContextos, $referencia) {
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
        $dbResult = self::insertNewComment($decodedParameters, $idContextos, $referencia);

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
    
    private static function insertNewComment($decodedParameters, $idContextos, $referencia) {
        //Extraer datos del usuario
        $idColaborador = $decodedParameters["idColaborador"];
        $colaborador = $decodedParameters["colaborador"];
        $comentario = $decodedParameters["comentario"];
        $fecha = $decodedParameters["fecha"];


        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO comentarios (idColaborador, colaborador, comentario, fecha, idContextos, referencia)" .
                " VALUES (?,?,?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idColaborador);
            $preparedStament->bindParam(2, $colaborador);
            $preparedStament->bindParam(3, $comentario);
            $preparedStament->bindParam(4, $fecha);
            $preparedStament->bindParam(5, $idContextos);
            $preparedStament->bindParam(6, $referencia);

            // Ejecutar sentencia
            return $preparedStament->execute();

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el comentario: " . $e->getMessage());
        }
    }
    private static function saveNewResponse($idComentario) {
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
        $dbResult = self::insertNewResponse($decodedParameters, $idComentario);

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
    
    private static function insertNewResponse($decodedParameters, $idComentario) {
        //Extraer datos del usuario
        $idColaborador = $decodedParameters["idColaborador"];
        $colaborador = $decodedParameters["colaborador"];
        $comentario = $decodedParameters["comentario"];
        $fecha = $decodedParameters["fecha"];


        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            //$sentence = "INSERT INTO respuesta (idColaborador, colaborador, ,comentario, fecha, idContextos, referencia) VALUES (?,?,?,?,?,?)";
            $sentence = "INSERT INTO respuesta (idColaborador, colaborador, idComentario, comentario, fecha) VALUES (?,?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idColaborador);
            $preparedStament->bindParam(2, $colaborador);
            $preparedStament->bindParam(3, $idComentario);
            $preparedStament->bindParam(4, $comentario);
            $preparedStament->bindParam(5, $fecha);
            //$preparedStament->bindParam(5, $idContextos);
            //$preparedStament->bindParam(6, $referencia);
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

    private static function deleteComment($idComentario){
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE comentarios "
                . "SET borrar = 1 "
                . "WHERE idComentario = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $idComentario, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findComment($idComentario);

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

    private static function findComment($idComentario) {    
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM comentarios"
                        . " WHERE idComentario = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idComentario, PDO::PARAM_INT);

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

    private static function findResponse($idRespuesta) {    
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM respuesta"
                        . " WHERE idRespuesta = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idRespuesta, PDO::PARAM_INT);

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

    private static function deleteResponse($idRespuesta){
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE respuesta "
                . "SET borrar = 1 "
                . "WHERE idRespuesta = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $idRespuesta, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findResponse($idRespuesta);

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

    private static function retrieveHistoryComments($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT COUNT(*) AS COUNT"
                    . " FROM comentarios"
                    . " WHERE referencia = ?";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                // Ligar idContacto e idUsuario
                $sentencia->bindParam(1, $codigoBarras, PDO::PARAM_INT);
                // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                return $sentencia->fetch(PDO::FETCH_ASSOC);
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

    public static function editComment($idComentario){
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
    $comentario = $decodedParameters["comentario"];
    try {
        $pdo = MysqlManager::get()->getDb();

        // Verificar integridad de datos
        // TODO: Implementar restricciones de datos adicionales
        // Componer sentencia UPDATE
        $sentence = "UPDATE comentarios "
                . "SET comentario = ? "
                . "WHERE idComentario = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        $preparedStatement->bindParam(1, $comentario);
        $preparedStatement->bindParam(2, $idComentario, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findComment($idComentario);

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
    
    public static function editResponse($idRespuesta){
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
    $comentario = $decodedParameters["comentario"];
    try {
        $pdo = MysqlManager::get()->getDb();

        // Verificar integridad de datos
        // TODO: Implementar restricciones de datos adicionales
        // Componer sentencia UPDATE
        $sentence = "UPDATE respuesta "
                . "SET comentario = ? "
                . "WHERE idRespuesta = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        $preparedStatement->bindParam(1, $comentario);
        $preparedStatement->bindParam(2, $idRespuesta, PDO::PARAM_INT);
        
        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findResponse($idRespuesta);

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