<?php
namespace App;

use PDO;

class Rental {
    private PDO $db;

    public function __construct(PDO $db){
        $this->db = $db;
    }

    public function create(array $data): bool {
        $query = "INSERT INTO homes (host_id, title, description, city, address, price_per_night, max_guests, image_url)
                  VALUES (:host_id, :title, :description, :city, :address, :price_per_night, :max_guests, :image_url)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':host_id' => $data['host_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':city' => $data['city'],
            ':address' => $data['address'],
            ':price_per_night' => $data['price_per_night'],
            ':max_guests' => $data['max_guests'],
            ':image_url' => $data['image_url'] ?? null
        ]);
    }

    public function update(int $id, int $hostId, array $data): bool {
        $query = "UPDATE homes SET title=:title, description=:description, city=:city, address=:address, 
                  price_per_night=:price_per_night, max_guests=:max_guests, image_url=:image_url
                  WHERE id=:id AND host_id=:host_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':city' => $data['city'],
            ':address' => $data['address'],
            ':price_per_night' => $data['price_per_night'],
            ':max_guests' => $data['max_guests'],
            ':image_url' => $data['image_url'] ?? null,
            ':id' => $id,
            ':host_id' => $hostId
        ]);
    }

    public function delete(int $id, int $hostId): bool {
        $query = "DELETE FROM homes WHERE id=:id AND host_id=:host_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id'=>$id, ':host_id'=>$hostId]);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM homes WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        $home = $stmt->fetch(PDO::FETCH_ASSOC);
        return $home ?: null;
    }

    public function findAllByHost(int $hostId): array {
        $stmt = $this->db->prepare("SELECT * FROM homes WHERE host_id=:host_id ORDER BY created_at DESC");
        $stmt->execute([':host_id'=>$hostId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(array $criteria = [], int $page = 1, int $limit = 6)
{
    $sql = "SELECT homes.*, users.name AS host_name 
            FROM homes 
            JOIN users ON homes.host_id = users.id ";

    $params = [];

    // Filter city
    if (!empty($criteria['city'])) {
        $sql .= " AND city LIKE :city";
        $params[':city'] = "%".$criteria['city']."%";
    }

    // Min price
    if (!empty($criteria['min_price'])) {
        $sql .= " AND price_per_night >= :min_price";
        $params[':min_price'] = $criteria['min_price'];
    }

    // Max price
    if (!empty($criteria['max_price'])) {
        $sql .= " AND price_per_night <= :max_price";
        $params[':max_price'] = $criteria['max_price'];
    }

    // ---- Pagination ----
    $offset = ($page - 1) * $limit;

    $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);

    // Bind numeric values manually
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>
