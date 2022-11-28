<?php

class Producto
{
    public int $id;
    public float $precio;
    public string $nombre;
    public string $tipo;
    public int $tiempo_estimado;

    public function altaProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (precio, nombre, tipo, tiempo_estimado) VALUES (:precio, :nombre, :tipo, :tiempo_estimado)");
        
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, precio, nombre, tipo FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, precio, nombre, tipo FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarNombre($id, $nombre)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET nombre = :nombre WHERE id = :id");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarPrecio($id, $precio)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET precio = :precio WHERE id = :id");
        $consulta->bindValue(':precio', $precio);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarTiempo($id, $tiempo_estimado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET tiempo_estimado = :tiempo_estimado WHERE id = :id");
        $consulta->bindValue(':tiempo_estimado', $tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrar($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function Existe($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function ObtenerPrecio($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT precio FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function ObtenerTiempoEstimado($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT tiempo_estimado FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function GenerarCsv()
    {
        $direccionArchivo = './temporal.csv';
        $archivo = fopen($direccionArchivo, "w");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();

        fwrite($archivo, "id, precio, nombre, tipo, tiempo_estimado\n");
        while ($fila = $consulta->fetch(PDO::FETCH_NUM)) 
        { 
            fputcsv($archivo, $fila); 
        }
        fclose($archivo);

        return $direccionArchivo;
    }

    public static function AltaCsv($nombreArchivo)
    {
        try{
            $direccionTemporal = $_FILES[$nombreArchivo]['tmp_name'];
            $archivo = fopen($direccionTemporal, "r");

            while(!feof($archivo))
            {
                $datosCsv = fgetcsv($archivo);

                $objAccesoDatos = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos 
                                                                    (precio, nombre, tipo, tiempo_estimado) 
                                                                VALUES 
                                                                    (:precio, :nombre, :tipo, :tiempo)");
                $consulta->bindValue(':precio', $datosCsv[0], PDO::PARAM_STR);
                $consulta->bindValue(':nombre', $datosCsv[1], PDO::PARAM_STR);
                $consulta->bindValue(':tipo', $datosCsv[2], PDO::PARAM_STR);
                $consulta->bindValue(':tiempo', $datosCsv[3], PDO::PARAM_STR);
                $consulta->execute();
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