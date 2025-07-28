<?php
declare(strict_types=1);

class UniversalSanitizer {
    private $dbConnection;

    public function __construct(?mysqli $dbConnection = null) {
        $this->dbConnection = $dbConnection;
    }

    public static function basicString(string $input): string {
        return trim($input);
    }

    public function dbEscapeString(string $input): string {
        if (!$this->dbConnection) {
            throw new RuntimeException("Database connection not provided for escaping");
        }
        return $this->dbConnection->real_escape_string(self::basicString($input));
    }

    public static function htmlSpecialChars(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ==================== VALIDACIÓN DE DATOS ====================
    public static function email(string $email): string {
    $clean = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    
    // Permitir cadena vacía pero no lanzar excepción
    if (empty($clean)) {
        return '';
    }
    
    if (!filter_var($clean, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Email inválido");
    }
    
    return strtolower($clean);
}

public static function cedulaPanama(string $cedula, bool $required = true): string {
    $clean = preg_replace('/[^0-9-]/', '', trim($cedula));

    if ($required && !preg_match('/^\d-\d{4}-\d{4}$/', $clean)) {
        throw new InvalidArgumentException("Formato de cédula inválido. Use: 8-0000-0000");
    }

    if (!$required && empty($clean)) {
        return '';
    }

    if (!$required && !empty($clean) && !preg_match('/^\d-\d{4}-\d{4}$/', $clean)) {
        throw new InvalidArgumentException("Formato de cédula inválido. Use: 8-0000-0000");
    }

    return $clean;
}



public static function phoneNumber(string $telefono, bool $required = true): string {
    $clean = preg_replace('/[^0-9]/', '', trim($telefono));

    if (!$required && empty($clean)) {
        return '';
    }

    if (!preg_match('/^[267]\d{7}$/', $clean)) {
        if ($required || (!$required && !empty($clean))) {
            throw new InvalidArgumentException("Número de teléfono inválido. Debe comenzar con 2, 6 o 7 y tener 8 dígitos.");
        }
    }

    return $clean;
}


    // ==================== TEXTOS Y NOMBRES ====================
        public static function name(string $name): string {
        // Permitir cadenas vacías pero no lanzar excepción
        if (empty(trim($name))) {
            return '';
        }
        
        $clean = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/u', '', trim($name));
        if (empty($clean)) {
            return '';
        }
        
        return mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
        }


    public static function textArea(string $text): string {
        $clean = strip_tags(trim($text));
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $clean);
        return self::htmlSpecialChars($clean);
    }

    // ==================== FECHAS Y NÚMEROS ====================
    public static function date(string $date): string {
        try {
            $d = new DateTime(trim($date));
            return $d->format('Y-m-d');
        } catch (Exception $e) {
            throw new InvalidArgumentException("Fecha inválida");
        }
    }

    public static function integer($value): int {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Valor debe ser numérico");
        }
        return (int)$value;
    }

    public static function float($value): float {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Valor debe ser numérico");
        }
        return (float)$value;
    }

    // ==================== CONTRASEÑAS ====================
    public static function password(string $pass): string {
        $clean = trim($pass);
        if (strlen($clean) < 8) {
            throw new InvalidArgumentException("La contraseña debe tener al menos 8 caracteres");
        }
        return $clean; // La hashearemos después
    }

    // ==================== ARCHIVOS E IMÁGENES ====================
    public static function fileName(string $name): string {
        $clean = preg_replace('/[^a-zA-Z0-9_\-.]/', '', $name);
        return substr($clean, 0, 255);
    }

    public static function imageExtension(string $filename): string {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            throw new InvalidArgumentException("Tipo de archivo no permitido");
        }
        return $ext;
    }

    // ==================== BUSQUEDAS Y SQL ====================
    public function searchQuery(string $term): string {
        $clean = $this->dbEscapeString($term);
        return str_replace(['%', '_'], ['\%', '\_'], $clean);
    }

    // ==================== VALIDACIONES ESPECÍFICAS ====================
    public static function role(string $role): string {
        $allowed = ['estudiante', 'admin', 'profesor', 'bibliotecario'];
        $clean = strtolower(trim($role));
        if (!in_array($clean, $allowed)) {
            throw new InvalidArgumentException("Rol no válido");
        }
        return $clean;
    }

    public static function isbn(string $isbn): string {
        $clean = preg_replace('/[^0-9X]/', '', strtoupper(trim($isbn)));
        // Validación básica de ISBN (puedes mejorar esto)
        if (strlen($clean) != 10 && strlen($clean) != 13) {
            throw new InvalidArgumentException("ISBN inválido");
        }
        return $clean;
    }
}
?>