<?php

/**
 * Controlador del endpoint /foods
 */
class comments
{
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
        //Hacer switch case para encontrar la URL tipo foods/codigodeBarra/aditivos
        //barcode=urlSegments[0], aditivos e ingredientes=urlSegments[1]
        else if (isset($urlSegments[0])) {
            return self::retrieveComments($urlSegments[0]);
       }
    }

    public static function post($urlSegments)
    {
        //Si se manda algo mas que la url
        if (isset($urlSegments[1])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
        else if (isset($urlSegments[0])) {
            return self::saveNewComment($urlSegments[0]);
       }

    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }
    
    private static function retrieveComments($codigoBarras)
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT idComentario, usuarios.Nombre, usuarios.Apellido, usuarios.FotoUsuario, "
                        . "usuarios.Reputacion, comentario, fecha "
                        . "FROM comentarios "
                        . "LEFT JOIN usuarios ON comentarios.idUsuario = usuarios.idUsuario "
                        . "WHERE codigoBarras = ? ORDER BY fecha DESC";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);
                // Ligar idContacto e idUsuario
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
    
    private static function saveNewComment($codigoBarras) {
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
        $dbResult = self::insertNewComment($decodedParameters, $codigoBarras);

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
    
    private static function insertNewComment($decodedParameters, $codigoBarras) {
        //Extraer datos del usuario
        $idUsuario = $decodedParameters["idUsuario"];
        $comentario = $decodedParameters["comentario"];

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO comentarios (idUsuario, codigoBarras, comentario)" .
                " VALUES (?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $codigoBarras);
            $preparedStament->bindParam(3, $comentario);

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