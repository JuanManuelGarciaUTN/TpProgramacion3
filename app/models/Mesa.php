<?php

class Mesa
{
    public string $codigo;
    public string $estado;

    public function alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo) VALUES (:codigo)");
        
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo, estado FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerUno($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo, estado FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    private static function modificar($codigo, $estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();

        if(self::Existe($codigo))
        {
            $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado WHERE codigo = :codigo");
            $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
            $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
    
            return $consulta->execute();
        }
        return false;
    }

    public static function Abrir($codigo)
    {
        $estado = "cerrada";
        $estadoFinal = "con cliente esperando pedido";

        if(self::VerificarEstado($codigo, $estado))
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();

            $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado WHERE codigo = :codigo");
            $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $estadoFinal, PDO::PARAM_STR);
            
            return $consulta->execute();
        }
        else
        {
            return false;
        } 
    }

    public static function Servir($codigo)
    {
        $estado = "con cliente esperando pedido";
        $estadoFinal = "con cliente comiendo";

        if(self::VerificarEstado($codigo, $estado))
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();

            $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado WHERE codigo = :codigo");
            $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $estadoFinal, PDO::PARAM_STR);
            
            return $consulta->execute();
        }
        else
        {
            return false;
        } 
    }

    public static function Cobrar($codigo)
    {
        $estado = "con cliente comiendo";
        $estadoFinal = "con cliente pagando";

        if(self::VerificarEstado($codigo, $estado))
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();

            $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado WHERE codigo = :codigo");
            $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $estadoFinal, PDO::PARAM_STR);
            
            return $consulta->execute();
        }
        else
        {
            return false;
        }
    }

    public static function Cerrar($codigo)
    {
        $estado = "con cliente pagando";
        $estadoFinal = "cerrada";

        if(self::VerificarEstado($codigo, $estado))
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();

            $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado WHERE codigo = :codigo");
            $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $estadoFinal, PDO::PARAM_STR);
            
            return $consulta->execute();
        }
        else
        {
            return false;
        }
    }

    public static function borrar($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function Existe($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function EstaLibre($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo FROM mesas WHERE codigo = :codigo AND estado = 'cerrada'");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    private static function VerificarEstado($codigo, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo FROM mesas WHERE codigo = :codigo AND estado = :estado");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function GenerarCsv()
    {
        $direccionArchivo = './temporal.csv';
        $archivo = fopen($direccionArchivo, "w");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        fwrite($archivo, "codigo, estado\n");
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
                $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo) VALUES (:codigo)");
                $consulta->bindValue(':codigo', $datosCsv[0], PDO::PARAM_STR);
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