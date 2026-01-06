<?php

namespace App;

use PDO;

use Exception;

class booking {
     private PDO $db;

     public function __construct(PDO $db){
        $this->db = $db ;
     }
     public function checkAvailability($homeId, $startDate, $endDate){
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE home_id = :home_id
            AND status = 'confirmed'
            AND (
               (:start BETWEEN check_in AND check_out)
                OR (:end BETWEEN check_in AND check_out)
                OR (check_in BETWEEN :start AND :end)
            )";
             $stmt = $this->db->prepare($sql);
             $stmt->execute([
                ':home_id' => $homeId,
                ':start' => $startDate,
                ':end' => $endDate
             ]);

             if($stmt->fetchColumn() > 0){
                throw new Exception("this home in not available for the selected dates.");
             }

                return true;
     }

     public function create($data){
         
        if($data['check_out'] <= $data['check_in']){
            throw new Exception("checkout must be later than checkin ");
        
        }

        $this->checkAvailability(
            $data['home_id'],
            $data['check_in'],
            $data['check_out']
        );

        $days = (strtotime($data['check_out']) - strtotime($data['check_in'])) / 86400;
        $priceStmt = $this->db->prepare("SELECT price_per_night FROM homes WHERE id = ?");
        $priceStmt->execute([$data['home_id']]);
        $pricePerNight = $priceStmt->fetchColumn();

        $total = $days * $pricePerNight;

        $sql = "INSERT INTO reservations (home_id, user_id, check_in, check_out, total_price)
                VALUES (:home_id, :user_id, :check_in, :check_out, :total_price)";
                $stmt = $this->db->prepare($sql);

                return $stmt->execute([
                    ':home_id' => $data['home_id'],
                    ':user_id' => $data['user_id'],
                    ':check_in' => $data['check_in'],
                    ':check_out' => $data['check_out'],
                    ':total_price' => $total
                ]);
     }

     //take it off
        public function cancel($bookingId, $userId = null, $isAdmin = false){
            if(!$isAdmin){
                $stmt = $this->db->prepare("SELECT user_id FROM reservations WHERE id = ?");
                $stmt->execute([$bookingId]);

                if($stmt->fetchColumn() != $userId){
                    throw new Exception("you cannot cancel someone else's booking.");
                }
            }

            $stmt = $this->db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            return $stmt->execute([$bookingId]);
        
        }

        //resrvation list traveler
        public function finduserBookings($userId){
            $stmt = $this->db->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function findRentalBookings($homeId){
            $stmt = $this->db->prepare("SELECT * FROM reservations WHERE home_id = ? ORDER BY check_in");
            $stmt->execute([$homeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
}