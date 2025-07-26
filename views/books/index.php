<?php require_once 'partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Libros</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="index.php?action=books&subaction=create" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Libro
            </a>
        </div>
    </div>
</div>

<div class="mb-3">
    <form action="index.php?action=books&subaction=search" method="GET" class="row g-3">
        <input type="hidden" name="action" value="books">
        <input type="hidden" name="subaction" value="search">
        <div class="col-md-8">
            <input type="text" name="term" class="form-control" placeholder="Buscar por título, autor o categoría..." 
                   value="<?= htmlspecialchars($_GET['term'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
            <a href="index.php?action=books" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Portada</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Disponibles</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($books as $index => $book): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td>
                    <?php if($book['thumbnail_path']): ?>
                        <img src="<?= $book['thumbnail_path'] ?>" alt="Portada" style="width: 50px; height: auto;">
                    <?php else: ?>
                        <i class="bi bi-book" style="font-size: 2rem;"></i>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= htmlspecialchars($book['category_name']) ?></td>
                <td><?= $book['available_quantity'] ?> / <?= $book['quantity'] ?></td>
                <td>
                    <a href="index.php?action=books&subaction=show&id=<?= $book['id'] ?>" 
                       class="btn btn-sm btn-outline-primary" title="Ver">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="index.php?action=books&subaction=edit&id=<?= $book['id'] ?>" 
                       class="btn btn-sm btn-outline-secondary" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="index.php?action=books&subaction=delete&id=<?= $book['id'] ?>" 
                       class="btn btn-sm btn-outline-danger" title="Eliminar"
                       onclick="return confirm('¿Estás seguro de eliminar este libro?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'partials/footer.php'; ?>