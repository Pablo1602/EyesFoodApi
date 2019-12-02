<?php

/**
 * Controlador del endpoint /foods
 */
class measures
{
    //URL: /measures/idUsuario/medida
    //[0]: idUsuario
    //[1]: medida
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
        else {
            switch ($urlSegments[0]) {
                case "weight":
                    return self::retrieveWeight($urlSegments[1]);
                    break;
                
                case "fat":
                    return self::retrieveFat($urlSegments[1]);
                    break;
                
                case "waist":
                    return self::retrieveWaist($urlSegments[1]);
                    break;
                
                case "a1c":
                    return self::retrieveA1c($urlSegments[1]);
                    break;
                
                case "preglucose":
                    return self::retrievePreGlu($urlSegments[1]);
                    break;
                
                case "postglucose":
                    return self::retrievePostGlu($urlSegments[1]);
                    break;
                
                case "pressure":
                    return self::retrievePressure($urlSegments[1]);
                    break;
                
                default:
                    break;
            }
       }
    }

    public static function post($urlSegments)
    {
        //URL: /measure/medida para insertar /measure/medida/edit para editar
        if (isset($urlSegments[2])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else if(isset($urlSegments[1])){
            if($urlSegments[1] == "edit"){
                return self::editMeasure($urlSegments[0]);
            }
        }
        else {
            return self::saveNewMeasure($urlSegments[0]);
        }       
    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {
        //URL: /measure/medida/delete/idMedida
        if (isset($urlSegments[3])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else if(isset($urlSegments[2])){
            return self::deleteMeasure($urlSegments[0], $urlSegments[2]);
        }
    }
    
    private static function retrieveWeight($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idPesoUsuario AS idMedida, idUsuario, peso AS medida, fecha"
                    . " FROM usuario_peso"
                    . " WHERE idUsuario=?"
                    . " ORDER BY peso DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrieveFat($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idGrasaUsuario AS idMedida, idUsuario, grasa AS medida, fecha"
                    . " FROM usuario_grasa"
                    . " WHERE idUsuario=?"
                    . " ORDER BY grasa DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrieveWaist($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idCinturaUsuario AS idMedida, idUsuario, cintura AS medida, fecha"
                    . " FROM usuario_cintura"
                    . " WHERE idUsuario=?"
                    . " ORDER BY cintura DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrieveA1c($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idA1cUsuario AS idMedida, idUsuario, a1c AS medida, fecha"
                    . " FROM usuario_a1c"
                    . " WHERE idUsuario=?"
                    . " ORDER BY a1c DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrievePreGlu($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idGlucosapreUsuario AS idMedida, idUsuario, glucosapre AS medida, fecha"
                    . " FROM usuario_glucosapre"
                    . " WHERE idUsuario=?"
                    . " ORDER BY glucosapre DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrievePostGlu($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idGlucosapostUsuario AS idMedida, idUsuario, glucosapost AS medida, fecha"
                    . " FROM usuario_glucosapost"
                    . " WHERE idUsuario=?"
                    . " ORDER BY glucosapost DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function retrievePressure($idUsuario)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT idPresionUsuario AS idMedida, idUsuario, presion AS medida, fecha"
                    . " FROM usuario_presion"
                    . " WHERE idUsuario=?"
                    . " ORDER BY presion DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
            "Ocurrió el siguiente error al consultar las citas médicas: " . $e->getMessage());
        }
    }
    
    private static function saveNewMeasure($medida) {
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

        switch ($medida){
            case "weight":
                $dbResult = self::insertNewWeight($decodedParameters);
                break;
            case "fat":
                $dbResult = self::insertNewFat($decodedParameters);
                break;
            case "waist":
                $dbResult = self::insertNewWaist($decodedParameters);
                break;
            case "a1c":
                $dbResult = self::insertNewA1c($decodedParameters);
                break;
            case "preglucose":
                $dbResult = self::insertNewPreglucose($decodedParameters);
                break;
            case "postglucose":
                $dbResult = self::insertNewPostglucose($decodedParameters);
                break;
            case "pressure":
                $dbResult = self::insertNewPressure($decodedParameters);
                break;
        }

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 201, "message" => "Medida registrada"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
    
    private static function insertNewWeight($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $peso = $decodedParameters["peso"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_peso (idUsuario, peso, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $peso);

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
    
    private static function insertNewFat($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $grasa = $decodedParameters["grasa"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_grasa (idUsuario, grasa, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $grasa);

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
    
    private static function insertNewWaist($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $cintura = $decodedParameters["cintura"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_cintura (idUsuario, cintura, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $cintura);

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
    
    private static function insertNewA1c($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $a1c = $decodedParameters["a1c"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_a1c (idUsuario, a1c, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, a1c);

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
    
    private static function insertNewPreglucose($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $preglucosa = $decodedParameters["preglucosa"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_glucosapre (idUsuario, glucosapre, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $preglucosa);

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
    
    private static function insertNewPostglucose($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $glucosapost = $decodedParameters["glucosapost"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_glucosapost (idUsuario, glucosapost, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $glucosapost);

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
    
    private static function insertNewPressure($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $presion = $decodedParameters["pressure"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_presion (idUsuario, presion, fecha)" .
                " VALUES (?,?,CURRENT_TIME)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $presion);

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
    
    private static function editMeasure($medida) {
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

        switch ($medida){
            case "weight":
                $dbResult = self::editWeight($decodedParameters);
                break;
            case "fat":
                $dbResult = self::editFat($decodedParameters);
                break;
            case "waist":
                $dbResult = self::editWaist($decodedParameters);
                break;
            case "a1c":
                $dbResult = self::editA1c($decodedParameters);
                break;
            case "preglucose":
                $dbResult = self::editPreglucose($decodedParameters);
                break;
            case "postglucose":
                $dbResult = self::editPostglucose($decodedParameters);
                break;
            case "pressure":
                $dbResult = self::editPressure($decodedParameters);
                break;
            case "height":
                $dbResult = self::editHeight($decodedParameters);
                break;
        }

        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 200, "message" => "Medida editada"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
    
    private static function editWeight($decodedParameters) {
        //Extraer datos del usuario
        $idPesoUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $peso = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_peso"
                    . " SET peso = ?" .
                " WHERE idUsuario = ? AND idPesoUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $peso);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idPesoUsuario);

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
    
    private static function editFat($decodedParameters) {
        //Extraer datos del usuario
        $idGrasaUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $grasa = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_grasa"
                    . " SET peso = ?" .
                " WHERE idUsuario = ? AND idGrasaUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $grasa);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idGrasaUsuario);

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
    
    private static function editWaist($decodedParameters) {
        //Extraer datos del usuario
        $idCinturaUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $cintura = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_cintura"
                    . " SET cintura = ?" .
                " WHERE idUsuario = ? AND idCinturaUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $cintura);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idCinturaUsuario);

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
    
    private static function editA1c($decodedParameters) {
        //Extraer datos del usuario
        $idA1cUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $a1c = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_a1c"
                    . " SET a1c = ?" .
                " WHERE idUsuario = ? AND idA1cUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $a1c);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idA1cUsuario);

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
    
    private static function editPreglucose($decodedParameters) {
        //Extraer datos del usuario
        $idGlucosapreUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $glucosapre = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_glucosapre"
                    . " SET glucosapre = ?" .
                " WHERE idUsuario = ? AND idGlucosapreUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $glucosapre);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idGlucosapreUsuario);

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
    
    private static function editPostglucose($decodedParameters) {
        //Extraer datos del usuario
        $idGlucosapostUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $glucosapost = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_glucosapost"
                    . " SET glucosapost = ?" .
                " WHERE idUsuario = ? AND idGlucosapostUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $glucosapost);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idGlucosapostUsuario);

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
    
    private static function editPressure($decodedParameters) {
        //Extraer datos del usuario
        $idPresionUsuario = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $presion = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuario_presion"
                    . " SET presion = ?" .
                " WHERE idUsuario = ? AND idPresionUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $presion);
            $preparedStament->bindParam(2, $idUsuario);
            $preparedStament->bindParam(3, $idPresionUsuario);

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
    private static function editHeight($decodedParameters) {
        //Extraer datos del usuario
        $idMedida = $decodedParameters["idMedida"];
        $idUsuario = $decodedParameters["idUsuario"];
        $estatura = $decodedParameters["medida"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "UPDATE usuarios"
                    . " SET Estatura = ?" .
                " WHERE idUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $estatura);
            $preparedStament->bindParam(2, $idUsuario);

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
    
    private static function deleteMeasure($medida, $idMedida) {
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

        switch ($medida){
            case "weight":
                $dbResult = self::deleteWeight($idMedida);
                break;
            case "fat":
                $dbResult = self::deleteFat($idMedida);
                break;
            case "waist":
                $dbResult = self::deleteWaist($idMedida);
                break;
            case "a1c":
                $dbResult = self::deleteA1c($idMedida);
                break;
            case "preglucose":
                $dbResult = self::deletePreglucose($idMedida);
                break;
            case "postglucose":
                $dbResult = self::deletePostglucose($idMedida);
                break;
            case "pressure":
                $dbResult = self::deletePressure($idMedida);
                break;
        }
        
        // Procesar resultado de la inserción
        if ($dbResult) {
            return ["status" => 200, "message" => "Medida eliminada"];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
    
    private static function deleteWeight($idMedida) {

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_peso".
                " WHERE idPesoUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
    
    private static function deleteFat($idMedida) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_grasa".
                " WHERE idGrasaUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
    
    private static function deleteWaist($idMedida) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_cintura".
                " WHERE idCinturaUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
    
    private static function deleteA1c($idMedida) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_a1c".
                " WHERE idA1cUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
    
    private static function deletePreglucose($idMedida) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_glucosapre".
                " WHERE idGlucosapreUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
    
    private static function deletePostglucose($idMedida) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_glucosapost".
                " WHERE idGlucosapostUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
    
    private static function deletePressure($idMedida) {try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "DELETE FROM usuario_presion".
                " WHERE idPresionUsuario = ?";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idMedida);

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
}