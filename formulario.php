<?php
// session_start() SIEMPRE debe ir antes de cualquier salida HTML,
// por eso va en la primerísima línea del archivo.
session_start();

$limpiar_carrito = !empty($_SESSION['limpiar_carrito']);
unset($_SESSION['limpiar_carrito']);

include('Backend/connection.php');

$con = connection();

$sql = "SELECT p.*,
               (SELECT GROUP_CONCAT(
                    CONCAT(dp.nombre_producto, ' ×', dp.cantidad)
                    ORDER BY dp.id SEPARATOR '||'
                )
                FROM detalle_pedido dp
                WHERE dp.pedido_id = p.id) AS productos
        FROM pedidos p
        ORDER BY p.id DESC";
$query = mysqli_query($con, $sql);

// Si la consulta falla, mysqli_query() regresa "false" en vez de un resultado.
// Sin este chequeo, el while() de abajo intenta leer "false" como si fuera
// una tabla de resultados, y ahí es donde salta el error que viste.
// Con esto, en vez de adivinar, vas a ver la razón EXACTA (tabla mal escrita,
// columna que no existe, etc.)
if (!$query) {
    die('<div style="padding:2rem; font-family:sans-serif; color:#900; background:#fdecea;">
            <strong>Error en la consulta SQL:</strong> ' . mysqli_error($con) . '
         </div>');
}

$hoy = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Dancing+Script:wght@400..700&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

     <!-- LINK DE BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- LINK DE CSS -->
    <link rel="stylesheet" href="styles.css">

    <!-- LINK DE BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <script src="JavaScript/scroll-animations.js" defer></script>

</head>
<body data-clear-cart="<?= $limpiar_carrito ? 'true' : 'false' ?>">
     <header>
            <nav class="navbar navbar-expand-lg py-2">
                <div class="container">
                    <a class="navbar-brand me-5" href="index.html" aria-label="Ir a la página de inicio"><img src="imgs/Logu.png" alt="Pastelería Cristy" style="height: 80px;"></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse align-items-lg-center" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                        <li class="nav-item">

                            <a class="nav-link nav-linking-dark" href="index.html">Home</a>

                        </li>

                        <li class="nav-item">

                            <a class="nav-link nav-linking-dark" href="conocenos.html">Conócenos</a>

                        </li>

                        <li class="nav-item">
                            
                            <a class="nav-link nav-linking-dark" href="catalogo.html">Catálogo</a>
                        
                        </li>

                        <li class="nav-item">
                            <a class="nav-link active nav-linking-dark" aria-current="page" href="formulario.php">Ordena ya</a>
                        </li>

                    </ul>
                    <a class="cart-nav-button" href="#resumen-carrito" aria-label="Ver resumen del carrito">
                        <i class="bi bi-bag-heart" aria-hidden="true"></i>
                        <span class="cart-badge d-none" data-cart-count>0</span>
                    </a>
                    </div>
                </div>
            </nav>
    </header>


    <main>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="container mt-4">
                <div class="alert alert-<?= htmlspecialchars($_SESSION['tipo']) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['mensaje']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php
                // Se muestra UNA sola vez: lo borramos apenas lo leemos para
                // que no reaparezca si el usuario refresca la página.
                unset($_SESSION['mensaje'], $_SESSION['tipo']);
            ?>
        <?php endif; ?>

        <section class="banner">
            <h1 class="titles c text-center display-4" style="padding-top: 80px;" data-reveal="fade">¡Ordena ahora!</h1>
            <div class="d-flex align-items-center justify-content-center flex-column mt-3" data-reveal="up">
                <p class="col-10 col-md-6 text-center px-0">Ordena uno de nuestros pasteles personalizados usando el formulario y disfruta de tu postre único y con esencia.</p>
            </div>
        </section>


        <section>
            <div class="container-fluid">
                <div class="container">
                    <form action="Backend/insert.php" method="POST" id="order-form">
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-8">
                                <h1 class="titles c7 display-5 ms-md-4 mt-5 h1-header" data-reveal="up">Detalles del pedido</h1>
                                <p class="ms-md-4" data-reveal="up">
                                    ¡Ingresa tus datos en el formulario para contactar con nosotros de manera directa! Brindamos todo tipo de información y excelente atención.
                                </p>
                                <div class="form-wrapper" data-reveal="left">
                                
                                    <!-- Card -->
                                    <div class="p-3 p-sm-4 p-lg-5 shadow-pro rounded-5">
                                
                                    <!-- ① Datos personales -->
                                    <p class="section-label">DATOS DEL CLIENTE</p>
                                
                                    <div class="row g-3 mb-3">
                                        <div class="col-12">
                                        <label for="full_name" class="form-label text-muted">Nombre completo</label>
                                        <input
                                            type="text"
                                            id="full_name"
                                            name="full_name"
                                            class="form-control"
                                            required 
                                        />
                                        </div>
                                
                                        <div class="col-sm-6">
                                        <label for="phone_number" class="form-label text-muted">Número de teléfono</label>
                                        <input
                                            type="tel"
                                            id="phone_number"
                                            name="phone_number"
                                            class="form-control"
                                            placeholder="+(502) 0000-0000"
                                            required 
                                        />
                                        </div>
                                
                                        <div class="col-sm-6">
                                        <label for="event_date" class="form-label text-muted">Fecha de entrega</label>
                                        <input
                                            type="date"
                                            id="event_date"
                                            name="delivery_date"
                                            class="form-control"
                                            min="<?= $hoy ?>"
                                            required 
                                        />
                                        </div>
                                    </div>
                                
                                    <!-- Productos seleccionados en el catálogo -->
                                    <p class="section-label mt-4">PRODUCTOS DEL PEDIDO</p>
                                    <input type="hidden" id="cart_json" name="cart_json" value="[]">
                                    <div class="cart-form-hint mb-3">
                                        <i class="bi bi-bag-check" aria-hidden="true"></i>
                                        <span>Tu selección y las cantidades aparecen en el resumen. Puedes modificarlas antes de enviar la orden.</span>
                                    </div>

                                    <!-- ③ Comentarios -->
                                    <p class="section-label mt-4">DETALLES DE FACTURACIÓN</p>

                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-6">
                                            <label for="payment_type" class="form-label text-muted">Método de pago</label>
                                            <select id="payment_type" name="payment_type" class="form-select" required >
                                                <option value="" disabled selected>Selecciona una opción</option>
                                                <option value="Efectivo">Efectivo</option>
                                                <option value="POS">POS al momento de entrega</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-6">
                                        <label for="nit_code" class="form-label text-muted">NIT</label>
                                        <input
                                            type="text"
                                            id="nit_code"
                                            name="nit_code"
                                            class="form-control"
                                            placeholder="CF o número de NIT"
                                            required 
                                        />
                                        </div>
                    
                                    </div>

                                    <!-- ③ Comentarios -->
                                    <p class="section-label mt-4">INFORMACIÓN ADICIONAL</p>
                                
                                    <div class="mb-4">
                                        <label for="extra_comments" class="form-label text-muted">Comentarios extra</label>
                                        <textarea
                                        id="extra_comments"
                                        name="extra_comment"
                                        class="form-control"
                                        placeholder="Alergias, colores específicos, mensaje en el pastel, referencias de diseño…"
                                        ></textarea>
                                    </div>
                                
                                    <!-- Submit -->
                                    <button type="submit" class="btn btn-order btn-1 w-100" data-cart-submit disabled>
                                        Enviar orden
                                    </button>
                                
                                    </div><!-- /form-card -->
                                </div><!-- /form-wrapper -->
                            </div>
                            <div class="col-lg-4 mt-lg-5 pt-lg-4" id="resumen-carrito" data-reveal="right">
                                <aside class="cart-summary shadow-pro">
                                    <span class="cart-eyebrow">TU SELECCIÓN</span>
                                    <h2 class="titles c6 h3 mb-3">Resumen del carrito</h2>

                                    <div class="cart-empty" data-cart-empty>
                                        <i class="bi bi-bag-heart" aria-hidden="true"></i>
                                        <p class="mb-1 fw-semibold">Aún no agregaste productos</p>
                                        <span>Visita el catálogo y presiona el botón + de cada postre.</span>
                                        <a href="catalogo.html" class="btn btn-outline-secondary mt-3">Ir al catálogo</a>
                                    </div>

                                    <div class="d-none" data-cart-content>
                                        <div class="cart-items" data-cart-items></div>
                                        <div class="cart-total-row">
                                            <span>Total del pedido</span>
                                            <strong data-cart-total>Q 0.00</strong>
                                        </div>
                                        <a href="catalogo.html" class="cart-continue-link">
                                            <i class="bi bi-plus-circle" aria-hidden="true"></i> Agregar más productos
                                        </a>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
        </section>

                        

          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </main>

    
    
    <footer>
        <div class="container-fluid bc6" >
            <div class="container pt-5 pb-4 h-100">
                <div style="border-bottom: #fff 2px solid;" class="pb-5">
                    <div class="row g-4" data-reveal="up">
                        <div class="col-12 col-lg-5">
                            <img src="imgs/Logu-obs.png" alt="" class="mb-2 mb-lg-5" style="height: 80px;">  
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2 text-white">
                            <h5 class="fw-bold">Enlaces</h5>
                            <p><a class="footer-link" href="index.html">Inicio</a></p>
                            <p><a class="footer-link" href="conocenos.html">Conócenos</a></p>
                            <p><a class="footer-link" href="catalogo.html">Productos</a></p>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3 text-white">
                            <h5 class="fw-bold">Información de contacto</h5>
                            <p><i class="bi bi-alarm-fill"></i> Horario: 7 a. m.–9 p. m.</p>
                            <p><i class="bi bi-envelope-at-fill"></i> Email: pedidos@cristy.com</p>
                        </div>
                        <div class="col-12 col-lg-2 text-white">
                            <h5 class="fw-bold">Cobertura</h5>
                            <p>Disponibles: 7 a. m.–9 p. m.</p>
                        </div>
                    </div>
                </div>
                <div class="pt-4" >
                    <div class="row align-items-center g-3" data-reveal="fade">

                        <div class="col-12 col-md-8"><p class="text-white mb-0 text-center text-md-start">© Copyright 2026 | Todos los derechos reservados</p></div>
                        <div class="col-12 col-md-4 text-white text-center text-md-end pb-3 pb-md-0">
                            <a href="https://www.instagram.com/" target="blank" class="footer-a">

                                <i class="bi bi-instagram footer-icons"></i>

                            </a>
                            <a href="https://www.facebook.com" target="blank" class="footer-a">

                                <i class="bi bi-facebook footer-icons"></i>

                            </a>
                            <a href="https://www.whatsapp.com/" target="blank" class="footer-a">

                                <i class="bi bi-whatsapp footer-icons"></i>

                            </a>
                            <a href="https://x.com/" target="blank" class="footer-a">

                                <i class="bi bi-twitter-x footer-icons"></i>

                            </a>
                        </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

    </footer>



    <script src="JavaScript/carrito.js"></script>
</body>
</html>
