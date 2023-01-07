<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/AutentificadorJWT.php';
require_once './models/Pedido.php';

class VerificarSectorMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $token = $request->getHeaderLine("Authorization");

        try{
            $parametros = $request->getParsedBody();

            $idElementoDelPedido = $parametros['id_elemento_del_pedido'];
            $tipoDeProducto = ElementoDelPedido::obtenerSector($idElementoDelPedido);
            
            $data = AutentificadorJWT::ObtenerData($token);
            
            if(!ElementoDelPedido::verificarSector($tipoDeProducto, $data->rol))
            {   
                //si el rol no es valido devuelve 401 e indica que tipo de empleado debe manejar el pedido
                throw new Exception("Rol Invalido.");
            }
            //agrego los datos del usuario al request
            
            $parametros = $request->getParsedBody();
            $parametros["tipo_empleado"] = $data->rol;
            $parametros["id_empleado"] = $data->id;
            $request = $request->withParsedBody($parametros);
            
            $response = $handler->handle($request);

            $contenidoExistente = (string) $response->getBody();
            $response = new Response($response->getStatusCode());
            $response->getBody()->write($contenidoExistente);
        }
        catch (Exception $e){
            $response = new Response(401);
            $payload = array("mensaje"=>"Error de Autentificacion. Rol debe poder manejar ".$tipoDeProducto);
            $payload = json_encode($payload);
            $response->getBody()->write("$payload");
        }    
        finally
        {
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}