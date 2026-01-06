<?php

namespace App;

use PDO;
use Exception;

class User
{
    private ?int $id;
    private string $email;
    private string $password_hash;
    private string $name;
    private string $role;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->id = null;
    }

    /* ==========================
       Register new user
       ========================== */
    public function register(array $data): bool
    {
        $this->email = trim($data['email']);
        $this->name  = trim($data['username']);
        $password    = $data['password'];
        $roleName    = $data['role']; // traveler | host | admin

        $this->password_hash = password_hash($password, PASSWORD_BCRYPT);

        $this->db->beginTransaction();

        try {
            // 1️⃣ Insert into users
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, name)
                VALUES (:email, :password_hash, :name)
            ");
            $stmt->execute([
                ':email'         => $this->email,
                ':password_hash' => $this->password_hash,
                ':name'          => $this->name
            ]);


            $userId = (int) $this->db->lastInsertId();

            // 2️⃣ Get role id
            $stmtRole = $this->db->prepare("
                SELECT id FROM roles WHERE name = :name LIMIT 1
            ");
            $stmtRole->execute([':name' => $roleName]);

            $role = $stmtRole->fetch(PDO::FETCH_ASSOC);

            if (!$role) {
                throw new Exception("Role not found: " . $roleName);
            }

            $roleId = (int)$role['id'];

            // 3️⃣ Assign role to user
            $stmtAssign = $this->db->prepare("
                INSERT INTO user_roles (user_id, role_id)
                VALUES (:user_id, :role_id)
            ");
            $stmtAssign->execute([
                ':user_id' => $userId,
                ':role_id' => $roleId
            ]);
            

            $this->db->commit();

            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /* ==========================
       Find user by email
       ========================== */
    public function findByEmail(string $email): ?array
    {
        $query = "
            SELECT u.id, u.email, u.password_hash, u.name, r.name AS role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /* ==========================
       Login user
       ========================== */
    public function login(string $email, string $password): bool
{
    $user = $this->findByEmail($email);

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    // Hydrate object
    $this->id    = (int)$user['id'];
    $this->email = $user['email'];
    $this->name  = $user['name'];      // <-- was missing
    $this->role  = $user['role'] ?? 'traveler'; // <-- was missing

    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id']   = $this->id;
    $_SESSION['user_name'] = $this->name;
    $_SESSION['role']      = $this->role;

    return true;
}


    /* ==========================
       Logout user
       ========================== */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();
    }

    /* ==========================
       Update profile
       ========================== */
    public function updateProfile(int $id, array $data): bool
    {
        $query = "
            UPDATE users
            SET name = :name,
                email = :email
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':name'  => trim($data['username']),
            ':email' => trim($data['email']),
            ':id'    => $id
        ]);
    }

    /* ==========================
       Get user role
       ========================== */
    public function getRole(): string
    {
        return $this->role;
    }
}
