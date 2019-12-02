<?php

/**
 * Controlador del endpoint /appointments
 */
class history
{
    public static function get($urlSegments)
    {
        // TODO: 2. Verificaciones, restricciones, defensas
        //?????????????????????????????????????????????????
        if (isset($urlSegments[2])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        //history/userId/idAlimento [0]=userId, [1]=idAlimento
        
        if(isset($urlSegments[1])){
            
            if($urlSegments[1] == "likes"){
                return self::retrieveHistoryLikes($urlSegments[0]);
            }
            else if($urlSegments[1] == "dislikes"){
                return self::retrieveHistoryDislikes($urlSegments[0]);
            }
            else if($urlSegments[1] == "uploads"){
                return self::retrieveHistoryUploads($urlSegments[0]);
            }
            else if($urlSegments[1] == "favorites"){
                return self::retrieveHistoryFavorites($urlSegments[0]);
            }
            else if($urlSegments[1] == "rejected"){
                return self::retrieveHistoryRejected($urlSegments[0]);
            }
            else{
                //Retornar el alimento pedido, sirve para ver si lo tiene o no
                return self::retrieveHistoryFoods($urlSegments[0], $urlSegments[1]);
            }           
        }
        else if (isset($urlSegments[0])){
            //Retornar todos los alimentos del historial
            return self::retrieveHistoryFoodsUser($urlSegments[0]);
            
        }
    }

    public static function post($urlSegments) {

        //Si se manda algo mas que la url
        /*if (isset($urlSegments[0])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }*/
        // Modificar cita médica en la base de datos local
        if(isset($urlSegments[2])){
            if($urlSegments[2] == "scan"){
                $result = self::modifyHistoryScan($urlSegments[0], $urlSegments[1]);
            }
            else{
                $result = self::modifyHistoryLike($urlSegments[0], $urlSegments[1], $urlSegments[2]);
            }
        }
        else if(isset($urlSegments[1])){
            $result = self::modifyHistory($urlSegments[0], $urlSegments[1]);
        }
        else{
            if($urlSegments[0] == "noscan"){
                return self::saveFoodNoScan();
            }
            else{
                return self::saveFood();
            }
        }
    }

    public static function put($urlSegments)
    {
        
    }
    
    public static function patch($urlSegments)
    {
        // Extraer id del alimento
        if (!isset($urlSegments[1]) || empty($urlSegments[1])) {
            throw new ApiException(
                400,
                0,
                "Se requiere id del alimento",
                "http://localhost",
                "La URL debe tener la forma /history/userId/:foodId para aplicar el método PATCH"
            );
        }
        
        // Verificar anomalías de la URL
        if (isset($urlSegments[3])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "La URL no es de la forma /Userid/barcode"
            );
        }

        // Extraer cuerpo de la petición
        $body = file_get_contents("php://input");

        $content_type = '';

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        }
        switch ($content_type) {
            case "application/json":
                $body_params = json_decode($body);
                if ($body_params) {
                    foreach ($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
                    }
                }
                break;
            default:
                throw new ApiException(
                    400,
                    0,
                    "Formato de los datos no soportado",
                    "http://localhost",
                    "El cuerpo de la petición no usa el tipo application/json"
                );
        }
        
        // Modificar cita médica en la base de datos local
        if(isset($urlSegments[2])){
            $result = self::modifyHistoryLike($urlSegments[0], $urlSegments[1], $urlSegments[2]);
        }
        else{
            $result = self::modifyHistory($urlSegments[0], $urlSegments[1]);
        }

        // Retornar mensaje de modificación
        if ($result > 0) {
            return $result;
        } else {
            throw new ApiException(
                409,
                0,
                "Hubo un conflicto al intentar modificar la cita",
                "http://localhost",
                "La modificación no afecta ninguna fila"
            );
        }
    }

    public static function delete($urlSegments)
    {

    }
    
private static function retrieveHistoryFoodsUser($userId)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
            $comando = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras, alimentos.nombreAlimento,"
                    . " alimentos.idPeligroAlimento, alimentos.peligroAlimento, fechaEscaneo,"
                    . " meGusta"
                    . " FROM historial_escaneo"
                    . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                    . " WHERE historial_escaneo.idUsuario = ? AND (historial_escaneo.escaneo = '1' OR historial_escaneo.escaneo = '2')"
                    . " ORDER BY fechaEscaneo DESC";

            // Preparar sentencia
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $userId, PDO::PARAM_INT);
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

    private static function retrieveHistoryFoods($userId, $codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
            $comando = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras, alimentos.nombre,"
                    . " alimentos.idPeligroAlimento, alimentos.peligroAlimento, fechaEscaneo, alimentos.fotoOficial,"
                    . " meGusta, escaneo"
                    . " FROM historial_escaneo"
                    . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                    . " WHERE historial_escaneo.idUsuario = ? AND historial_escaneo.codigoBarras = ?";

            // Preparar sentencia
            $sentencia = $pdo->prepare($comando);
            // Ligar idContacto e idUsuario
            $sentencia->bindParam(1, $userId, PDO::PARAM_INT);
            $sentencia->bindParam(2, $codigoBarras, PDO::PARAM_INT);
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
    
    private static function saveFood() {
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
        if (!isset($decodedParameters["idUsuario"]) ||
            !isset($decodedParameters["codigoBarras"])/*||
            !isset($decodedParameters["fechanacimiento"]) ||
            !isset($decodedParameters["sexo"]) ||
            !isset($decodedParameters["estatura"]) ||
            !isset($decodedParameters["nacionalidad"])*/
        ) {
            // TODO: Crear una excepción individual por cada causa anómala
            throw new ApiException(
                400,
                0,
                "Verifique los datos del usuario tengan formato correcto",
                "http://localhost",
                "Uno de los atributos del usuario no está definido en los parámetros");
        }

        // Insertar usuario
        $dbResult = self::insertFood($decodedParameters);

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 201, "message" => "Usuario registrado"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
    
    private static function insertFood($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $codigoBarras = $decodedParameters["codigoBarras"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO historial_escaneo (idUsuario, codigoBarras, fechaEscaneo)" .
                " VALUES (?,?, CURRENT_TIMESTAMP)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $codigoBarras);

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
    
    private static function modifyHistory($userId, $barcode)
{
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE historial_escaneo "
                . "SET fechaEscaneo=CURRENT_TIMESTAMP "
                . "WHERE idUsuario=? AND codigoBarras = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $userId, PDO::PARAM_INT);
        $preparedStatement->bindParam(2, $barcode, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findShortFood($userId, $barcode);

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

private static function modifyHistoryLike($userId, $barcode, $like)
{
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE historial_escaneo "
                . "SET meGusta=? "
                . "WHERE idUsuario=? AND codigoBarras = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $like, PDO::PARAM_INT);
        $preparedStatement->bindParam(2, $userId, PDO::PARAM_INT);
        $preparedStatement->bindParam(3, $barcode, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findShortFood($userId, $barcode);

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

private static function findShortFood($idUsuario, $codigoBarras) {
        
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras, alimentos.nombre,"
                        . " alimentos.peligroAlimento, fechaEscaneo, alimentos.fotoOficial,"
                        . " meGusta"
                        . " FROM historial_escaneo"
                        . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                        . " WHERE historial_escaneo.idUsuario = ? AND historial_escaneo.codigoBarras = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idUsuario, PDO::PARAM_INT);
            $preparedSentence->bindParam(2, $codigoBarras, PDO::PARAM_INT);

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
    
    private static function retrieveHistoryLikes($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT COUNT(*) AS COUNT"
                    . " FROM historial_escaneo"
                    . " WHERE codigoBarras=? AND meGusta='1'";

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
    
    private static function retrieveHistoryDislikes($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT COUNT(*) AS COUNT"
                    . " FROM historial_escaneo"
                    . " WHERE codigoBarras=? AND meGusta='2'";

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
    
    
    private static function saveFoodNoScan() {
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
        if (!isset($decodedParameters["idUsuario"]) ||
            !isset($decodedParameters["codigoBarras"]) ||
            !isset($decodedParameters["meGusta"])
        ) {
            // TODO: Crear una excepción individual por cada causa anómala
            throw new ApiException(
                400,
                0,
                "Verifique los datos del usuario tengan formato correcto",
                "http://localhost",
                "Uno de los atributos del usuario no está definido en los parámetros");
        }

        // Insertar usuario
        $dbResult = self::insertFoodNoScan($decodedParameters);

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 201, "message" => "Usuario registrados"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
    
    private static function insertFoodNoScan($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $codigoBarras = $decodedParameters["codigoBarras"];
        $like = $decodedParameters["meGusta"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO historial_escaneo (idUsuario, codigoBarras, escaneo, meGusta, fechaEscaneo)" .
                " VALUES (?,?,'0',?,CURRENT_TIMESTAMP)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $codigoBarras);
            $preparedStament->bindParam(3, $like);

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
    
    private static function modifyHistoryScan($userId, $barcode)
{
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE historial_escaneo "
                . "SET escaneo = '1' "
                . "WHERE idUsuario=? AND codigoBarras = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        $preparedStatement->bindParam(1, $userId, PDO::PARAM_INT);
        $preparedStatement->bindParam(2, $barcode, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findShortFood($userId, $barcode);

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

private static function retrieveHistoryUploads($userId)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

                $comando = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras, alimentos.nombre,"
                        . " alimentos.idPeligroAlimento, alimentos.peligroAlimento, fechaEscaneo, alimentos.fotoOficial,"
                        . " meGusta"
                        . " FROM historial_escaneo"
                        . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                        . " WHERE historial_escaneo.idUsuario = ? AND historial_escaneo.escaneo = '2'"
                        . " ORDER BY fechaEscaneo DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $userId, PDO::PARAM_INT);
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
    
    private static function retrieveHistoryFavorites($userId)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

                $comando = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras, alimentos.nombre,"
                        . " alimentos.idPeligroAlimento, alimentos.peligroAlimento, fechaEscaneo, alimentos.fotoOficial,"
                        . " meGusta"
                        . " FROM historial_escaneo"
                        . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                        . " WHERE historial_escaneo.idUsuario = ? AND historial_escaneo.meGusta = '1'"
                        . " ORDER BY fechaEscaneo DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $userId, PDO::PARAM_INT);
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
    
    private static function retrieveHistoryRejected($userId)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

                $comando = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras, alimentos.nombre,"
                        . " alimentos.idPeligroAlimento, alimentos.peligroAlimento, fechaEscaneo, alimentos.fotoOficial,"
                        . " meGusta"
                        . " FROM historial_escaneo"
                        . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                        . " WHERE historial_escaneo.idUsuario = ? AND historial_escaneo.meGusta = '2'"
                        . " ORDER BY fechaEscaneo DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $userId, PDO::PARAM_INT);
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