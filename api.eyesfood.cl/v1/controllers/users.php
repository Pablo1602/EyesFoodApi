<?php

require_once 'data/MysqlManager.php';

/**
 * Controlador del recurso "/Users"
 */
class users {

    public static function get($urlSegments) {
        if (isset($urlSegments[0])) {
            return self::retrieveUser($urlSegments[0]);
        }else{
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
    }
    public static function post($urlSegments) {

        //Si por ejemplo se manda solo users/ sin register o login
        if (!isset($urlSegments[0])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        } 
        else if(isset($urlSegments[1])){
            switch ($urlSegments[1]){
                case "register":
                    return self::saveExternalUser();
                    break;
            }
        }
        else{
        switch ($urlSegments[0]) {
            case "register":
                return self::saveUser();
                break;
            case "login":
                return self::authUser();
                break;
            case "external":
                return self::existExternalUser();
                break;
            default:
                throw new ApiException(
                    404,
                    0,
                    "El recurso al que intentas acceder no existe",
                    "http://localhost", "No se encontró el segmento \"Users/$urlSegments[0]\".");
        }
    }
    }

    public static function put($urlSegments) {

    }

    public static function delete($urlSegments) {

    }

    private static function saveUser() {
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
        if (!isset($decodedParameters["nombre"]) ||
            !isset($decodedParameters["apellido"]) ||
            !isset($decodedParameters["correo"]) ||
            !isset($decodedParameters["password"]) /*||
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
        $dbResult = self::insertUser($decodedParameters);

        // Procesar resultado de la inserción
        if ($dbResult) {
            //return ["status" => 201, "message" => "Usuario registrado"];
            return $dbResult;
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }

    private static function authUser() {

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
        if (!isset($decodedParameters["correo"]) ||
            !isset($decodedParameters["password"])
        ) {
            throw new ApiException(
                400,
                0,
                "Las credenciales del usuario deben estar definidas correctamente",
                "http://localhost",
                "El atributo \"correo\" o \"password\" o ambos, están vacíos o no definidos"
            );
        }

        $userId = $decodedParameters["correo"];
        $password = $decodedParameters["password"];

        // Buscar usuario en la tabla
        $dbResult = self::findUserByCredentials($userId, $password);

        // Procesar resultado de la consulta
        // El de la derecha es la columna de la base de datos, case sensitive
        if ($dbResult != NULL) {
            return [
                "status" => 200,
                "idUsuario" => $dbResult["idUsuario"],
                "Nombre" => $dbResult["Nombre"],
                "Apellido" => $dbResult["Apellido"],
                "Correo" => $dbResult["Correo"],
                "FotoUsuario" => $dbResult["FotoUsuario"],
                "Reputacion" => $dbResult["Reputacion"],
                "FechaNacimiento" => $dbResult["FechaNacimiento"],
                "Sexo" => $dbResult["Sexo"],
                "Estatura" => $dbResult["Estatura"],
                "Nacionalidad" => $dbResult["Nacionalidad"],
                "rol" => $dbResult["rol"]
            ];
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

    private static function insertUser($decodedParameters) {
        //Extraer datos del usuario
        $nombre = $decodedParameters["nombre"];
        $apellido = $decodedParameters["apellido"];
        $id = $decodedParameters["correo"];
        //$foto = $decodedParameters["fotousuario"];
        $password = $decodedParameters["password"];
        //$reputacion = $decodedParameters["reputacion"];
        //$nacimiento = $decodedParameters["fechanacimiento"];
        //$sexo = $decodedParameters["sexo"];
        //$estatura = $decodedParameters["estatura"];
        //$nacionalidad = $decodedParameters["nacionalidad"];

        // Encriptar contraseña
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generar token
        $token = uniqid(rand(), TRUE);

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuarios (Nombre, Apellido, Correo, hash_password)" .
                " VALUES (?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $nombre);
            $preparedStament->bindParam(2, $apellido);
            $preparedStament->bindParam(3, $id);
            $preparedStament->bindParam(4, $hashPassword);

            // Ejecutar sentencia
            if($preparedStament->execute()){
                // Buscar usuario en la tabla
                $dbResult = self::findUserByCredentials($id, $password);

                // Procesar resultado de la consulta
                if ($dbResult != NULL) {
                    // Crear alergia del nuevo usuario
                    $pdo = MysqlManager::get()->getDb();
                    // Componer sentencia INSERT
                    $sentence = "INSERT INTO usuario_alergia (idUsuario)" .
                        " VALUES (?)";

                    // Preparar sentencia
                    $preparedStament = $pdo->prepare($sentence);
                    $preparedStament->bindParam(1, dbResult["idUsuario"]);
                    $preparedStament->execute();
                    if(!(self::findUserAllergy(dbResult["idUsuario"]))){
                        throw new ApiException(
                            400,
                            0,
                            "http://localhost",
                            "No se pudo crear alergeno del usuario \"$correo\"."
                        );       
                    }

                    return [
                    // El de la derecha es la columna de la base de datos, case sensitive
                        "status" => 200,
                        "idUsuario" => $dbResult["idUsuario"],
                        "Nombre" => $dbResult["Nombre"],
                        "Apellido" => $dbResult["Apellido"],
                        "Correo" => $dbResult["Correo"],
                        "FotoUsuario" => $dbResult["FotoUsuario"],
                        "Reputacion" => $dbResult["Reputacion"],
                        "FechaNacimiento" => $dbResult["FechaNacimiento"],
                        "Sexo" => $dbResult["Sexo"],
                        "Estatura" => $dbResult["Estatura"],
                        "Nacionalidad" => $dbResult["Nacionalidad"],
                        "rol" => $dbResult["rol"]
                    ];
                } else {
                    throw new ApiException(
                        400,
                        0,
                        "Número de identificación o contraseña inválidos",
                        "http://localhost",
                        "Puede que no exista un usuario creado con el correo \"$correo\" o que la contraseña \"$password\" sea incorrecta."
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

    private static function findUserByCredentials($userId, $password) {
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                . " FROM usuarios"
                . " WHERE Correo = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $userId, PDO::PARAM_STR, 128);
            // Ejecutar sentencia
            if ($preparedSentence->execute()) {
                $userData = $preparedSentence->fetch(PDO::FETCH_ASSOC);
                // Verificar contraseña
                if (password_verify($password, $userData["hash_password"])) {
                    return $userData;
                } else {
                    return null;
                }

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
    
    private static function existExternalUser() {
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
        
        //Extraer datos del usuario
        $correo = $decodedParameters["correo"];
        $dbResult = self::findExternalUser($correo);
        
        if ($dbResult == null) {
            return $dbResult;
        } else {
            throw new ApiException(
                400,
                0,
                "Número de identificación o contraseña inválidos",
                "http://localhost",
                "Puede que no exista un usuario creado con el correo \"$correo\" o que la contraseña sea incorrecta."
            );
        }
    }
    
    private static function findExternalUser($correo) {
        
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT * FROM usuarios WHERE Correo=?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $correo, PDO::PARAM_INT);

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
    
    private static function saveExternalUser() {
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
        if (!isset($decodedParameters["nombre"]) ||
            !isset($decodedParameters["apellido"]) ||
            !isset($decodedParameters["correo"])
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
        $dbResult = self::insertExternalUser($decodedParameters);

        // Procesar resultado de la inserción
        if ($dbResult) {
            return $dbResult;
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del usuario.");
        }
    }
    
    private static function insertExternalUser($decodedParameters) {
        //Extraer datos del usuario
        $nombre = $decodedParameters["nombre"];
        $apellido = $decodedParameters["apellido"];
        $correo = $decodedParameters["correo"];
        $foto = $decodedParameters["foto"];
        $hashPassword = "";

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuarios (Nombre, Apellido, Correo, FotoUsuario, hash_password)" .
                " VALUES (?,?,?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $nombre);
            $preparedStament->bindParam(2, $apellido);
            $preparedStament->bindParam(3, $correo);
            $preparedStament->bindParam(4, $foto);
            $preparedStament->bindParam(5, $hashPassword);

            // Ejecutar sentencia
            if($preparedStament->execute()){
                // Buscar usuario en la tabla
        $dbResult = self::findExternalUser($correo);

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
                "Puede que no exista un usuario creado con el correo \"$correo\" o que la contraseña \"$password\" sea incorrecta."
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



    private static function retrieveUser($codigo) {
        
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT * FROM usuarios WHERE idUsuario=?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $codigo, PDO::PARAM_INT);

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
    
    private static function findUserAllergy($idUsuario) {
        
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT * FROM usuario_alergia WHERE idUsuario = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idUsuario, PDO::PARAM_INT);

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

