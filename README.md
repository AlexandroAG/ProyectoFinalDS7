# Sistema de Gestión de Biblioteca

Un sistema completo para la gestión de bibliotecas con módulos administrativos y acceso público para estudiantes.

---

## Características Principales

### 🔐 Módulo de Autenticación
- Login seguro para administradores y bibliotecarios  
- Contraseñas almacenadas con hash  
- Control de sesiones  

### 👥 Módulo de Usuarios
- CRUD completo de usuarios administrativos  
- Roles: Administrador y Bibliotecario  
- Paginación y búsqueda de usuarios  

### 🎓 Módulo de Estudiantes
- Registro de estudiantes con CIP único  
- Gestión de información personal (nombres, apellidos, fecha nacimiento)  
- Asignación de carreras  
- Validación para evitar duplicados  

### 🏫 Módulo de Carreras
- Catálogo de carreras universitarias  
- Asignación a estudiantes  

### 📚 Módulo de Libros
- Catálogo completo de libros  
- Subida de imágenes con generación de thumbnails  
- Gestión de categorías (Química, Sistemas, Matemática, etc.)  
- Control de inventario (unidades disponibles)  
- Búsqueda avanzada  

### 📆 Módulo de Reservaciones
- Sistema de préstamos para estudiantes  
- Control de devoluciones  
- Actualización automática de inventario  

### 📊 Módulo de Reportes
- Generación de reportes en Excel  
- Estadísticas de libros más prestados  
- Filtros por períodos de tiempo  

### 🌐 Interfaz Pública para Estudiantes
- Catálogo de libros disponibles  
- Sistema de reservas  
- Sugerencias de compra para la biblioteca  

---

## Requisitos del Sistema

- Servidor web (Apache, Nginx)  
- PHP 7.4 o superior  
- MySQL 5.7 o superior  

**Extensiones PHP requeridas:**
- PDO  
- GD (para manejo de imágenes)  
- mbstring  
- zip (para reportes Excel)  

---

## Instalación

### 1️⃣ Clonar el repositorio:

git clone https://github.com/tu-usuario/sistema-biblioteca.git
cd sistema-biblioteca
2️⃣ Instalar dependencias:
bash
Copiar
Editar
composer install
3️⃣ Configurar la base de datos:
Crear una base de datos MySQL

Importar el archivo: database/schema.sql

Configurar las credenciales en config/database.php

4️⃣ Configurar el entorno:
Asegurarse que el directorio assets/uploads/ tenga permisos de escritura

Configurar la base URL en el archivo .htaccess si es necesario

🔐 Credenciales iniciales:
Usuario: admin

Contraseña: root2514