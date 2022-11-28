<?php
require_once './models/Empleado.php';
require_once "./models/AutentificadorJWT.php";

class LoginController
{
    public function Login($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        $identificacion = Empleado::verificarEmpleado($clave, $usuario);

        if($identificacion)
        {
            $token = AutentificadorJWT::CrearToken($identificacion);
            $token = json_encode($token);
        
            $response->getBody()->write($token);
        
            return $response->withHeader('Content-Type', 'application/json');
        }
        else{
            $payload = json_encode(array("mensaje" => "Error de Autentificacion. Usuario y|o contraseña invalidos"));
            $newResponse = $response->withStatus(401);
            $newResponse->getBody()->write($payload);
            return $newResponse;
        }
    }
}
