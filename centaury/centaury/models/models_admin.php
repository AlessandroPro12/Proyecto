<?php
//////////////////////////////////////////////////////////
//  CONEXIÓN Y MODELO DE NEGOCIO PARA SOFTWARE DE VENTAS
//////////////////////////////////////////////////////////

class DBConfig {
    private $host;
    private $user;
    private $pass;
    private $db;
    protected $db_link;
    private $conn = false;
    public $error_message = '';
    private $rowsCant = 0;

    // ====== CONEXIÓN A LA BASE DE DATOS ======
    public function conexion(
        $host = 'db-venta.mysql.database.azure.com',
        $user = 'admin12',
        $pass = 'Alexander22',
        $db   = 'db2'
    ) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db   = $db;
        $this->error_message = "";

        try {
            // Usamos utf8mb4 para acentos y emojis
            $this->db_link = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->pass
            );

            $this->db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db_link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn = true;
            return $this->db_link;

        } catch (PDOException $exception) {
            $this->error_message = $exception->getMessage();
            die("❌ Error de conexión: " . $this->error_message);
        }
    }

    // ====== CONSULTAS SELECT ======
    public function Consultas($Query, $params = []) {
        $this->error_message = "";
        $this->rowsCant = 0;

        try {
            $sql = $this->db_link->prepare($Query);
            $sql->execute($params);
            $records_query = $sql->fetchAll();
            $this->rowsCant = $sql->rowCount();
            return $records_query ?: false;

        } catch (PDOException $exception) {
            $this->error_message = $exception->getMessage();
            return false;
        }
    }

    // ====== CONSULTAS INSERT / UPDATE / DELETE ======
    public function Operaciones($Query, $params = []) {
        $this->error_message = "";

        try {
            $sql = $this->db_link->prepare($Query);
            return $sql->execute($params);
        } catch (PDOException $exception) {
            $this->error_message = $exception->getMessage();
            return false;
        }
    }

    public function numero_de_filas() {
        return $this->rowsCant;
    }

    public function close() {
        if ($this->conn) {
            $this->db_link = null;
            $this->conn = false;
        }
    }
}

//////////////////////////////////////////////////////////
//  CLASE ADMINMODEL (NEGOCIO)
//////////////////////////////////////////////////////////

class AdminModel extends DBConfig {

    /* ========== LOGIN ========== */
    public function login($usuario, $password) {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->db_link->prepare($sql);
        $stmt->execute([':usuario' => $usuario]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $user = $result[0];
            // Acepta contraseña hasheada o, si tienes usuarios viejos, texto plano
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                return $user; // contiene id, cliente_id, rol, etc.
            }
        }
        return false;
    }

    /* ========== REGISTRO DE USUARIOS ========== */
    public function registrarUsuario($usuario, $email, $password, $rol = 'usuario') {
        try {
            // Validaciones básicas
            if (empty($usuario) || empty($email) || empty($password)) {
                throw new Exception("Todos los campos son obligatorios.");
            }

            // Sanitizar
            $usuario = htmlspecialchars(strip_tags($usuario));
            $email   = filter_var($email, FILTER_SANITIZE_EMAIL);

            // Comprobar si el usuario ya existe
            $sqlCheck = "SELECT id FROM usuarios WHERE usuario = :usuario OR email = :email";
            $stmt = $this->db_link->prepare($sqlCheck);
            $stmt->execute([':usuario' => $usuario, ':email' => $email]);
            if ($stmt->rowCount() > 0) {
                throw new Exception("El usuario o correo ya está registrado.");
            }

            // Crear el cliente asociado
            // my_row_id es la PK AUTO_INCREMENT invisible, id puede ser NULL
            $sqlCliente = "INSERT INTO clientes (nombre, telefono, email, direccion, created_at)
                           VALUES (:nombre, '', :email, '', NOW())";
            $stmtCliente = $this->db_link->prepare($sqlCliente);
            $stmtCliente->execute([':nombre' => $usuario, ':email' => $email]);
            $cliente_id = $this->db_link->lastInsertId(); // = clientes.my_row_id

            // Hashear contraseña segura
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insertar usuario con referencia al cliente
            $sqlUsuario = "INSERT INTO usuarios (usuario, email, password, rol, cliente_id)
                           VALUES (:usuario, :email, :password, :rol, :cliente_id)";
            $stmtUsuario = $this->db_link->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':usuario'    => $usuario,
                ':email'      => $email,
                ':password'   => $hash,
                ':rol'        => $rol,
                ':cliente_id' => $cliente_id
            ]);

            return true;

        } catch (Exception $e) {
            $this->error_message = $e->getMessage();
            return false;
        }
    }

    /* ========== CLIENTES ========== */
    public function listarClientes() {
        // Usamos my_row_id como id para la vista
        $sql = "SELECT my_row_id AS id,
                       nombre,
                       telefono,
                       email AS correo,
                       direccion
                FROM clientes
                ORDER BY my_row_id DESC";
        return $this->Consultas($sql);
    }

    public function agregarCliente($nombre, $telefono, $correo, $direccion) {
        // En la tabla la columna se llama "email", no "correo"
        $sql = "INSERT INTO clientes (nombre, telefono, email, direccion)
                VALUES (?, ?, ?, ?)";
        return $this->Operaciones($sql, [$nombre, $telefono, $correo, $direccion]);
    }

    public function actualizarCliente($id, $nombre, $telefono, $correo, $direccion) {
        $sql = "UPDATE clientes
                SET nombre = ?, telefono = ?, email = ?, direccion = ?
                WHERE my_row_id = ?";
        return $this->Operaciones($sql, [$nombre, $telefono, $correo, $direccion, $id]);
    }

    public function eliminarCliente($id) {
        $sql = "DELETE FROM clientes WHERE my_row_id = ?";
        return $this->Operaciones($sql, [$id]);
    }

    /* ========== PRODUCTOS ========== */
    public function listarProductos() {
        return $this->Consultas("SELECT * FROM productos ORDER BY id DESC");
    }

    public function agregarProducto($nombre, $descripcion, $precio, $stock) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock)
                VALUES (?, ?, ?, ?)";
        return $this->Operaciones($sql, [$nombre, $descripcion, $precio, $stock]);
    }

    public function actualizarProducto($id, $nombre, $descripcion, $precio, $stock) {
        $sql = "UPDATE productos
                SET nombre = ?, descripcion = ?, precio = ?, stock = ?
                WHERE id = ?";
        return $this->Operaciones($sql, [$nombre, $descripcion, $precio, $stock, $id]);
    }

    public function eliminarProducto($id) {
        $sql = "DELETE FROM productos WHERE id = ?";
        return $this->Operaciones($sql, [$id]);
    }

    /* ========== VENTAS / PEDIDOS ========== */
    public function registrarVenta($cliente_id, $items) {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        // Pedido principal
        $sql = "INSERT INTO pedidos (cliente_id, total, fecha, estado)
                VALUES (:cliente_id, :total, NOW(), 'pendiente')";
        $stmt = $this->db_link->prepare($sql);
        $stmt->execute(['cliente_id' => $cliente_id, 'total' => $total]);
        $pedido_id = $this->db_link->lastInsertId();

        // Detalle del pedido
        foreach ($items as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $sql = "INSERT INTO pedido_lineas (pedido_id, producto_id, cantidad, precio, subtotal)
                    VALUES (:pedido_id, :producto_id, :cantidad, :precio, :subtotal)";
            $stmt = $this->db_link->prepare($sql);
            $stmt->execute([
                'pedido_id'   => $pedido_id,
                'producto_id' => $item['producto_id'],
                'cantidad'    => $item['cantidad'],
                'precio'      => $item['precio'],
                'subtotal'    => $subtotal
            ]);

            // Actualizar stock
            $this->Operaciones(
                "UPDATE productos SET stock = stock - ? WHERE id = ?",
                [$item['cantidad'], $item['producto_id']]
            );
        }

        return $pedido_id;
    }

    // Pedidos de un usuario (cliente)
    public function listarPedidosUsuario($cliente_id) {
        $sql = "SELECT * FROM pedidos 
                WHERE cliente_id = :cliente_id 
                ORDER BY fecha DESC";
        $stmt = $this->db_link->prepare($sql);
        $stmt->execute(['cliente_id' => $cliente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Pedidos para el panel admin
    public function listarPedidos() {
        $sql = "SELECT p.id,
                       c.nombre AS cliente,
                       p.total,
                       p.fecha,
                       p.estado
                FROM pedidos p
                JOIN clientes c ON p.cliente_id = c.my_row_id
                ORDER BY p.fecha DESC";
        return $this->Consultas($sql);
    }

    public function obtenerDetallePedido($pedido_id) {
        $sql = "SELECT pl.id,
                       pr.nombre,
                       pl.cantidad,
                       pl.precio,
                       pl.subtotal
                FROM pedido_lineas pl
                JOIN productos pr ON pl.producto_id = pr.id
                WHERE pl.pedido_id = ?";
        return $this->Consultas($sql, [$pedido_id]);
    }
}
?>
