<?php
require_once 'data/MysqlManager.php';
/**
 * Controlador del endpoint /notifications
 */
class notifications
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
        else if (isset($urlSegments[1])) {

            return self::findNotifictionUser($urlSegments[0], $urlSegments[1]);
        }
        else if (isset($urlSegments[0])) {
            return self::retrieveNotificationUser($urlSegments[0]);
        }
        else {
            return self::retrieveNotification();
        }
    }

    public static function post($urlSegments)
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
                case 'new':
                    return self::insertNewNotification($urlSegments[1],$urlSegments[2]);
                    break;
                case 'no':
                	return self::modifyNotificationUser($urlSegments[1], $urlSegments[2]);
                    break;
                default:
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

    private static function retrieveNotification(){
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT *"
                        . " FROM notificaciones"
                        . " WHERE habilitar = 1 ORDER BY fecha DESC";
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

    private static function retrieveNotificationUser($idUsuario){
        try {
            $pdo = MysqlManager::get()->getDb();
                $comando = "SELECT n.idNotificacion, n.titulo, n.texto, n.push, n.fecha"
                        . " FROM usuario_notificacion u_n LEFT JOIN notificaciones n ON"
                        . " u_n.idNotificacion = n.idNotificacion"
                        . " WHERE u_n.idUsuario = ?"
                        . " AND u_n.habilitar = 1" 
                        . " AND n.habilitar = 1 ORDER BY n.fecha DESC";
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

    private static function insertNewNotification($idUsuario, $idNotificacion){
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia INSERT
            $sentence = "INSERT INTO usuario_notificacion (idUsuario, idNotificacion)".
                " VALUES (?,?,?)";

            // Preparar sentencia
            $preparedStament = $pdo->prepare($sentence);
            $preparedStament->bindParam(1, $idUsuario);
            $preparedStament->bindParam(2, $idNotificacion);

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

    private static function modifyNotificationUser($idUsuario, $idNotificacion){
    	try {
        $pdo = MysqlManager::get()->getDb();

        // Componer sentencia UPDATE
        $sentence = "UPDATE usuario_notificacion "
                . "SET habilitar = 0 "
                . "WHERE idNotificacion = ? AND idUsuario = ?";

        // Preparar sentencia
        $preparedStatement = $pdo->prepare($sentence);
        
        $preparedStatement->bindParam(1, $idNotificacion, PDO::PARAM_INT);
        $preparedStatement->bindParam(2, $idUsuario, PDO::PARAM_INT);

        // Ejecutar sentencia
        if ($preparedStatement->execute()) {

            $rowCount = $preparedStatement->rowCount();
            $dbResult = self::findNotifictionUser($idUsuario, $idNotificacion);

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

    private static function findNotifictionUser($idUsuario, $idNotificacion) {
        
        try {
            $pdo = MysqlManager::get()->getDb();

            // Componer sentencia SELECT
            $sentence = "SELECT habilitar"
                        . " FROM usuario_notificacion"
                        . " WHERE idUsuario = ? AND idNotificacion = ?";

            // Preparar sentencia
            $preparedSentence = $pdo->prepare($sentence);
            $preparedSentence->bindParam(1, $idUsuario, PDO::PARAM_INT);
            $preparedSentence->bindParam(2, $idNotificacion, PDO::PARAM_INT);

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
