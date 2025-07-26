<?php
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ .'/../models/BookCategory.php';

class BookController {
    private $bookModel;
    private $categoryModel;

    public function __construct() {
        $this->bookModel = new Book();
        $this->categoryModel = new BookCategory();
    }

    public function index() {
        $books = $this->bookModel->getAll();
        $categories = $this->categoryModel->getAll();
        require_once '../views/books/index.php';
    }

    public function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':title' => trim($_POST['title']),
                ':author' => trim($_POST['author']),
                ':isbn' => trim($_POST['isbn']),
                ':category_id' => trim($_POST['category_id']),
                ':description' => trim($_POST['description']),
                ':quantity' => trim($_POST['quantity']),
                ':available_quantity' => trim($_POST['quantity']), // Initially same as quantity
                ':published_year' => trim($_POST['published_year'])
            ];

            // Validate ISBN uniqueness
            if($this->bookModel->isbnExists($data[':isbn'])) {
                $error = "El ISBN ya está registrado";
                $categories = $this->categoryModel->getAll();
                require_once '../views/books/create.php';
                return;
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
                $error = "Error al crear el libro";
            }
        }
        
        $categories = $this->categoryModel->getAll();
        require_once '../views/books/create.php';
    }

    public function edit($id) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':title' => trim($_POST['title']),
                ':author' => trim($_POST['author']),
                ':isbn' => trim($_POST['isbn']),
                ':category_id' => trim($_POST['category_id']),
                ':description' => trim($_POST['description']),
                ':quantity' => trim($_POST['quantity']),
                ':available_quantity' => trim($_POST['available_quantity']),
                ':published_year' => trim($_POST['published_year'])
            ];

            // Validate ISBN uniqueness
            $book = $this->bookModel->getById($id);
            if($book['isbn'] !== $data[':isbn'] && $this->bookModel->isbnExists($data[':isbn'])) {
                $error = "El ISBN ya está registrado";
                $categories = $this->categoryModel->getAll();
                require_once '../views/books/edit.php';
                return;
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

            $data[':id'] = $id;
            if($this->bookModel->update($id, $data)) {
                header('Location: index.php?action=books');
                exit();
            } else {
                $error = "Error al actualizar el libro";
            }
        }
        
        $book = $this->bookModel->getById($id);
        $categories = $this->categoryModel->getAll();
        require_once '../views/books/edit.php';
    }

    public function delete($id) {
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
        $term = trim($_GET['term']);
        $books = $this->bookModel->search($term);
        require_once '../views/books/search_results.php';
    }

    private function handleImageUpload() {
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Check if the upload directory exists
            if(!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            
            if(in_array($fileType, $allowedTypes)) {
                if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    return 'assets/uploads/' . $fileName;
                }
            }
        }
        return null;
    }

    private function createThumbnail($imagePath, $width = 200, $height = 200) {
        $originalPath = "../" . $imagePath;
        $info = getimagesize($originalPath);
        
        if(!$info) return null;
        
        list($originalWidth, $originalHeight, $type) = $info;
        
        switch($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($originalPath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($originalPath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($originalPath);
                break;
            default:
                return null;
        }
        
        $thumbnail = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = 'assets/uploads/thumb_' . $pathInfo['filename'] . '.jpg';
        
        imagejpeg($thumbnail, "../" . $thumbnailPath, 90);
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return $thumbnailPath;
    }
}
?>