<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';

class Encuesta
{
    public $id;
    public $comentario;
    public $nota_mesa;
    public $nota_restaurante;
    public $nota_mozo;
    public $nota_comida;
    public $id_mesa;
    public $id_pedido;

    public function Alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
                                                            encuestas (id_mesa, id_pedido, comentario, nota_mesa, nota_mozo, nota_restaurante, nota_comida) 
                                                        VALUES 
                                                            (:id_mesa, :id_pedido, :comentario, :mesa, :mozo, :restaurante, :comida)");
        
        $consulta->bindValue(':comentario', $this->comentario, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa',$this->id_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':mozo', $this->nota_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':restaurante', $this->nota_restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':comida', $this->nota_comida, PDO::PARAM_INT);
        $consulta->bindValue(':mesa', $this->nota_mesa, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function VerificarDatos($id_mesa, $id_pedido, $nota_comida, $nota_restaurante, $nota_mozo, $nota_mesa, $comentario)
    {
        return Pedido::ExisteIdYCodigoDeMesa($id_pedido, $id_mesa) &&
            $nota_comida > 0 && $nota_comida < 11 &&
            $nota_restaurante > 0 && $nota_restaurante < 11 &&
            $nota_mozo > 0 && $nota_mozo < 11 &&
            $nota_mesa > 0 && $nota_mesa < 11 &&
            strlen($comentario) < 67;
    }

    public static function Existe($id_mesa, $id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM encuestas WHERE id_mesa = :id_mesa AND id_pedido = :id_pedido");
        $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function TraerMejoresEncuestas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas ORDER BY (nota_mozo + nota_restaurante + nota_comida + nota_mesa) DESC LIMIT 3");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function GenerarCsv()
    {
        $direccionArchivo = './temporal.csv';
        $archivo = fopen($direccionArchivo, "w");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas");
        $consulta->execute();

        fwrite($archivo, "id, id_mesa, id_pedido, comentario, nota_mesa, nota_restaurante, nota_comida, nota_mozo\n");
        while ($fila = $consulta->fetch(PDO::FETCH_NUM)) 
        { 
            fputcsv($archivo, $fila); 
        }
        fclose($archivo);

        return $direccionArchivo;
    }
}