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

$errores = [];
$datos = $contacto;

if ($_POST) {
    $datos = array_map('sanitizar', $_POST);
    $datos['id'] = $id;
    
    if (empty($datos['nombre'])) {
        $errores[] = "El nombre es obligatorio";
    }
    
    if (empty($datos['telefono'])) {
        $errores[] = "El teléfono es obligatorio";
    } elseif (!validarTelefono($datos['telefono'])) {
        $errores[] = "El formato del teléfono no es válido";
    }
    
    if (!empty($datos['email']) && !validarEmail($datos['email'])) {
        $errores[] = "El formato del email no es válido";
    }
    
    $imagen = $contacto['imagen'];
    
    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
        if ($imagen && file_exists($imagen)) {
            unlink($imagen);
        }
        $imagen = null; 
    }
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extension, $extensionesPermitidas)) {
            if ($imagen && file_exists($imagen)) {
                unlink($imagen);
            }
            
            $nombreImagen = uniqid() . '.' . $extension;
            $rutaImagen = 'uploads/' . $nombreImagen;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaImagen)) {
                $imagen = $rutaImagen;
            } else {
                $errores[] = "Error al subir la imagen";
            }
        } else {
            $errores[] = "Formato de imagen no permitido. Use JPG, PNG o GIF";
        }
    }
    if (empty($errores)) {
        try {
            $stmt = $db->prepare("UPDATE contactos SET 
                                nombre = :nombre, 
                                apellido = :apellido, 
                                telefono = :telefono, 
                                email = :email, 
                                direccion = :direccion, 
                                notas = :notas, 
                                imagen = :imagen 
                                WHERE id = :id");
            
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':notas', $datos['notas']);
            $stmt->bindParam(':imagen', $imagen);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = mostrarMensaje('success', 'Contacto actualizado exitosamente');
                header('Location: ver.php?id=' . $id);
                exit;
            }
        } catch(PDOException $e) {
            $errores[] = "Error al actualizar el contacto: " . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Editar Contacto</h2>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="id" value="<?= $datos['id'] ?>">
    
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre *</label>
        <input type="text" class="form-control" id="nombre" name="nombre" 
               value="<?= $datos['nombre'] ?>" required maxlength="50">
    </div>
    
    <div class="col-md-6">
        <label for="apellido" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="apellido" name="apellido" 
               value="<?= $datos['apellido'] ?>" maxlength="50">
    </div>
    
    <div class="col-md-6">
        <label for="telefono" class="form-label">Teléfono *</label>
        <input type="tel" class="form-control" id="telefono" name="telefono" 
               value="<?= $datos['telefono'] ?>" required pattern="[0-9\-\+\s\(\)]{7,15}">
        <div class="form-text">Formato: números, +, -, () y espacios</div>
    </div>
    
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" 
               value="<?= $datos['email'] ?>">
    </div>
    
    <div class="col-12">
        <label for="direccion" class="form-label">Dirección</label>
        <input type="text" class="form-control" id="direccion" name="direccion" 
               value="<?= $datos['direccion'] ?>" maxlength="255">
    </div>
    
    <div class="col-12">
        <label for="notas" class="form-label">Notas</label>
        <textarea class="form-control" id="notas" name="notas" rows="3" 
                  maxlength="500"><?= $datos['notas'] ?></textarea>
    </div>
    
    <div class="col-12">
        <label for="imagen" class="form-label">Foto del Contacto</label>
        
        <?php if ($datos['imagen']): ?>
            <div class="mb-3">
                <p><strong>Imagen actual:</strong></p>
                <img src="<?= $datos['imagen'] ?>" alt="Foto actual" class="img-thumbnail" style="max-height: 150px;">
                
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="eliminar_imagen" id="eliminar_imagen" value="1">
                    <label class="form-check-label text-danger" for="eliminar_imagen">
                        🗑️ <strong>Eliminar imagen actual</strong>
                    </label>
                </div>
            </div>
        <?php endif; ?>
        
        <input type="file" class="form-control" id="imagen" name="imagen" 
               accept="image/jpeg,image/png,image/gif">
        <div class="form-text">Formatos permitidos: JPG, PNG, GIF (máx. 2MB)</div>
    </div>
    
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Actualizar Contacto</button>
        <a href="ver.php?id=<?= $datos['id'] ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxEliminar = document.getElementById('eliminar_imagen');
    const inputImagen = document.getElementById('imagen');
    
    if (checkboxEliminar && inputImagen) {
        checkboxEliminar.addEventListener('change', function() {
            if (this.checked) {
                inputImagen.disabled = true;
                inputImagen.style.opacity = '0.5';
            } else {
                inputImagen.disabled = false;
                inputImagen.style.opacity = '1';
            }
        });
        
        inputImagen.addEventListener('change', function() {
            if (this.files.length > 0) {
                checkboxEliminar.checked = false;
                checkboxEliminar.dispatchEvent(new Event('change'));
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>