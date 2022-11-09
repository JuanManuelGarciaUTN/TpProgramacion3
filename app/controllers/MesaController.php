<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

use Slim\Psr7\Response;

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo = $parametros['codigo'];

        if($codigo !== null && strlen($codigo) == 5)
        {
          // Creamos la mesa
          $mesa = new Mesa();
          $mesa->codigo = $codigo;

          $mesa->alta();

          $payload = json_encode(array("mensaje" => "Mesa dada de Alta Exitosamente"));
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
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Empleado::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo = $parametros['codigo'];
        $estado = $parametros['estado'];
        
        if(Mesa::modificar($codigo, $estado))
        {
          $payload = json_encode(array("mensaje" => "Estado de Mesa modificado con exito"));
          $response->getBody()->write($payload);
        }
        else{
          $response = $response->withStatus(400);
          $payload = json_encode(array("mensaje" => "Error codigo de mesa Inexistente"));
          $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['usuarioId'];
        Empleado::borrarUsuario($usuarioId);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
