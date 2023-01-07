<?php
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

use Slim\Psr7\Response;

class EmpleadoController extends Empleado implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $rol = $parametros['rol'];
        $salario = $parametros['salario'];

        if($usuario !== null && $clave !== null && $rol !== null && $salario !== null)
        {
          // Creamos el empleado
          $empleado = new Empleado();
          $empleado->usuario = $usuario;
          $empleado->clave = $clave;
          $empleado->rol = $rol;
          $empleado->salario = $salario;
          $empleado->crearEmpleado();

          $payload = json_encode(array("mensaje" => "Empleado dado de Alta Exitosamente"));
          $response->getBody()->write($payload);

          return $response->withHeader('Content-Type', 'application/json');
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Error, faltan datos necesarios para el alta"));

          $response = new Response(400);
          $response->getBody()->write($payload);
          
          return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos empleado por id
        $empleado = $args['id'];
        $empleado = Empleado::obtenerUno($empleado);
        $payload = json_encode($empleado);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Empleado::obtenerTodos();
        $payload = json_encode(array("listaEmpleados" => $lista));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
      if(isset($args['id']) && Empleado::Existe($args['id']))
      {
        // Buscamos empleado por id
        $id = $args['id'];

        $listaDeModificaciones = [];
        $parametros = $request->getParsedBody();

        if(isset($parametros["usuario"]))
        {
          Empleado::modificarUsuario($id, $parametros["usuario"]);
          $listaDeMoficaciones[] = "Se modifico el usuario";
        }
        if(isset($parametros["clave"]))
        {
          Empleado::modificarClave($id, $parametros["clave"]);
          $listaDeMoficaciones[] = "Se modifico la clave";
        }
        if(isset($parametros["salario"]))
        {
          Empleado::modificarSalario($id, $parametros["salario"]);
          $listaDeMoficaciones[] = "Se modifico el salario";
        }
        if(isset($parametros["estado"]))
        {
          Empleado::modificarEstado($id, $parametros["estado"]);
          $listaDeMoficaciones[] = "Se modifico el estado";
        }
    
        if(count($listaDeMoficaciones) == 0)
        {
          $response = $response->withStatus(400);
          $payload = json_encode(array("mensaje"=>"No se ingresaron parametros para modificar"));
        }
        else
        {
          $payload = array("mensaje"=>"Se modifico correctamente", "lista"=>$listaDeMoficaciones);
          $payload = json_encode($payload);
        }
      }
      else{
        $response = $response->withStatus(400);
        $payload = json_encode(array("mensaje"=>"No hay empleados con dicha id"));
      }
    
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
	}

    public function BorrarUno($request, $response, $args)
    {
        //Eliminamos un empleado por id
        $empleado = $args['id'];
        Empleado::borrarEmpleado($empleado);

        $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerCsv($request, $response, $args)
    {
        $csv = Empleado::GenerarCsv();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=empleados.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv, 'rb'))));

        unlink($csv);

        return $response;
    }

    public function CargarCsv($request, $response, $args)
    {
        if(Empleado::AltaCsv('csv'))
        {
          $payload = json_encode(array("mensaje"=>"Empleados cargados con exito"));
        }
        else
        {
          $response = new Response(400);
          $payload = json_encode(array("mensaje"=>"Archivo invalido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
