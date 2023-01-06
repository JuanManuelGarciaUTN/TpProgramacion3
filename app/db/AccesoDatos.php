<?php
class AccesoDatos
{
    private static $objAccesoDatos;
    private $objetoPDO;

    private function __construct()
    {
        try {
            $dsn = "mysql:host={$_ENV["HOST"]};dbname={$_ENV["DATABASE"]}";

            $options = array(
          
              PDO::MYSQL_ATTR_SSL_CA => "/etc/ssl/certs/ca-certificates.crt",
              PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
          
            );
          
            $this->objetoPDO = new PDO($dsn, $_ENV["USERNAME"], "pscale_pw_xg1fbKyp5GVpgKDVxwwEmS7xY4Y8uavIT1bXMnNaKTT", $options);          
          
        } catch (PDOException $e) {
            print "Error: " . $e->getMessage();
            die();
        }
    }

    public static function obtenerInstancia()
    {
        if (!isset(self::$objAccesoDatos)){
            self::$objAccesoDatos = new AccesoDatos();
        }
        return self::$objAccesoDatos;
    }

    public function prepararConsulta($sql)
    {
        return $this->objetoPDO->prepare($sql);
    }

    public function obtenerUltimoId()
    {
        return $this->objetoPDO->lastInsertId();
    }

    public function __clone()
    {
        trigger_error('ERROR: La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}
