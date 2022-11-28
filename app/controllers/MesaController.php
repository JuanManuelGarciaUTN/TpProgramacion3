<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';

use Slim\Psr7\Response;

class MesaController extends Mesa
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
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Error, faltan datos necesarios para el alta"));

          $response = new Response(400);
          $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos mesa por id
        $id = $args['id'];
        $mesa = Mesa::obtenerUno($id);
        $payload = json_encode($mesa);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));
        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CobrarMesa($request, $response, $args)
    {
        // Buscamos mesa por id
        $id = $args['id'];
        if(Mesa::Existe($id))
        {
          if(Mesa::Cobrar($id))
          {
            $payload = json_encode(array("mensaje" => "Se inicio cobro de mesa ".$id));
          }
          else
          {
            $response = new Response(400);
            $payload = json_encode(array("mensaje" => "Mesa ".$id." no se encuentra en estado posible para cobrar"));
          }
        }
        else
        {
          $response = new Response(400);
          $payload = json_encode(array("mensaje" => "No existe mesa con codigo ".$id));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CerrarMesa($request, $response, $args)
    {
        // Buscamos mesa por id
        $id = $args['id'];
        if(Mesa::Existe($id))
        {
          if(Mesa::Cerrar($id))
          {
            Pedido::Finalizar($id);
            $payload = json_encode(array("mensaje" => "Se cerro la mesa ".$id));
          }
          else
          {
            $response = new Response(400);
            $payload = json_encode(array("mensaje" => "Mesa ".$id." no se encuentra en estado posible para cerrar"));
          }
        }
        else
        {
          $response = new Response(400);
          $payload = json_encode(array("mensaje" => "No existe mesa con codigo ".$id));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        Mesa::borrar($id);

        $payload = json_encode(array("mensaje" => "Mesa borrado con exito"));
        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerMesaMasUsada($request, $response, $args)
    {
        // Buscamos mesa por id
        
        $mesas = Pedido::obtenerMasUsada();
        $payload = json_encode(array("Mesa más usada"=>$mesas));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerCsv($request, $response, $args)
    {
        $csv = Mesa::GenerarCsv();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=mesas.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv, 'rb'))));

        unlink($csv);

        return $response;
    }

    public function CargarCsv($request, $response, $args)
    {
        if(Mesa::AltaCsv('csv'))
        {
          $payload = json_encode(array("mensaje"=>"Mesas cargadas con exito"));
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
