<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/AutentificadorJWT.php';

class VerificarEmpleadoMiddleware
{
    private $tiposValidos;

    public function __construct($tipoUno = null, $tipoDos = null, $tipoTres = null, $tipoCuatro = null)
    {
        if($tipoUno == null)
        {
            $this->tiposValidos = "todos";
        }
        else
        {
            $this->tiposValidos = [$tipoUno];
            if($tipoDos !== null)
            {
                $this->tiposValidos[] = $tipoDos;

                if($tipoTres !== null)
                {
                    $this->tiposValidos[] = $tipoTres;

                    if($tipoCuatro !== null)
                    {
                        $this->tiposValidos[] = $tipoCuatro;
                    }
                }
            }
        }
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $token = $request->getHeaderLine("Authorization");

        try{
            
            $data = AutentificadorJWT::ObtenerData($token);

            if($this->tiposValidos !== "todos" && !in_array($data->rol, $this->tiposValidos))
            {   
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
            $rolesValidos = implode(", ", $this->tiposValidos);
            $payload = array("mensaje"=>"Error de Autentificacion. Rol debe ser ".$rolesValidos);
            $payload = json_encode($payload);
            $response->getBody()->write("$payload");
        }    
        finally
        {
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}