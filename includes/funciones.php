<?php

function sanitizar($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validarTelefono($telefono) {
    return preg_match('/^[0-9\-\+\s\(\)]{7,15}$/', $telefono);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function mostrarMensaje($tipo, $mensaje) {
    $clase = $tipo == 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $clase alert-dismissible fade show' role='alert'>
                $mensaje
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}
?>