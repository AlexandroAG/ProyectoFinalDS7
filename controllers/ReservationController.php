<?php
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/auth_middleware.php';

class ReservationController {
    private $reservationModel;

    public function __construct() {
        $this->reservationModel = new Reservation();
        
        // Iniciar sesión solo si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Obtener las reservas del usuario actual
     */
    public function getUserReservations() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }

        return $this->reservationModel->getUserReservations($_SESSION['user_id']);
    }

    /**
     * Obtener las reservas activas del usuario actual
     */
    public function getActiveUserReservations() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }

        return $this->reservationModel->getActiveUserReservations($_SESSION['user_id']);
    }

    /**
     * Devolver un libro
     */
    public function returnBook($prestamoId) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        return $this->reservationModel->returnBook($prestamoId, $_SESSION['user_id']);
    }

    /**
     * Obtener estadísticas de reservas del usuario actual
     */
    public function getUserStats() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'total_reservas' => 0,
                'activas' => 0,
                'completadas' => 0,
                'atrasadas' => 0
            ];
        }

        return $this->reservationModel->getUserReservationStats($_SESSION['user_id']);
    }

    /**
     * Verificar si el usuario ya tiene un libro reservado
     */
    public function hasActiveReservation($bookId) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        return $this->reservationModel->hasActiveReservation($_SESSION['user_id'], $bookId);
    }
}
?>
