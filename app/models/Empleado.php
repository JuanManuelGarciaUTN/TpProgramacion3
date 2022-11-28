<?php

class Empleado
{
    public int $id;
    public string $usuario;
    public string $clave;
    public string $rol;
    public float $salario;
    public bool $estado;

    public function crearEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO personal (usuario, clave, rol, salario) VALUES (:usuario, :clave, :rol, :salario)");
        
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);

        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':salario', $this->salario);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, rol, salario, estado FROM personal");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    public static function obtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave FROM personal WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function borrarEmpleado($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM personal WHERE id = :id");
        $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function verificarEmpleado($clave, $usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, clave, rol FROM personal WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        $datos = $consulta->fetch();

        if($datos)
        {
            $claveHash = $datos["clave"];
            $rol = $datos["rol"];
            $id = $datos["id"];
    
            if(password_verify($clave, $claveHash))
            {
                $identificacion = ["rol"=>$rol, "id"=>$id];
                return $identificacion;
            }
        }

        return false;
    }

    public static function modificarUsuario($id, $usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE personal SET usuario = :usuario WHERE id = :id");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarClave($id, $clave)
    {
        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE personal SET clave = :clave WHERE id = :id");
        $consulta->bindValue(':clave', $claveHash, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarSalario($id, $salario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE personal SET salario = :salario WHERE id = :id");
        $consulta->bindValue(':salario', $salario);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarEstado($id, $estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE personal SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_BOOL);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function Existe($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM personal WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(0);
    }

    public static function GenerarCsv()
    {
        $direccionArchivo = './temporal.csv';
        $archivo = fopen($direccionArchivo, "w");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM personal");
        $consulta->execute();

        fwrite($archivo, "id, usuario, clave, rol, salario, estado\n");
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

                $claveHash = password_hash($datosCsv[1], PASSWORD_DEFAULT);

                $objAccesoDatos = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO personal 
                                                                    (usuario, clave, rol, salario) 
                                                                VALUES 
                                                                    (:usuario, :clave, :rol, :salario)");
                $consulta->bindValue(':usuario', $datosCsv[0], PDO::PARAM_STR);
                $consulta->bindValue(':clave', $claveHash, PDO::PARAM_STR);
                $consulta->bindValue(':rol', $datosCsv[2], PDO::PARAM_STR);
                $consulta->bindValue(':salario', $datosCsv[3], PDO::PARAM_STR);
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