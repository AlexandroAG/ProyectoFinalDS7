<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/BaseModel.php';

class Reservation extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtener todas las reservas/préstamos de un usuario específico
     */
    public function getUserReservations($userId) {
        try {
            $sql = "SELECT p.*, l.titulo, l.autor, l.isbn, l.imagen_path, l.anio_publicacion,
                           c.nombre as categoria_nombre
                    FROM prestamos p 
                    INNER JOIN libros l ON p.libro_id = l.id 
                    LEFT JOIN categorias_libros c ON l.categoria_id = c.id
                    WHERE p.usuario_id = :user_id 
                    ORDER BY p.fecha_prestamo DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reservas del usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener reservas activas de un usuario
     */
    public function getActiveUserReservations($userId) {
        try {
            $sql = "SELECT p.*, l.titulo, l.autor, l.isbn, l.imagen_path, l.anio_publicacion,
                           c.nombre as categoria_nombre
                    FROM prestamos p 
                    INNER JOIN libros l ON p.libro_id = l.id 
                    LEFT JOIN categorias_libros c ON l.categoria_id = c.id
                    WHERE p.usuario_id = :user_id AND p.estado = 'activo'
                    ORDER BY p.fecha_prestamo DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reservas activas del usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devolver un libro (marcar préstamo como completado)
     */
    public function returnBook($prestamoId, $userId) {
        try {
            $this->db->beginTransaction();

            // Verificar que el préstamo pertenezca al usuario y esté activo
            $checkSql = "SELECT p.*, l.id as libro_id FROM prestamos p 
                        INNER JOIN libros l ON p.libro_id = l.id 
                        WHERE p.id = :prestamo_id AND p.usuario_id = :user_id AND p.estado = 'activo'";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':prestamo_id' => $prestamoId, ':user_id' => $userId]);
            $prestamo = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$prestamo) {
                $this->db->rollBack();
                return false;
            }

            // Marcar préstamo como completado
            $updateSql = "UPDATE prestamos SET estado = 'completado', fecha_devolucion_real = NOW() 
                         WHERE id = :prestamo_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([':prestamo_id' => $prestamoId]);

            // Incrementar cantidad disponible del libro
            $bookSql = "UPDATE libros SET cantidad_disponible = cantidad_disponible + 1 
                       WHERE id = :libro_id";
            $bookStmt = $this->db->prepare($bookSql);
            $bookStmt->execute([':libro_id' => $prestamo['libro_id']]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al devolver libro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario ya tiene un libro específico prestado
     */
    public function hasActiveReservation($userId, $bookId) {
        try {
            $sql = "SELECT COUNT(*) FROM prestamos 
                    WHERE usuario_id = :user_id AND libro_id = :book_id AND estado = 'activo'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar reserva activa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de reservas de un usuario
     */
    public function getUserReservationStats($userId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_reservas,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activas,
                        SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN estado = 'atrasado' THEN 1 ELSE 0 END) as atrasadas
                    FROM prestamos 
                    WHERE usuario_id = :user_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de reservas: " . $e->getMessage());
            return [
                'total_reservas' => 0,
                'activas' => 0,
                'completadas' => 0,
                'atrasadas' => 0
            ];
        }
    }
}
?>
