<?php
require_once 'BaseModel.php';

class Book extends BaseModel {
    public function getAll() {
        $query = 'SELECT b.*, bc.name as category_name FROM books b 
                  JOIN book_categories bc ON b.category_id = bc.id';
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById($id) {
        $query = 'SELECT b.*, bc.name as category_name FROM books b 
                  JOIN book_categories bc ON b.category_id = bc.id 
                  WHERE b.id = :id';
        $stmt = $this->executeQuery($query, [':id' => $id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function create($data) {
        $query = 'INSERT INTO books (title, author, isbn, category_id, description, 
                  quantity, available_quantity, image_path, thumbnail_path, published_year)
                  VALUES (:title, :author, :isbn, :category_id, :description, 
                  :quantity, :available_quantity, :image_path, :thumbnail_path, :published_year)';
        return $this->executeQuery($query, $data);
    }

    public function update($id, $data) {
        $query = 'UPDATE books SET title = :title, author = :author, isbn = :isbn, 
                  category_id = :category_id, description = :description, 
                  quantity = :quantity, available_quantity = :available_quantity, 
                  published_year = :published_year WHERE id = :id';
        $data[':id'] = $id;
        return $this->executeQuery($query, $data);
    }

    public function updateImage($id, $imagePath, $thumbnailPath) {
        $query = 'UPDATE books SET image_path = :image_path, 
                  thumbnail_path = :thumbnail_path WHERE id = :id';
        return $this->executeQuery($query, [
            ':image_path' => $imagePath,
            ':thumbnail_path' => $thumbnailPath,
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $query = 'DELETE FROM books WHERE id = :id';
        return $this->executeQuery($query, [':id' => $id]);
    }

    public function search($term) {
        $query = 'SELECT b.*, bc.name as category_name FROM books b 
                  JOIN book_categories bc ON b.category_id = bc.id 
                  WHERE b.title LIKE :term OR b.author LIKE :term OR bc.name LIKE :term';
        $stmt = $this->executeQuery($query, [':term' => "%$term%"]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function isbnExists($isbn, $excludeId = null) {
        $query = 'SELECT id FROM books WHERE isbn = :isbn';
        $params = [':isbn' => $isbn];
        
        if($excludeId) {
            $query .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->executeQuery($query, $params);
        return $stmt && $stmt->rowCount() > 0;
    }
}
?>