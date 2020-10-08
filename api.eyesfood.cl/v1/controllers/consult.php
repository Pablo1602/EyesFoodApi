<?php

/**
 * Controlador del endpoint /consult
 */
class consult
{
    public static function get($urlSegments)
    {
        // TODO: 2. Verificaciones, restricciones, defensas
        //?????????????????????????????????????????????????
        if (isset($urlSegments[0])) {
            throw new ApiException(
                400,
                0,
                "El recurso está mal referenciado",
                "http://localhost",
                "El recurso $_SERVER[REQUEST_URI] no esta sujeto a resultados"
            );
        }
    }

    public static function post($urlSegments)
    {
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
        else {
            if(isset($urlSegments[0])){
                switch ($urlSegments[0]){
                    case "consult":
                        return self::newConsult();
                        break;
                }  
            }
        }
    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }

    private static function newConsult()
    {
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
        $dbResult = self::insertNewConsult($decodedParameters);

        // Procesar resultado de la inserción
        if ($dbResult) {
            $idExperto = $decodedParameters["idExperto"];
            $idUsuario = $decodedParameters["idUsuario"];
            return ["status" => 201, "message" => "Consulta creada ".$idExperto." ".$idUsuario];
        } else {
            throw new ApiException(
                500,
                0,
                "Error del servidor",
                "http://localhost",
                "Error en la base de datos al ejecutar la inserción del consulta.");
        }
    }

    private static function insertNewConsult ($decodedParameters)
    {
         //Extraer datos del usuario
        $idExperto = $decodedParameters["idExperto"];
        $idUsuario = $decodedParameters["idUsuario"];
      

        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO consultas (idExperto, idUsuario)" .
                " VALUES (?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idExperto, PDO::PARAM_INT);
            $preparedStament->bindParam(2, $idUsuario, PDO::PARAM_INT);

            // Ejecutar sentencia
            return $preparedStament->execute();

        } catch (PDOException $e) {
            throw new ApiException(
                500,
                0,
                "Error de base de datos en el servidor",
                "http://localhost",
                "Ocurrió el siguiente error al intentar insertar el consulta: " . $e->getMessage());
        }       
    }   

}