<?php
require_once './models/ElementoDelPedido.php';
require_once './models/Mesa.php';

class Pedido
{
    public $codigo;
    public $precio_total;
    public $id_mesa;
    public $id_mozo;
    public $estado;
    public $tiempo_estimado = "cliente pidiendo";
    public $fecha_inicio;
    public $fecha_fin;
    public $productos;

    public function alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo, id_mesa, id_mozo, fecha_inicio) VALUES (:codigo, :id_mesa, :id_mozo, :fecha)");
        
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', date("y-m-d H:i"), PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':id_mozo', $this->id_mozo, PDO::PARAM_INT);
        $consulta->execute();

        Mesa::Abrir($this->id_mesa);

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $pedidos = self::ObtenerPedidos();
        foreach ($pedidos as $elemento) {
            $elemento->productos = ElementoDelPedido::obtenerPorPedido($elemento->codigo);
            $elemento->precio_total = ElementoDelPedido::obtenerPrecioTotal($elemento->productos);
        }

        return $pedidos;
    }

    public static function obtenerTodosMozo($id)
    {
        $pedidos = self::ObtenerPedidos($id);
        foreach ($pedidos as $elemento) {
            $elemento->productos = ElementoDelPedido::obtenerPorPedido($elemento->codigo);
            $elemento->precio_total = ElementoDelPedido::obtenerPrecioTotal($elemento->productos);
        }

        return $pedidos;
    }

    private static function ObtenerPedidos($idMozo = null)
    {
        if($idMozo != null)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id_mozo = :id_mozo AND estado != 'finalizado'");
            $consulta->bindValue(':id_mozo', $idMozo, PDO::PARAM_INT);
        }
        else{
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE estado != 'finalizado'");
        }

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPorIdYCodigoDeMesa($codigoPedido, $codigoMesa)
    {
        if(self::ExisteIdYCodigoDeMesa($codigoPedido, $codigoMesa))
        {
            $pedido = self::ObtenerUnPedido($codigoPedido);
            $pedido->productos = ElementoDelPedido::obtenerPorPedido($pedido->codigo);
            $pedido->precio_total = ElementoDelPedido::obtenerPrecioTotal($pedido->productos);

            return $pedido;
        }
        return false;
    }

    private static function ObtenerUnPedido($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function Existe($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function NoFueFinalizado($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo FROM pedidos WHERE codigo = :codigo AND estado != 'finalizado' AND estado != 'finalizado'");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function ExisteIdYCodigoDeMesa($codigoPedido, $codigoMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo FROM pedidos WHERE codigo = :codigo AND id_mesa = :id_mesa");
        $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function VerificarEstadoElementosDelPedido($id)
    {
        $codigoPedido = ElementoDelPedido::obtenerCodigoDelPedido($id);
        $pedidos = ElementoDelPedido::obtenerPorPedido($codigoPedido);

        $listoParaServir = true;
        foreach ($pedidos as $elemento) {
            if($elemento->estado !== "listo para servir")
            {
                $listoParaServir = false;
                break;
            }
        }
        if($listoParaServir)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = 'listo para servir' WHERE codigo = :codigo");
            $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
            $consulta->execute();
        }
    }

    public static function Servir($codigoPedido)
    {
        $id_mesa = self::ListoParaServir($codigoPedido);
        if($id_mesa && Mesa::Servir($id_mesa) && ElementoDelPedido::Servir($codigoPedido))
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = 'servido' WHERE codigo = :codigo");
            $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
            return $consulta->execute();
        }
        return false;
    }

    public static function Finalizar($idMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = 'finalizado', fecha_fin = :fecha WHERE id_mesa = :id_mesa AND estado = 'servido'");
        $consulta->bindValue(':id_mesa', $idMesa, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', date("y-m-d H:i"), PDO::PARAM_STR);
        return $consulta->execute();
    }

    private static function ListoParaServir($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa FROM pedidos WHERE codigo = :codigo AND estado = 'listo para servir'");
        $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function obtenerMasUsada()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa FROM pedidos WHERE estado = 'finalizado' ORDER BY id_mesa");
        $consulta->execute();

        $pedidos = $consulta->fetchAll(PDO::FETCH_COLUMN);

        $mesaMasUsadada = [$pedidos[0]];
        $mesaAnalizadaActualmente = $pedidos[0];
        $cantidadMesaMasUsada = 0;
        $cantidadMesaAnalizadaActualmente = 0;

        foreach ($pedidos as $elemento) {
            if($mesaAnalizadaActualmente == $elemento)
            {
                $cantidadMesaAnalizadaActualmente += 1;
            }
            else
            {
                if($cantidadMesaAnalizadaActualmente == $cantidadMesaMasUsada)
                {
                    $mesaMasUsadada[] = $mesaAnalizadaActualmente;
                }
                else if ($cantidadMesaAnalizadaActualmente > $cantidadMesaMasUsada)
                {
                    $mesaMasUsadada = [$mesaAnalizadaActualmente];
                    $cantidadMesaMasUsada = $cantidadMesaAnalizadaActualmente;
                }
                $cantidadMesaAnalizadaActualmente = 1;
                $mesaAnalizadaActualmente = $elemento;
            }
        }
        if($cantidadMesaAnalizadaActualmente == $cantidadMesaMasUsada)
        {
            $mesaMasUsadada[] = $mesaAnalizadaActualmente;
        }
        else if ($cantidadMesaAnalizadaActualmente > $cantidadMesaMasUsada)
        {
            $mesaMasUsadada = [$mesaAnalizadaActualmente];
            $cantidadMesaMasUsada = $cantidadMesaAnalizadaActualmente;
        }

        if(count($mesaMasUsadada) == 1)
        {
            return $mesaMasUsadada[0];
        }
        return $mesaMasUsadada;
    }

    public static function ObtenerIdMesa($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $id_pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function GenerarCsv()
    {
        $direccionArchivo = './temporal.csv';
        $archivo = fopen($direccionArchivo, "w");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();

        fwrite($archivo, "codigo, id_mesa, estado, id_mozo, tiempo_estimado, fecha_inicio, fecha_fin\n");
        while ($fila = $consulta->fetch(PDO::FETCH_NUM)) 
        { 
            fputcsv($archivo, $fila); 
        }
        fclose($archivo);

        return $direccionArchivo;
    }

    /*public static function AltaCsv($nombreArchivo, $id_pedido, $id_mesa, $id_mozo)
    {
        if(!Pedido::Existe($id_pedido) && Mesa::Existe($id_mesa) && Mesa::EstaLibre($id_mesa))
        {
            try
            {
                $pedido = new Pedido();
                $pedido->codigo = $id_pedido;
                $pedido->id_mesa = $id_mesa;
                $pedido->id_mozo = $id_mozo;

                $pedido->alta();
                return ElementoDelPedido::AgregarAlPedidoCsv($nombreArchivo, $id_pedido);
            }
            catch(Exception $e)
            {
                return false;
            }
        }

        return false;
    }*/
}