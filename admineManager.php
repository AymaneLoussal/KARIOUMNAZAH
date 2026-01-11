<?php
namespace App;

use PDO;

class admineManager{
    private PDO $db;

    public function __construct(PDO $db){
        $this->db = $db;
    }
    public function getAllUsers(): array {
        $sql = "SELECT u.id, u.name, u.email, u.is_active, GROUP_CONCAT(r.name) as roles FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            GROUP BY u.id ORDER BY u.created_at DESC";

            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    }

    public function toggleUserStatus(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }

    public function getAllRentals(): array {
        $sql = "SELECT h.*, u.name as host_name
                FROM homes h
                JOIN users u ON h.host_id = u.id
                ORDER BY h.created_at DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function toggleRentalStatus(int $rentalId): bool {
        $stmt = $this->db->prepare("UPDATE homes SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute([':id' => $rentalId]);
    }
}