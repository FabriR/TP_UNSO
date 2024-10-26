<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); // Redirigir al login si no está autenticado
    exit;
}

// Conexión a la base de datos
require '../includes/db.php';

// Definir cuántos registros se mostrarán por página
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Contar el total de registros para calcular el número de páginas
$stmt_count = $pdo->query("SELECT COUNT(*) FROM access_log");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Obtener los registros con límite y desplazamiento (offset)
$stmt = $pdo->prepare("
    SELECT access_log.*, users.username, users.email 
    FROM access_log 
    JOIN users ON access_log.user_id = users.id 
    ORDER BY login_time DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$access_logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand">Sistema de Gestión de Usuarios</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto"></ul>
            <form method="POST" action="../controllers/logout.php" class="form-inline">
                <button type="submit" class="btn btn-danger my-2 my-sm-0">Cerrar sesión</button>
            </form>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Bienvenido al Panel de Administración</h1>
        <h2 class="mt-5">Registro de Accesos</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Hora de Inicio de Sesión</th>
                    <th>Dirección IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($access_logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['id']); ?></td>
                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                    <td><?php echo htmlspecialchars($log['email']); ?></td>
                    <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
    <footer class="text-center mt-5">
        <p>&copy; 2024 Sistema de Gestión de Usuarios. Todos los derechos reservados. Grupo B2</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>











<?php
// Fetch the users and display them along with a delete button (X) for each
$stmt = $pdo->prepare("
    SELECT access_log.*, users.username, users.email 
    FROM access_log 
    JOIN users ON access_log.user_id = users.id 
    ORDER BY login_time DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

echo "<table class='table'>";
echo "<tr><th>Username</th><th>Email</th><th>Login Time</th><th>Action</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['login_time']) . "</td>";
    echo "<td><a href='admin.php?delete=" . htmlspecialchars($row['user_id']) . "' class='btn btn-danger'>X</a></td>";
    echo "</tr>";
}
echo "</table>";
?>


<?php
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Check if the user exists before deleting
    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $stmt_check->execute(['id' => $user_id]);
    if ($stmt_check->rowCount() > 0) {
        // Delete the user
        $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt_delete->execute(['id' => $user_id]);
        echo "<p>User deleted successfully.</p>";
    } else {
        echo "<p>User not found.</p>";
    }
}
?>
