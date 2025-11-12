<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!file_exists('includes/db.php') || !file_exists('includes/funciones.php')) {
    die("❌ Error: Archivos incluidos no encontrados");
}

require_once 'includes/db.php';
require_once 'includes/funciones.php';

$database = new Database();
$db = $database->getConnection();

$resultados = [];
$terminoBusqueda = '';
$mensaje = '';

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $terminoBusqueda = trim($_GET['q']);
    
    try {
        $terminoLike = '%' . $terminoBusqueda . '%';
        
        $sql = "SELECT * FROM contactos 
                WHERE nombre LIKE :termino 
                   OR apellido LIKE :termino 
                   OR telefono LIKE :termino
                   OR CONCAT(nombre, ' ', apellido) LIKE :termino
                   OR CONCAT(apellido, ' ', nombre) LIKE :termino
                ORDER BY nombre, apellido";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':termino', $terminoLike);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $mensaje = "Se encontraron " . count($resultados) . " contacto(s) para \"$terminoBusqueda\"";
        
    } catch(PDOException $e) {
        $mensaje = "❌ Error en la búsqueda: " . $e->getMessage();
    }
} else {
    $mensaje = "🔍 Ingresa un término de búsqueda";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Contactos - Agenda Telefónica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">📒 Agenda Telefónica</a>
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Inicio</a>
                <a class="nav-link" href="crear.php">Nuevo Contacto</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>🔍 Buscar Contactos</h2>
        
        <form method="GET" action="buscar.php" class="mb-4">
            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="q" class="form-control" 
                           placeholder="Buscar por nombre, apellido, teléfono o nombre completo..." 
                           value="<?= htmlspecialchars($terminoBusqueda) ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
                    <a href="index.php" class="btn btn-secondary w-100 mt-2">← Volver</a>
                </div>
            </div>
            <div class="form-text mt-1">
                💡 <strong>Tip:</strong> Puedes buscar "Juan Pérez", "Pérez Juan" o solo "Juan"
            </div>
        </form>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($resultados)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $contacto): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($contacto['nombre'] . ' ' . $contacto['apellido']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($contacto['telefono']) ?></td>
                                <td><?= htmlspecialchars($contacto['email'] ?? '') ?></td>
                                <td>
                                    <a href="ver.php?id=<?= $contacto['id'] ?>" class="btn btn-sm btn-info">👁️ Ver</a>
                                    <a href="editar.php?id=<?= $contacto['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                                    <a href="eliminar.php?id=<?= $contacto['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('¿Estás seguro de eliminar este contacto?')">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($_GET['q'])): ?>
            <div class="alert alert-warning">
                No se encontraron contactos para "<?= htmlspecialchars($terminoBusqueda) ?>"
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">Agenda Telefónica &copy; <?= date('Y'); ?></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>s