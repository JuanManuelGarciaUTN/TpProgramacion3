<?php
require_once './models/Producto.php';


class ElementoDelPedido extends Producto
{
    public int $id_producto;
    public float $precio;
    public string $codigo_pedido;
    public string $estado;
    public int $id_empleado;

    public function alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO elementos_del_pedido 
                                                            (id_producto, precio, codigo_pedido, tiempo_estimado) 
                                                        VALUES 
                                                            (:id_producto, :precio, :codigo_pedido, :tiempo_estimado)");
        
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':precio',$this->precio);
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->execute();

        self::actualizarTiempoDeEspera($this->tiempo_estimado, $this->codigo_pedido);
        self::cambiarEstadoPedido("esperando pedido", $this->codigo_pedido);

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerPorPedido($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT e.id, p.nombre, e.precio, e.tiempo_estimado, p.tipo, e.estado, e.id_empleado
                                                        FROM elementos_del_pedido AS e
                                                            JOIN productos AS p ON (e.id_producto = p.id)
                                                        WHERE e.codigo_pedido = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);                                      
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerTodosSector($idEmpleado, $sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT e.id, p.nombre, e.precio, e.tiempo_estimado, p.tipo, e.estado, e.id_empleado
                                                        FROM elementos_del_pedido AS e
                                                            JOIN productos AS p ON (e.id_producto = p.id)
                                                        WHERE p.tipo = :sector AND (e.estado = 'en espera' OR e.id_empleado = :idEmpleado) AND e.estado != 'servido'");
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_STR);  
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);                                      
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerSector($idElementoDelPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT productos.tipo FROM elementos_del_pedido
                                                        RIGHT JOIN productos ON elementos_del_pedido.id_producto = productos.id
                                                        WHERE elementos_del_pedido.id = :id");
        $consulta->bindValue(':id', $idElementoDelPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function obtenerPrecioTotal($productos)
    {
        $precio = 0;
        foreach ($productos as $elemento) {
            $precio += $elemento->precio;
        }

        return $precio;
    }

    public static function verificarSector($tipoDeProducto, $tipoDeEmpleado)
    {
        return (($tipoDeProducto == "cocina" && $tipoDeEmpleado == "cocinero") ||
                ($tipoDeProducto == "cerveza" && $tipoDeEmpleado == "cervezero") ||
                ($tipoDeProducto == "bar" && $tipoDeEmpleado == "bartender"));
    }

    public static function VerificarEmpleado($idEmpleado, $idElementoDelPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM elementos_del_pedido WHERE id = :id AND (id_empleado IS NULL OR id_empleado = :id_empleado)");
        $consulta->bindValue(':id', $idElementoDelPedido, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $idEmpleado, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function Servir($codigo_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE elementos_del_pedido SET estado = :estado WHERE codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "servido", PDO::PARAM_STR);

        return $consulta->execute(); 
    }

    public static function EstaEnPreparacion($idElementoDelPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM elementos_del_pedido WHERE id = :id AND estado = 'en preparacion'");
        $consulta->bindValue(':id', $idElementoDelPedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function EmpezarAPreparar($idEmpleado, $idElementoDelPedido, $tiempo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE elementos_del_pedido SET estado = :estado, tiempo_estimado = :tiempo, id_empleado = :id_empleado WHERE id = :id");
        $consulta->bindValue(':id', $idElementoDelPedido, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $idEmpleado, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "en preparacion", PDO::PARAM_STR);
        $consulta->bindValue(':tiempo', $tiempo, PDO::PARAM_INT);
        $consulta->execute();

        $codigo_pedido = self::obtenerCodigoDelPedido($idElementoDelPedido);
        self::actualizarTiempoDeEspera($tiempo, $codigo_pedido);
    }

    public static function MarcarListoParaServir($idElementoDelPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE elementos_del_pedido SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':id', $idElementoDelPedido, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "listo para servir", PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function obtenerCodigoDelPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_pedido FROM elementos_del_pedido WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    private static function actualizarTiempoDeEspera($tiempo, $codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos
                                                        SET tiempo_estimado = :tiempo 
                                                        WHERE 
                                                            (codigo = :codigo) 
                                                            AND 
                                                                (tiempo_estimado <= :tiempoDos 
                                                                OR 
                                                                tiempo_estimado IS NULL)");
        $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo', $tiempo, PDO::PARAM_INT);
        $consulta->bindValue(':tiempoDos', $tiempo, PDO::PARAM_INT);
        $consulta->execute();
    }

    private static function cambiarEstadoPedido($estado, $codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos
                                                        SET estado = :estado 
                                                        WHERE (codigo = :codigo)");
        $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function GenerarCsv()
    {
        $direccionArchivo = './temporal.csv';
        $archivo = fopen($direccionArchivo, "w");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM elementos_del_pedido");
        $consulta->execute();

        fwrite($archivo, "id, id_producto, precio, codigo_pedido, estado, tiempo_estimado, id_empleado\n");
        while ($fila = $consulta->fetch(PDO::FETCH_NUM)) 
        { 
            fputcsv($archivo, $fila); 
        }
        fclose($archivo);

        return $direccionArchivo;
    }

    public static function AgregarAlPedidoCsv($nombreArchivo, $id_pedido)
    {
        try{
            $elementoDelPedido = new ElementoDelPedido();
            $idProducto = null;
            $precio = null;
            $tiempoEstimado = null;

            $direccionTemporal = $_FILES[$nombreArchivo]['tmp_name'];
            $archivo = fopen($direccionTemporal, "r");

            while(!feof($archivo))
            {
                $datosCsv = fgetcsv($archivo);
                $idProducto = $datosCsv[0];

                for ($i=0; $i < $datosCsv[1]; $i++) { 

                    $precio = Producto::ObtenerPrecio($idProducto);
                    $tiempoEstimado = Producto::ObtenerTiempoEstimado($idProducto);
            
                    $elementoDelPedido->id_producto = $idProducto;
                    $elementoDelPedido->precio = $precio;
                    $elementoDelPedido->codigo_pedido = $id_pedido;
                    $elementoDelPedido->tiempo_estimado = $tiempoEstimado;

                    $elementoDelPedido->alta();
                }
            }

            fclose($archivo);
            return true;
        }
        catch(Exception $e)
        {
            echo $e;
            return false;
        }
    }
}