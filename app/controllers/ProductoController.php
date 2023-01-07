<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

use Slim\Psr7\Response;

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $precio = $parametros['precio'];
        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];
        $tiempo_estimado = $parametros['tiempo_estimado'];

        if($precio !== null && $nombre !== null && $tipo !== null)
        {
          // Creamos el usuario
          $producto = new Producto();
          $producto->precio = $precio;
          $producto->nombre = $nombre;
          $producto->tipo = $tipo;
          $producto->tiempo_estimado = $tiempo_estimado;
          $producto->altaProducto();

          $payload = json_encode(array("mensaje" => "Producto dado de Alta Exitosamente"));
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
        // Buscamos producto por id
        $id = $args['id'];
        $usuario = Producto::obtenerUno($id);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
		if(isset($args['id']) && Producto::Existe($args['id']))
		{
		  // Buscamos producto por id
		  $id = $args['id'];
  
		  $listaDeModificaciones = [];
		  $parametros = $request->getParsedBody();
  
		  if(isset($parametros["nombre"]))
		  {
			Producto::modificarNombre($id, $parametros["nombre"]);
			$listaDeMoficaciones[] = "Se modifico el nombre";
		  }
		  if(isset($parametros["precio"]))
		  {
			Producto::modificarPrecio($id, $parametros["precio"]);
			$listaDeMoficaciones[] = "Se modifico el precio";
		  }
		  if(isset($parametros["tiempo_estimado"]))
		  {
			Producto::modificarTiempo($id, $parametros["tiempo_estimado"]);
			$listaDeMoficaciones[] = "Se modifico el tiempo estimado";
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
		  $payload = json_encode(array("mensaje"=>"No hay productos con dicha id"));
		}
	  
		$response->getBody()->write($payload);
		return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        Producto::borrar($id);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerCsv($request, $response, $args)
    {
        $csv = Producto::GenerarCsv();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=productos.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv, 'rb'))));

        unlink($csv);

        return $response;
    }

    public function CargarCsv($request, $response, $args)
    {
        if(Producto::AltaCsv('csv'))
        {
          $payload = json_encode(array("mensaje"=>"Productos cargados con exito"));
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
