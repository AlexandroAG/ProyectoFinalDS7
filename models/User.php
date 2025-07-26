<?php
require_once 'BaseModel.php';

class User extends BaseModel {
    public function login($username, $password) {
        $query = 'SELECT * FROM users WHERE username = :username';
        $stmt = $this->executeQuery($query, [':username' => $username]);
        
        if($stmt && $stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function create($data) {
        $query = 'INSERT INTO users (username, password, full_name, email, role) 
                  VALUES (:username, :password, :full_name, :email, :role)';
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->executeQuery($query, $data);
    }

    public function getAll() {
        $query = 'SELECT * FROM users';
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById($id) {
        $query = 'SELECT * FROM users WHERE id = :id';
        $stmt = $this->executeQuery($query, [':id' => $id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function update($id, $data) {
        $query = 'UPDATE users SET full_name = :full_name, email = :email, role = :role 
                  WHERE id = :id';
        $data[':id'] = $id;
        return $this->executeQuery($query, $data);
    }

    public function delete($id) {
        $query = 'DELETE FROM users WHERE id = :id';
        return $this->executeQuery($query, [':id' => $id]);
    }

    public function usernameExists($username, $excludeId = null) {
        $query = 'SELECT id FROM users WHERE username = :username';
        $params = [':username' => $username];
        
        if($excludeId) {
            $query .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->executeQuery($query, $params);
        return $stmt && $stmt->rowCount() > 0;
    }

    public function emailExists($email, $excludeId = null) {
        $query = 'SELECT id FROM users WHERE email = :email';
        $params = [':email' => $email];
        
        if($excludeId) {
            $query .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->executeQuery($query, $params);
        return $stmt && $stmt->rowCount() > 0;
    }
}
?>