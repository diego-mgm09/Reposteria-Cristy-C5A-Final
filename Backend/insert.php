<?php
session_start();

include('connection.php');
$catalogo = require('productos.php');
$con = connection();

function regresarConError(array $errores): void
{
    $_SESSION['mensaje'] = 'No se pudo guardar tu pedido: ' . implode(' ', $errores);
    $_SESSION['tipo'] = 'danger';
    header('Location: ../formulario.php#order-form');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    regresarConError(['Solicitud no válida.']);
}

$name          = trim($_POST['full_name'] ?? '');
$phone_number  = trim($_POST['phone_number'] ?? '');
$delivery_date = trim($_POST['delivery_date'] ?? '');
$payment_type  = trim($_POST['payment_type'] ?? '');
$nit_code      = trim($_POST['nit_code'] ?? '');
$extra_comment = trim($_POST['extra_comment'] ?? '');
$cart_json     = $_POST['cart_json'] ?? '[]';

$errores = [];

if ($name === '' || mb_strlen($name) > 100) {
    $errores[] = 'El nombre completo es obligatorio y debe tener máximo 100 caracteres.';
}

if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone_number)) {
    $errores[] = 'El número de teléfono no es válido.';
}

$fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $delivery_date);
if (!$fecha || $fecha->format('Y-m-d') !== $delivery_date || $delivery_date < date('Y-m-d')) {
    $errores[] = 'La fecha de entrega no es válida.';
}

$pagos_validos = ['Efectivo', 'POS'];
if (!in_array($payment_type, $pagos_validos, true)) {
    $errores[] = 'Selecciona un método de pago válido.';
}

if ($nit_code === '' || mb_strlen($nit_code) > 20) {
    $errores[] = 'El NIT es obligatorio y debe tener máximo 20 caracteres.';
}

if (mb_strlen($extra_comment) > 2000) {
    $errores[] = 'Los comentarios no pueden superar 2000 caracteres.';
}

$carrito_recibido = json_decode($cart_json, true);
$carrito_validado = [];
$total_centavos = 0;

if (!is_array($carrito_recibido) || count($carrito_recibido) === 0 || count($carrito_recibido) > count($catalogo)) {
    $errores[] = 'Agrega al menos un producto válido al carrito.';
} else {
    foreach ($carrito_recibido as $item) {
        $id = is_array($item) ? ($item['id'] ?? '') : '';
        $cantidad = is_array($item) ? filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT) : false;

        if (!isset($catalogo[$id]) || $cantidad === false || $cantidad < 1 || $cantidad > 99 || isset($carrito_validado[$id])) {
            $errores[] = 'El carrito contiene un producto o una cantidad no válida.';
            break;
        }

        $producto = $catalogo[$id];
        $carrito_validado[$id] = [
            'nombre' => $producto['nombre'],
            'precio_centavos' => $producto['precio_centavos'],
            'cantidad' => $cantidad,
        ];
        $total_centavos += $producto['precio_centavos'] * $cantidad;
    }
}

if ($total_centavos <= 0) {
    $errores[] = 'El total del pedido no es válido.';
}

if (!empty($errores)) {
    mysqli_close($con);
    regresarConError(array_values(array_unique($errores)));
}

$total = $total_centavos / 100;

try {
    mysqli_begin_transaction($con);

    $sql_pedido = 'INSERT INTO pedidos
        (name, phone_number, delivery_date, payment_type, nit_code, total, extra_comment)
        VALUES (?, ?, ?, ?, ?, ?, ?)';
    $stmt_pedido = mysqli_prepare($con, $sql_pedido);
    mysqli_stmt_bind_param(
        $stmt_pedido,
        'sssssds',
        $name,
        $phone_number,
        $delivery_date,
        $payment_type,
        $nit_code,
        $total,
        $extra_comment
    );
    mysqli_stmt_execute($stmt_pedido);
    $pedido_id = mysqli_insert_id($con);

    $sql_detalle = 'INSERT INTO detalle_pedido
        (pedido_id, nombre_producto, precio_unitario, cantidad)
        VALUES (?, ?, ?, ?)';
    $stmt_detalle = mysqli_prepare($con, $sql_detalle);

    $detalle_nombre = '';
    $detalle_precio = 0.0;
    $detalle_cantidad = 0;
    mysqli_stmt_bind_param(
        $stmt_detalle,
        'isdi',
        $pedido_id,
        $detalle_nombre,
        $detalle_precio,
        $detalle_cantidad
    );

    foreach ($carrito_validado as $item) {
        $detalle_nombre = $item['nombre'];
        $detalle_precio = $item['precio_centavos'] / 100;
        $detalle_cantidad = $item['cantidad'];
        mysqli_stmt_execute($stmt_detalle);
    }

    mysqli_commit($con);

    mysqli_stmt_close($stmt_detalle);
    mysqli_stmt_close($stmt_pedido);

    $_SESSION['mensaje'] = '¡Tu pedido se guardó con éxito! Te contactaremos pronto.';
    $_SESSION['tipo'] = 'success';
    $_SESSION['limpiar_carrito'] = true;
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($con);
    error_log('Error al guardar pedido: ' . $e->getMessage());

    $_SESSION['mensaje'] = 'Ocurrió un error al guardar tu pedido. Intenta de nuevo.';
    $_SESSION['tipo'] = 'danger';
}

mysqli_close($con);
header('Location: ../formulario.php');
exit;
