<?php
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ .'/../models/BookCategory.php';
require_once __DIR__ . '/../class/UniversalSanitizer.php';

class BookController {
    private $bookModel;
    private $categoryModel;
    private $sanitizer;

    public function __construct() {
        $this->bookModel = new Book();
        $this->categoryModel = new BookCategory();
        $this->sanitizer = new UniversalSanitizer();
    }

    public function index() {
        $books = $this->bookModel->getAll();
        $categories = $this->categoryModel->getAll();
        require_once '../views/books/index.php';
    }

    public function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    ':title' => $this->sanitizer->textArea($_POST['title'] ?? ''),
                    ':author' => $this->sanitizer->name($_POST['author'] ?? ''),
                    ':isbn' => $this->sanitizer->isbn($_POST['isbn'] ?? ''),
                    ':category_id' => $this->sanitizer->integer($_POST['category_id'] ?? 0),
                    ':description' => $this->sanitizer->textArea($_POST['description'] ?? ''),
                    ':quantity' => $this->sanitizer->integer($_POST['quantity'] ?? 1),
                    ':available_quantity' => $this->sanitizer->integer($_POST['quantity'] ?? 1),
                    ':published_year' => $this->sanitizer->integer($_POST['published_year'] ?? date('Y'))
                ];

                // Validate ISBN uniqueness
                if($this->bookModel->isbnExists($data[':isbn'])) {
                    throw new RuntimeException("El ISBN ya está registrado");
                }

                // Handle image upload
                $imagePath = $this->handleImageUpload();
                if($imagePath) {
                    $data[':image_path'] = $imagePath;
                    $data[':thumbnail_path'] = $this->createThumbnail($imagePath);
                }

                if($this->bookModel->create($data)) {
                    header('Location: index.php?action=books');
                    exit();
                } else {
                    throw new RuntimeException("Error al crear el libro");
                }
                
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
                $categories = $this->categoryModel->getAll();
                require_once '../views/books/create.php';
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
                $categories = $this->categoryModel->getAll();
                require_once '../views/books/create.php';
            }
        }
        
        $categories = $this->categoryModel->getAll();
        require_once '../views/books/create.php';
    }

    public function edit($id) {
        $id = $this->sanitizer->integer($id);
        $book = $this->bookModel->getById($id);
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    ':title' => $this->sanitizer->textArea($_POST['title'] ?? ''),
                    ':author' => $this->sanitizer->name($_POST['author'] ?? ''),
                    ':isbn' => $this->sanitizer->isbn($_POST['isbn'] ?? ''),
                    ':category_id' => $this->sanitizer->integer($_POST['category_id'] ?? 0),
                    ':description' => $this->sanitizer->textArea($_POST['description'] ?? ''),
                    ':quantity' => $this->sanitizer->integer($_POST['quantity'] ?? 1),
                    ':available_quantity' => $this->sanitizer->integer($_POST['available_quantity'] ?? 1),
                    ':published_year' => $this->sanitizer->integer($_POST['published_year'] ?? date('Y')),
                    ':id' => $id
                ];

                // Validate ISBN uniqueness
                if($book['isbn'] !== $data[':isbn'] && $this->bookModel->isbnExists($data[':isbn'])) {
                    throw new RuntimeException("El ISBN ya está registrado");
                }

                // Handle image upload if new image is provided
                if(!empty($_FILES['image']['name'])) {
                    $imagePath = $this->handleImageUpload();
                    if($imagePath) {
                        // Delete old images if they exist
                        if($book['image_path'] && file_exists("../".$book['image_path'])) {
                            unlink("../".$book['image_path']);
                        }
                        if($book['thumbnail_path'] && file_exists("../".$book['thumbnail_path'])) {
                            unlink("../".$book['thumbnail_path']);
                        }
                        
                        $data[':image_path'] = $imagePath;
                        $data[':thumbnail_path'] = $this->createThumbnail($imagePath);
                        $this->bookModel->updateImage($id, $imagePath, $data[':thumbnail_path']);
                    }
                }

                if($this->bookModel->update($id, $data)) {
                    header('Location: index.php?action=books');
                    exit();
                } else {
                    throw new RuntimeException("Error al actualizar el libro");
                }
                
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
                $categories = $this->categoryModel->getAll();
                require_once '../views/books/edit.php';
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
                $categories = $this->categoryModel->getAll();
                require_once '../views/books/edit.php';
            }
        }
        
        $categories = $this->categoryModel->getAll();
        require_once '../views/books/edit.php';
    }

    public function delete($id) {
        $id = $this->sanitizer->integer($id);
        $book = $this->bookModel->getById($id);
        
        // Delete associated images
        if($book['image_path'] && file_exists("../".$book['image_path'])) {
            unlink("../".$book['image_path']);
        }
        if($book['thumbnail_path'] && file_exists("../".$book['thumbnail_path'])) {
            unlink("../".$book['thumbnail_path']);
        }
        
        if($this->bookModel->delete($id)) {
            header('Location: index.php?action=books');
            exit();
        } else {
            $error = "Error al eliminar el libro";
            require_once '../views/books/index.php';
        }
    }

    public function search() {
        $term = $this->sanitizer->searchQuery($_GET['term'] ?? '');
        $books = $this->bookModel->search($term);
        require_once '../views/books/search_results.php';
    }

    private function handleImageUpload() {
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadDir = '../assets/uploads/';
                $originalName = $this->sanitizer->fileName($_FILES['image']['name']);
                $fileName = uniqid() . '_' . $originalName;
                $targetPath = $uploadDir . $fileName;
                
                // Check if the upload directory exists
                if(!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Validate file type
                $this->sanitizer->imageExtension($originalName);
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    return 'assets/uploads/' . $fileName;
                }
            } catch (InvalidArgumentException $e) {
                // Log error or handle as needed
                return null;
            }
        }
        return null;
    }

    private function createThumbnail($imagePath, $width = 200, $height = 200) {
        // ... (mismo código que antes, ya es seguro)
    }
}