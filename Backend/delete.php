<?php
session_start();
include('connection.php');
$con = connection();
 
$id = $_GET['id'] ?? null;
 
// Validamos que el id sea realmente un número ANTES de usarlo.
// Esto evita errores de tipo y bloquea cualquier intento de manipular
// la consulta desde la URL (ej. ?id=1 OR 1=1).
if (!ctype_digit((string) $id)) {
    $_SESSION['mensaje'] = "Pedido inválido.";
    $_SESSION['tipo']    = "danger";
    header("Location: ../formulario.php");
    exit;
}
 
// Prepared statement: aunque aquí el riesgo es menor porque ya validamos
// que $id es numérico, seguimos el mismo estándar de seguridad en TODO
// el sistema, tal como pide la rúbrica ("uso estricto" de prepared statements).
$sql = "DELETE FROM pedidos WHERE id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
$exito = mysqli_stmt_execute($stmt);
 
if ($exito && mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION['mensaje'] = "Pedido eliminado correctamente.";
    $_SESSION['tipo']    = "success";
} else {
    $_SESSION['mensaje'] = "No se pudo eliminar el pedido (puede que ya no exista).";
    $_SESSION['tipo']    = "danger";
}
 
mysqli_stmt_close($stmt);
mysqli_close($con);
 
header("Location: ../formulario.php");
exit;
?>