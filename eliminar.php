<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = mostrarMensaje('danger', 'ID de contacto no proporcionado');
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $db->prepare("SELECT * FROM contactos WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$contacto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contacto) {
    $_SESSION['mensaje'] = mostrarMensaje('danger', 'Contacto no encontrado');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($contacto['imagen'] && file_exists($contacto['imagen'])) {
            unlink($contacto['imagen']);
        }
        
        $stmt = $db->prepare("DELETE FROM contactos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = mostrarMensaje('success', 'Contacto eliminado exitosamente');
            header('Location: index.php');
            exit;
        }
    } catch(PDOException $e) {
        $_SESSION['mensaje'] = mostrarMensaje('danger', 'Error al eliminar el contacto: ' . $e->getMessage());
        header('Location: index.php');
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">Confirmar Eliminación</h4>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h5>¿Estás seguro de que deseas eliminar este contacto?</h5>
                </div>
                
                <div class="alert alert-info text-start">
                    <strong>Contacto a eliminar:</strong><br>
                    <strong>Nombre:</strong> <?= sanitizar($contacto['nombre'] . ' ' . $contacto['apellido']) ?><br>
                    <strong>Teléfono:</strong> <?= sanitizar($contacto['telefono']) ?><br>
                    <?php if ($contacto['email']): ?>
                        <strong>Email:</strong> <?= sanitizar($contacto['email']) ?>
                    <?php endif; ?>
                </div>
                
                <p class="text-muted">
                    <strong>⚠️ Advertencia:</strong> Esta acción no se puede deshacer.
                </p>
                
                <form method="POST" class="d-inline">
                    <button type="submit" class="btn btn-danger">🗑️ Sí, Eliminar Contacto</button>
                </form>
                <a href="ver.php?id=<?= $contacto['id'] ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>