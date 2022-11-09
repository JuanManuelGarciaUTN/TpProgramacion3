<?php
require_once './models/Empleado.php';
require_once "./models/AutentificadorJWT";

class LoginController
{
    public function Login($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        $rol = Empleado::verificarEmpleado($clave, $usuario);

        if($rol)
        {
            $datos = array("rol"=>$rol);
            $token = AutentificadorJWT::CrearToken($datos);
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
