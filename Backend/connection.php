<?php

// Con esto, mysqli lanza EXCEPCIONES reales (mysqli_sql_exception) en vez de
// simples "warnings" que PHP normalmente imprime en pantalla (y que exponen
// rutas del servidor, versión de MySQL, etc). Gracias a esto, el try/catch
// de abajo sí puede capturar una conexión caída.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function connection(){
    #Definimos estos datos ya que son con los que nos registraremos en PHP my admin
    $host = "localhost";
    $user = "root";
    $pass = "";

    $bd = "reposteria_level_up";

    try {
        # Realizamos la conexión mandándole el host, user, pass y base de datos de una vez.
        $connect = mysqli_connect($host, $user, $pass, $bd);

        // Forzamos utf8mb4: sin esto, nombres con tildes o "ñ" se pueden
        // guardar mal en la base de datos (ej. "Piñata" -> "Pi?ata").
        mysqli_set_charset($connect, "utf8mb4");

        return $connect;

    } catch (mysqli_sql_exception $e) {
        // Si la BD está caída o las credenciales están mal, el usuario NUNCA
        // debe ver el error real de mysqli. Se le muestra algo amigable y se
        // detiene la ejecución de forma controlada (en vez de una pantalla
        // blanca o un error crudo de PHP).
        die('<div style="padding:2rem; font-family:sans-serif; color:#900; background:#fdecea; border-radius:8px; margin:2rem;">
                <strong>No pudimos conectar con la base de datos.</strong><br>
                Por favor, intenta de nuevo en unos minutos.
             </div>');
    }
};

?>
