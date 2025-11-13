<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

$database = new Database();
$db = $database->getConnection();

$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Visitante';
$mensaje_bienvenida = "Bienvenido, $nombreUsuario";

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 10;
$inicio = ($pagina > 1) ? ($pagina * $porPagina - $porPagina) : 0;

$stmt = $db->query("SELECT COUNT(*) as total FROM contactos");
$totalContactos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$paginas = ceil($totalContactos / $porPagina);

$stmt = $db->prepare("SELECT * FROM contactos ORDER BY nombre, apellido LIMIT :inicio, :porPagina");
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':porPagina', $porPagina, PDO::PARAM_INT);
$stmt->execute();
$contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Lista de Contactos</h2>
    <a href="crear.php" class="btn btn-success">➕ Nuevo Contacto</a>
</div>

<form method="GET" action="buscar.php" class="mb-4">
    <div class="row g-2">
        <div class="col-md-8">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, apellido o teléfono..." 
                   value="<?= isset($_GET['q']) ? sanitizar($_GET['q']) : '' ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
        </div>
    </div>
</form>

<?php if (empty($contactos)): ?>
    <div class="alert alert-info">
        No hay contactos en la agenda. <a href="crear.php" class="alert-link">Agregar el primer contacto</a>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contactos as $contacto): ?>
                    <tr>
                        <td><?= sanitizar($contacto['nombre'] . ' ' . $contacto['apellido']) ?></td>
                        <td><?= sanitizar($contacto['telefono']) ?></td>
                        <td><?= sanitizar($contacto['email']) ?></td>
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

    <?php if ($paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($pagina > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $paginas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina < $paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>s