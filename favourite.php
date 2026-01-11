<?php

namespace App;

use PDO;

class Favourite {
    private PDO $db;

    public function __construct(PDO $db){
        $this->db = $db;
    }

    public function addFavourite($userId, $rentalId){
        if($this->isFavourite($userId, $rentalId)){
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO favorites (user_id, home_id) VALUES (:user_id, :home_id)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':home_id' => $rentalId
        ]);
    }

    public function removeFavourite($userId, $rentalId){
        if(!$this->isFavourite($userId, $rentalId)){
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM favorites WHERE user_id = :user_id AND home_id = :home_id ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':home_id' => $rentalId
        ]);
    }

    public function findUserFavourite($userId){
        $stmt = $this->db->prepare("SELECT h.* FROM favorites f JOIN homes h ON h.id = f.home_id
                                     WHERE f.user_id = :user_id ORDER BY h.created_at DESC");
                                     $stmt->execute([':user_id' => $userId]);
                                     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isFavourite($userId, $rentalId){
        $stmt = $this->db->prepare("SELECT 1 FROM favorites WHERE user_id = :user_id AND home_id = :home_id LIMIT 1");
        $stmt->execute([
            ':user_id' => $userId,
            ':home_id' => $rentalId
        ]);
        return $stmt->fetchColumn() !== false;
    }
    
}