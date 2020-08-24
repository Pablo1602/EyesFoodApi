<?php

/**
 * Controlador del endpoint /foods
 */
class foods
{
    public static function get($urlSegments)
    {
        // TODO: 2. Verificaciones, restricciones, defensas
        //?????????????????????????????????????????????????
        if (isset($urlSegments[3])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        //barcode=urlSegments[0],
        else{
            if (isset($urlSegments[1])) {
                switch ($urlSegments[1]) {
                    case "recommendations":
                        return self::retrieveRecommendations($urlSegments[0]);
                        break;
//            Subida aceptada = 1
//            Subida pendiente = 2
//            Subida rechazada = 3
                    case "create":
                        return self::retrieveFoodsCreate($urlSegments[0]);
                        break;
                    case "new":
                        return self::retrieveFoodsStatement($urlSegments[0], 2);
                        break;
                    case "rejected":
                        return self::retrieveFoodsStatement($urlSegments[0], 3);
                        break;
                    //i = 1 aceptada
                    //i = 2 pendiente
                    //i = 3 rechazado
                    case "complaint":
                        return self::retrieveFoodsComplaint($urlSegments[0], 1);
                        break;
                    case "complaintp":
                        return self::retrieveFoodsComplaint($urlSegments[0], 2);
                        break;
                    case "complaintr":
                        return self::retrieveFoodsComplaint($urlSegments[0], 3);
                        break;
                    case "edits":
                        return self::retrieveFoodsEdits($urlSegments[0]);
                        break;
                    default:
                        throw new ApiException(
                        404,
                        0,
                        "El recurso al que intentas acceder no existe",
                        "http://localhost", "No se encontró el segmento \"Users/$urlSegments[0]\".");
                }
            } else if (isset($urlSegments[0])) {
                if($urlSegments[0] == "new"){
                    return self::retrieveNewFoods(); 
                }
                else if($urlSegments[0] == "complaint"){
                    return self::retrieveComplaintFoods();
                }else if($urlSegments[0] == "create"){
                    return self::retrieveFoods();
                }
                else{
                    return self::retrieveFoods($urlSegments[0]);
                }
            } else {
                return self::retrieveFoods();
            }
        }
    }

    public static function post($urlSegments)
    {
        //Si se manda algo mas que la url
        if (isset($urlSegments[2])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else{
            if (isset($urlSegments[1])) {
                if ($urlSegments[1]=="report") {
                    $result = self::modifyReport($urlSegments[0]);
                }else{
                    throw new ApiException(
                        400,
                        0,
                        "El recurso está mal referenciado",
                        "http://localhost",
                        "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
                    );
                }
            }else{
                switch ($urlSegments[0]){
                    case "complaint":
                        return self::saveNewComplaint();
                        break;
                    case "new":
                        return self::saveNewFood();
                        break;
                    case "create":
                        return self::saveFood();
                        break;
                }
            }
            
            
        }
        if (isset($urlSegments[1])) {
            
                
            
        }

    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }
    
    private static function retrieveFoods($codigoBarras = NULL)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            if (!$codigoBarras) {
                $comando = "SELECT * FROM alimentos ";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            } else {
                $comando = "SELECT codigoBarras,idUsuario, "
                        . "idPeligroAlimento, peligroAlimento,"
                        . "fechaSubida, indiceGlicemico, nombreAlimento FROM alimentos "
                        . "WHERE codigoBarras =?";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                // Ligar idContacto e idUsuario
                $sentencia->bindParam(1, $codigoBarras, PDO::PARAM_INT);
            }

            // Ejecutar sentencia preparada, si pongo fetchAll muere el historial
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
    
    private static function retrieveRecommendations($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();

            $comando = "SELECT recomendaciones.recomendacion FROM alimento_recomendacion"
                    . " LEFT JOIN recomendaciones ON alimento_recomendacion.idRecomendacion = recomendaciones.idRecomendacion"
                    . " WHERE codigoBarras = ?";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                $sentencia->bindParam(1, $codigoBarras, PDO::PARAM_INT);

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
    
    private static function retrieveAdditivesFull($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();            

            $comando = "SELECT alimento_aditivo.codigoE, aditivos.aditivo, peligro_aditivo.gradoPeligro, aditivos.idPeligroAditivo, "
                    . " origen_aditivo.origen, clasificacion_aditivo.clasificacion, aditivos.descripcionAditivo,"
                    . " aditivos.usoAditivo, aditivos.efectosSecundariosAditivo"
                    . " FROM alimento_aditivo"
                    . " LEFT JOIN aditivos ON alimento_aditivo.codigoE = aditivos.codigoE"
                    . " LEFT JOIN peligro_aditivo"
                    . " ON aditivos.idPeligroAditivo = peligro_aditivo.idPeligroAditivo"
                    . " LEFT JOIN origen_aditivo"
                    . " ON aditivos.idOrigenAditivo = origen_aditivo.idOrigenAditivo"
                    . " LEFT JOIN clasificacion_aditivo"
                    . " ON aditivos.idClasificacionAditivo = clasificacion_aditivo.idClasificacionAditivo"
                    . " WHERE codigoBarras = ? ORDER BY aditivos.idPeligroAditivo";
                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                $sentencia->bindParam(1, $codigoBarras, PDO::PARAM_INT);
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
    
    private static function saveNewFood() {
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
        $dbResult = self::insertNewFood($decodedParameters);

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

        // Insertar usuario
        $dbResult = self::insertFood($decodedParameters);

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
    
    private static function insertNewFood($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $codigoBarras = $decodedParameters["codigoBarras"];
        $nombre = $decodedParameters["nombre"];
        $producto = $decodedParameters["producto"];
        $marca = $decodedParameters["marca"];
        $contenidoNeto = $decodedParameters["contenidoNeto"];
        $porcion = $decodedParameters["porcion"];
        $porcionGramos = $decodedParameters["porcionGramos"];
        $energia = $decodedParameters["energia"];
        $proteinas = $decodedParameters["proteinas"];
        $grasaTotal = $decodedParameters["grasaTotal"];
        $grasaSaturada = $decodedParameters["grasaSaturada"];
        $grasaMono = $decodedParameters["grasaMono"];
        $grasaPoli = $decodedParameters["grasaPoli"];
        $grasaTrans = $decodedParameters["grasaTrans"];
        $colesterol = $decodedParameters["colesterol"];
        $hidratosCarbono = $decodedParameters["hidratosCarbono"];
        $azucaresTotales = $decodedParameters["azucaresTotales"];
        $fibra = $decodedParameters["fibra"];
        $sodio = $decodedParameters["sodio"];
        $ingredientes = $decodedParameters["ingredientes"];       
        $estadoAlimento = $decodedParameters["estadoAlimento"];
        $alergenos = $decodedParameters["alergenos"];
        $trazas = $decodedParameters["trazas"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO alimento_nuevo (idUsuario, codigoBarras, nombre, producto, marca, "
                    . "contenidoNeto, porcion, porcionGramos, energia, proteinas, grasaTotal, grasaSaturada, "
                    . "grasaMono, grasaPoli, grasaTrans, colesterol, hidratosCarbono, azucaresTotales, "
                    . "fibra, sodio, ingredientes, estadoAlimento, alergenos, trazas)" .
                " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $codigoBarras);
            $preparedStament->bindParam(3, $nombre);
            $preparedStament->bindParam(4, $producto);
            $preparedStament->bindParam(5, $marca);
            $preparedStament->bindParam(6, $contenidoNeto);
            $preparedStament->bindParam(7, $porcion);
            $preparedStament->bindParam(8, $porcionGramos);
            $preparedStament->bindParam(9, $energia);
            $preparedStament->bindParam(10, $proteinas);
            $preparedStament->bindParam(11, $grasaTotal);
            $preparedStament->bindParam(12, $grasaSaturada);
            $preparedStament->bindParam(13, $grasaMono);
            $preparedStament->bindParam(14, $grasaPoli);
            $preparedStament->bindParam(15, $grasaTrans);
            $preparedStament->bindParam(16, $colesterol);
            $preparedStament->bindParam(17, $hidratosCarbono);
            $preparedStament->bindParam(18, $azucaresTotales);
            $preparedStament->bindParam(19, $fibra);
            $preparedStament->bindParam(20, $sodio);
            $preparedStament->bindParam(21, $ingredientes);
//            Subida aceptada = 1
//            Subida pendiente = 2
//            Subida rechazada = 3          
            $preparedStament->bindParam(22, $estadoAlimento);
            $preparedStament->bindParam(23, $alergenos);
            $preparedStament->bindParam(24, $trazas);

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
    
    private static function insertFood($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $codigoBarras = $decodedParameters["codigoBarras"];
        $nombreAlimento = $decodedParameters["nombreAlimento"];
        $alergenos = $decodedParameters["alergenos"];
        $trazas = $decodedParameters["trazas"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO alimentos (idUsuario, codigoBarras, nombreAlimento, alergenos, trazas)" .
                " VALUES (?,?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $codigoBarras);
            $preparedStament->bindParam(3, $nombreAlimento);
            $preparedStament->bindParam(4, $alergenos);
            $preparedStament->bindParam(5, $trazas);

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
    
    private static function retrieveNewFoods()
    {
        try {
            $pdo = MysqlManager::get()->getDb();
            
            $comando = "SELECT *"
                    . " FROM alimento_nuevo";

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
    
    private static function retrieveComplaintFoods()
    {
        try {
            $pdo = MysqlManager::get()->getDb();
            
            $comando = "SELECT *"
                    . " FROM alimento_denuncia";

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
    //TODO: Hacer esto como el save new comment
    private static function saveNewComplaint() {
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
        $dbResult = self::insertNewComplaint($decodedParameters);

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
    
    private static function insertNewComplaint($decodedParameters) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $codigoBarras = $decodedParameters["codigoBarras"];
        $nombreAlimento = $decodedParameters["nombreAlimento"];
        $producto = $decodedParameters["producto"];
        $marca = $decodedParameters["marca"];
        $contenidoNeto = $decodedParameters["contenidoNeto"];
        $porcion = $decodedParameters["porcion"];
        $porcionGramos = $decodedParameters["porcionGramos"];
        $energia = $decodedParameters["energia"];
        $proteinas = $decodedParameters["proteinas"];
        $grasaTotal = $decodedParameters["grasaTotal"];
        $grasaSaturada = $decodedParameters["grasaSaturada"];
        $grasaMono = $decodedParameters["grasaMono"];
        $grasaPoli = $decodedParameters["grasaPoli"];
        $grasaTrans = $decodedParameters["grasaTrans"];
        $colesterol = $decodedParameters["colesterol"];
        $hidratosCarbono = $decodedParameters["hidratosCarbono"];
        $azucaresTotales = $decodedParameters["azucaresTotales"];
        $fibra = $decodedParameters["fibra"];
        $sodio = $decodedParameters["sodio"];
        $ingredientes = $decodedParameters["ingredientes"];
        $date = $decodedParameters["date"];
        $estadoAlimento = $decodedParameters["estadoAlimento"];
        $alergenos = $decodedParameters["alergenos"];
        $trazas = $decodedParameters["trazas"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO alimento_denuncia (idUsuario, codigoBarras, nombreAlimento, producto, marca, "
                    . "contenidoNeto, porcion, porcionGramos, energia, proteinas, grasaTotal, grasaSaturada, "
                    . "grasaMono, grasaPoli, grasaTrans, colesterol, hidratosCarbono, azucaresTotales, "
                    . "fibra, sodio, ingredientes, date, estadoAlimento, alergenos, trazas)" .
                " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $codigoBarras);
            $preparedStament->bindParam(3, $nombre);
            $preparedStament->bindParam(4, $producto);
            $preparedStament->bindParam(5, $marca);
            $preparedStament->bindParam(6, $contenidoNeto);
            $preparedStament->bindParam(7, $porcion);
            $preparedStament->bindParam(8, $porcionGramos);
            $preparedStament->bindParam(9, $energia);
            $preparedStament->bindParam(10, $proteinas);
            $preparedStament->bindParam(11, $grasaTotal);
            $preparedStament->bindParam(12, $grasaSaturada);
            $preparedStament->bindParam(13, $grasaMono);
            $preparedStament->bindParam(14, $grasaPoli);
            $preparedStament->bindParam(15, $grasaTrans);
            $preparedStament->bindParam(16, $colesterol);
            $preparedStament->bindParam(17, $hidratosCarbono);
            $preparedStament->bindParam(18, $azucaresTotales);
            $preparedStament->bindParam(19, $fibra);
            $preparedStament->bindParam(20, $sodio);
            $preparedStament->bindParam(21, $date);
            $preparedStament->bindParam(22, $estadoAlimento);
            $preparedStament->bindParam(23, $ingredientes);
            $preparedStament->bindParam(24, $alergenos);
            $preparedStament->bindParam(25, $trazas);

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
    
    private static function retrieveFoodsStatement($idUsuario, $estado){
//            Subida aceptada = 1
//            Subida pendiente = 2
//            Subida rechazada = 3    
            try {
                $pdo = MysqlManager::get()->getDb();

                $comando = "SELECT *"
                        . " FROM alimento_nuevo"
                        . " WHERE idUsuario = ? AND estadoAlimento = ?";

                    // Preparar sentencia
                    $sentencia = $pdo->prepare($comando);
                    //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);
                $sentencia->bindParam(2, $estado, PDO::PARAM_INT);
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
        

        
        private static function retrieveFoodsCreate($idUsuario){
            try {
                $pdo = MysqlManager::get()->getDb();

//                $comando = "SELECT *"
//                        . " FROM alimentos"
//                        . " WHERE idUsuario = ?";
                
                $comando = "SELECT historial_escaneo.idUsuario, historial_escaneo.codigoBarras,"
                        . " alimentos.idPeligroAlimento, alimentos.peligroAlimento, fechaEscaneo,"
                        . " meGusta, alimentos.nombreAlimento"
                        . " FROM historial_escaneo"
                        . " LEFT JOIN alimentos ON historial_escaneo.codigoBarras = alimentos.codigoBarras"
                        . " WHERE historial_escaneo.idUsuario = ? AND (historial_escaneo.escaneo = '1' OR historial_escaneo.escaneo = '2')"
                        . " AND alimentos.idUsuario = ?"
                        . " ORDER BY fechaEscaneo DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);
                $sentencia->bindParam(2, $idUsuario, PDO::PARAM_INT);
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
        
        private static function retrieveFoodsComplaint($idUsuario, $estado){
            
            //i = 1 aceptado
            //i = 2 pendiente
            //i = 3 rechazado
            try {
                $pdo = MysqlManager::get()->getDb();

//                $comando = "SELECT *"
//                        . " FROM alimentos"
//                        . " WHERE idUsuario = ?";
                
                $comando = "SELECT *"
                        . " FROM alimento_denuncia"
                        . " WHERE idUsuario=? AND estadoAlimento=?";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);
                $sentencia->bindParam(2, $estado, PDO::PARAM_INT);
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
        
        private static function retrieveFoodsEdits($codigoBarras){
            
//            Subida aceptada = 1
//            Subida pendiente = 2
//            Subida rechazada = 3
            try {
                $pdo = MysqlManager::get()->getDb();

//                $comando = "SELECT *"
//                        . " FROM alimentos"
//                        . " WHERE idUsuario = ?";
                
                $comando = "SELECT *"
                        . " FROM alimento_denuncia"
                        . " WHERE codigoBarras=? AND estadoAlimento=1";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                //$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                $sentencia->bindParam(1, $codigoBarras, PDO::PARAM_INT);
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

    public static function modifyReport($barcode) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia UPDATE
            $sentence = "UPDATE alimentos "
                    . "SET denuncia = 1 "
                    . "WHERE codigoBarras = ?";

            // Preparar sentencia
            $preparedStatement = $pdo->prepare($sentence);
            $preparedStatement->bindParam(1, $barcode, PDO::PARAM_INT);


            if ($preparedStatement->execute()) {
                $rowCount = $preparedStatement->rowCount();
                $dbResult = self::retrieveFoods($barcode);

                if ($dbResult != NULL) {
                    return $dbResult;
                } else {
                    throw new ApiException(
                        400,
                        0,
                        "Error durante la actualizacion del alimento",
                        "http://localhost",
                        "Puede que no exista el alimento #\" $barcode \"#"
                    );
                }
            }
        }catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el usuario: " . $e->getMessage());
        }        
    }
}