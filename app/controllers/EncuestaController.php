<?php
require_once './models/Encuesta.php';

use Slim\Psr7\Response;
use GuzzleHttp\Psr7\LazyOpenStream;

class EncuestaController extends Encuesta
{
    public function CargarEncuesta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id_mesa = $parametros['id_mesa'];
        $id_pedido = $parametros['id_pedido'];
        $nota_restaurante = $parametros['nota_restaurante'];
        $nota_mesa = $parametros['nota_mesa'];
        $nota_comida = $parametros['nota_comida'];
        $nota_mozo = $parametros['nota_mozo'];
        $comentario = $parametros['comentario'];

        if(Encuesta::VerificarDatos($id_mesa, $id_pedido, $nota_comida, $nota_restaurante, $nota_mozo, $nota_mesa, $comentario))
        {
            if(!Encuesta::Existe($id_mesa, $id_pedido))
            {
                $encuesta = new Encuesta();
                $encuesta->id_mesa = $id_mesa;
                $encuesta->id_pedido = $id_pedido;
                $encuesta->nota_restaurante = $nota_restaurante;
                $encuesta->nota_mesa = $nota_mesa;
                $encuesta->nota_comida = $nota_comida;
                $encuesta->nota_mozo = $nota_mozo;
                $encuesta->comentario = $comentario;
    
                $encuesta->Alta();
                
                $payload = json_encode(array("mensaje" => "Encuesta Cargada Con Exito"));
            }
            else
            {
                $response = new Response(400);
                $payload = json_encode(array("mensaje" => "Ya se cargo una encuesta para dicho pedido"));
            }
        }
        else
        {
            $response = new Response(400);
            $payload = json_encode(array("mensaje" => "Datos Invalidos para la encuestas"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function MejoresEncuestas($request, $response, $args)
    {
        $encuestas = Encuesta::TraerMejoresEncuestas();

        $payload = json_encode(array("Mejores encuestas"=>$encuestas));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerCsv($request, $response, $args)
    {
        $csv = Encuesta::GenerarCsv();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=encuestas.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv, 'rb'))));
        
        unlink($csv);

        return $response;
    }
}
