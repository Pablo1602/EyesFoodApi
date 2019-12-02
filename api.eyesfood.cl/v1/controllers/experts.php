<?php

/**
 * Controlador del endpoint /experts
 */
class experts
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
        //Hacer switch case para encontrar la URL tipo foods/codigodeBarra/aditivos
        //barcode=urlSegments[0], aditivos e ingredientes=urlSegments[1]
        else {
            if(isset($urlSegments[1])){
                switch ($urlSegments[1]){
                    case "foods":
                        return self::retrieveExpertsFood($urlSegments[0]);
                        break;
                }
            }
            else{
                return self::retrieveExperts();   
            }
       }
    }

    public static function post($urlSegments)
    {
        if (isset($urlSegments[2])) {
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
            if(isset($urlSegments[1])){
                return self::modifyExpertRating($urlSegments[0], $urlSegments[1]);   
            }
        }
    }

    public static function put($urlSegments)
    {

    }

    public static function delete($urlSegments)
    {

    }
    
    private static function retrieveExperts()
    {
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT * "
                        . "FROM expertos ";

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);

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
    
    private static function modifyExpertRating($idExperto, $reputacion){
    try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE expertos "
                . "SET reputacion = ? "
                . "WHERE idExperto = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $reputacion, PDO::PARAM_INT);
        $preparedStatement->bindParam(2, $idExperto, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findExperto($idExperto);

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
    
    private static function findExpert($idExperto){
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM expertos"
                        . " WHERE idExperto = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idExperto, PDO::PARAM_INT);
            
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
    
    private static function retrieveExpertsFood($idExperto){
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT *"
                        . " FROM alimentos"
                        . " WHERE alimentos.idExperto = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idExperto, PDO::PARAM_INT);
            
            // Ejecutar sentencia
            if ($preparedSentence->execute()) {
                return $preparedSentence->fetchall(PDO::FETCH_ASSOC);
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