<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['id']) || empty($_GET['id'])) {
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
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-8">
        <h2>Detalles del Contacto</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="editar.php?id=<?= $contacto['id'] ?>" class="btn btn-warning">✏️ Editar</a>
        <a href="eliminar.php?id=<?= $contacto['id'] ?>" class="btn btn-danger" 
           onclick="return confirm('¿Estás seguro de eliminar este contacto?')">🗑️ Eliminar</a>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<hr>

<div class="row">
    <?php if ($contacto['imagen']): ?>
        <div class="col-md-4 text-center mb-4">
            <img src="<?= $contacto['imagen'] ?>" alt="Foto de <?= sanitizar($contacto['nombre']) ?>" 
                 class="img-fluid rounded shadow" style="max-height: 300px;">
        </div>
        <div class="col-md-8">
    <?php else: ?>
        <div class="col-12">
    <?php endif; ?>
    
        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?= sanitizar($contacto['nombre'] . ' ' . $contacto['apellido']) ?></h3>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Información de Contacto</h5>
                        <p><strong>📞 Teléfono:</strong> <?= sanitizar($contacto['telefono']) ?></p>
                        <?php if ($contacto['email']): ?>
                            <p><strong>📧 Email:</strong> <?= sanitizar($contacto['email']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($contacto['direccion']): ?>
                    <div class="col-md-6">
                        <h5>Dirección</h5>
                        <p><?= nl2br(sanitizar($contacto['direccion'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($contacto['notas']): ?>
                <div class="mt-4">
                    <h5>Notas</h5>
                    <p class="text-muted"><?= nl2br(sanitizar($contacto['notas'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>