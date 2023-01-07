<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';

use Slim\Psr7\Response;

class PedidoController extends Pedido
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        if(isset($parametros['codigo']) && isset($parametros['id_mesa']))
        {
            $codigo = $parametros['codigo'];
            $id_mesa = $parametros['id_mesa'];
            $id_mozo = $parametros['id_empleado'];

            if($codigo !== null && strlen($codigo) == 5 && $id_mesa !== null && strlen($id_mesa) == 5)
            {
                if(!Pedido::Existe($codigo))
                {
                    if(Mesa::Existe($id_mesa))
                    {
                        if(Mesa::EstaLibre($id_mesa))
                        {
                        // Creamos la pedido
                        $pedido = new Pedido();
                        $pedido->codigo = $codigo;
                        $pedido->id_mesa = $id_mesa;
                        $pedido->id_mozo = $id_mozo;
                
                        $pedido->alta();
                
                        $payload = json_encode(array("mensaje" => "Pedido dado de Alta Exitosamente"));
                        $response->getBody()->write($payload);
                        }
                        else
                        {
                            $payload = json_encode(array("mensaje" => "Error, mesa ocupada"));

                            $response = new Response(400);
                            $response->getBody()->write($payload);  
                        }
                    }
                    else
                    {
                        $payload = json_encode(array("mensaje" => "Error, mesa inexistente"));

                        $response = new Response(400);
                        $response->getBody()->write($payload);  
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje" => "Error, ya existe un pedido con codigo ".$codigo));

                    $response = new Response(400);
                    $response->getBody()->write($payload); 
                }
            }
            else
            {
                $payload = json_encode(array("mensaje" => "Error, datos invalidos"));

                $response = new Response(400);
                $response->getBody()->write($payload);
            }
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
        // Buscamos pedido por id
        $idMesa = $args['id_mesa'];
        $idPedido = $args['id_pedido'];
        $pedido = Pedido::obtenerPorIdYCodigoDeMesa($idPedido, $idMesa);
        if($pedido)
        {
            $payload = json_encode($pedido);
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Error, no hay un pedido con dicho codigo de mesa y codigo de pedido"));
            $response = new Response(400);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $tipoEmpleado = $parametros["tipo_empleado"];
        $idEmpleado = $parametros["id_empleado"];

        switch($tipoEmpleado)
        {
            case "socio":
                $lista = Pedido::obtenerTodos();
                break;

            case "mozo":
                $lista = Pedido::obtenerTodosMozo($idEmpleado);
                break;

            case "cocinero":
                $lista = ElementoDelPedido::obtenerTodosSector($idEmpleado, "cocina");
                break;

            case "cervezero":
                $lista = ElementoDelPedido::obtenerTodosSector($idEmpleado, "cerveza");
                break;

            case "bartender":
                $lista = ElementoDelPedido::obtenerTodosSector($idEmpleado, "bar");
                break;
        }
        
        $payload = json_encode(array("listaPedidos" => $lista));
        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AgregarProducto($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $idPedido = $args['id_pedido'];
        $idProducto = $parametros['id_producto'];

        if(Pedido::NoFueFinalizado($idPedido))
        {
            $precio = Producto::ObtenerPrecio($idProducto);
            $tiempoEstimado = Producto::ObtenerTiempoEstimado($idProducto);
    
            $elementoDelPedido = new ElementoDelPedido();
            $elementoDelPedido->id_producto = $idProducto;
            $elementoDelPedido->precio = $precio;
            $elementoDelPedido->codigo_pedido = $idPedido;
            $elementoDelPedido->tiempo_estimado = $tiempoEstimado;
    
            $elementoDelPedido->alta();
    
            $payload = json_encode(array("mensaje" => "Se agrego el producto al pedido ".$idPedido));
        }
        else
        {
            $response = new Response(400);
            $payload = json_encode(array("mensaje" => "El pedido ya fue finalizado ".$idPedido));
        }

        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstado($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id_elemento_del_pedido'];
        $idEmpleado = $parametros['id_empleado'];
        
        if(ElementoDelPedido::VerificarEmpleado($idEmpleado, $id))
        {
            
            if(isset($parametros['tiempo_estimado']))
            {
                $tiempoEstimado = $parametros['tiempo_estimado'];
                ElementoDelPedido::EmpezarAPreparar($idEmpleado, $id, $tiempoEstimado);
                $payload = json_encode(array("mensaje" => "Preparacion del producto Iniciada con exito"));
            }
            else
            {
                if(ElementoDelPedido::EstaEnPreparacion($id))
                {
                    ElementoDelPedido::MarcarListoParaServir($id, $idEmpleado);
                    Pedido::VerificarEstadoElementosDelPedido($id);
                    $payload = json_encode(array("mensaje" => "Producto Listo Para Servir"));
                }
                else
                {
                    $payload = json_encode(array("mensaje" => "Primero debe empezar a preparar el pedido"));
                }
            }
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Pedido ya iniciado por otro empleado"));
            $response = new Response(400);
        }
        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ServirPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigo = $parametros['codigo_pedido'];
        if(Pedido::Servir($codigo))
        {
            $payload = json_encode(array("mensaje" => "Pedido servido con exito"));
        }
        else
        {
            $response = new Response(400);
            $payload = json_encode(array("mensaje" => "El pedido no se encuentra listo para servir"));
        }

        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AgregarFoto($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        if (isset($_FILES['imagen']) && isset($parametros['id_pedido']))
        {
            
            if(self::verificarExtension())
            {
                if (!file_exists("./FotosPedidos"))
                {
                    mkdir("./FotosPedidos");
                }

                $id_pedido = $parametros['id_pedido'];
                $id_mesa = Pedido::ObtenerIdMesa($id_pedido);

                $direccionTemporal = $_FILES["imagen"]["tmp_name"];

                $extension = $_FILES["imagen"]["type"];
                $extension = explode("/", $extension)[1];
    
                $direccionFinal = "./FotosPedidos/".$id_mesa."-".$id_pedido.".".$extension;
                move_uploaded_file($direccionTemporal, $direccionFinal);

                $payload = json_encode(array("mensaje" => "Imagen cargada con exito en ".$direccionFinal));
            }
            else
            {
                $response = new Response(400);
                $payload = json_encode(array("mensaje" => "Extension de imagen invalida. Debe ser .jpeg | .jpg | .png"));
            }
        }
        else
        {
            $response = new Response(400);
            $payload = json_encode(array("mensaje" => "Faltan datos. Debe ingresar [imagen, id_pedido]"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function verificarExtension()
    {
        $extension = $_FILES["imagen"]["type"];
        $extension = explode("/", $extension)[1];
        return $extension == "png" || $extension == "jpeg" || $extension == "jpg";
    }

    public function ObtenerPedidoCsv($request, $response, $args)
    {
        $csv = Pedido::GenerarCsv();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=pedidos.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv, 'rb'))));

        unlink($csv);

        return $response;
    }

    public function ObtenerProductosDelPedidoCsv($request, $response, $args)
    {
        $csv = ElementoDelPedido::GenerarCsv();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=elementos_del_pedido.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv, 'rb'))));

        unlink($csv);

        return $response;
    }

    public function CargarCsv($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_pedido = $parametros['id_pedido'];

        if(ElementoDelPedido::AgregarAlPedidoCsv('csv', $id_pedido))
        {
          $payload = json_encode(array("mensaje"=>"Pedido dado de alta con exito"));
        }
        else
        {
          $response = new Response(400);
          $payload = json_encode(array("mensaje"=>"Datos invalido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
