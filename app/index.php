<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';

require_once './controllers/EmpleadoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/LoginController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/EncuestaController.php';

require_once './middlewares/VerificarEmpleadoMiddleware.php';
require_once './middlewares/VerificarSectorMiddleware.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
//LOGIN|
$app->post('/login', \LoginController::class . ':Login');

//EMPLEADOS| alta, modificacion, consultas y eliminacion de empleados
$app->group('/empleados', function (RouteCollectorProxy $group) {
  $group->post('[/]', \EmpleadoController::class . ':CargarUno');
  $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
  $group->get('/{id}[/]', \EmpleadoController::class . ':TraerUno');
  $group->put('/{id}[/]', \EmpleadoController::class . ':ModificarUno');
  $group->delete('/{id}[/]', \EmpleadoController::class . ':BorrarUno');
})->add(new VerificarEmpleadoMiddleware("socio"));//middleware que verifica el rol socio

//PRODUCTOS|| alta, modificacion, consultas y eliminacion de productos
$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->post('[/]', \ProductoController::class . ':CargarUno')->add(new VerificarEmpleadoMiddleware("socio"));//middleware que verifica el rol socio
  $group->get('[/]', \ProductoController::class . ':TraerTodos')->add(new VerificarEmpleadoMiddleware());
  $group->get('/{id}[/]', \ProductoController::class . ':TraerUno')->add(new VerificarEmpleadoMiddleware());
  $group->put('/{id}[/]', \ProductoController::class . ':ModificarUno')->add(new VerificarEmpleadoMiddleware("socio"));//middleware que verifica el rol socio
  $group->delete('/{id}[/]', \ProductoController::class . ':BorrarUno')->add(new VerificarEmpleadoMiddleware("socio"));//middleware que verifica el rol socio
});

//MESAS|| alta, modificacion, consultas y eliminacion de mesas
$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->post('[/]', \MesaController::class . ':CargarUno')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('[/]', \MesaController::class . ':TraerTodos')->add(new VerificarEmpleadoMiddleware("socio", "mozo"));
  $group->get('/{id}[/]', \MesaController::class . ':TraerUno')->add(new VerificarEmpleadoMiddleware("socio", "mozo"));
  $group->post('/cobrar/{id}[/]', \MesaController::class . ':CobrarMesa')->add(new VerificarEmpleadoMiddleware("mozo"));
  $group->post('/cerrar/{id}[/]', \MesaController::class . ':CerrarMesa')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->delete('/{id}[/]', \MesaController::class . ':BorrarUno')->add(new VerificarEmpleadoMiddleware("socio"));
});

//PEDIDOS|| alta y consultas de pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->post('[/]', \PedidoController::class . ':CargarUno')->add(new VerificarEmpleadoMiddleware("mozo"));
  $group->post('/foto/agregar[/]', \PedidoController::class . ':AgregarFoto')->add(new VerificarEmpleadoMiddleware("mozo"));
  $group->post('/{id_pedido}[/]', \PedidoController::class . ':AgregarProducto')->add(new VerificarEmpleadoMiddleware("mozo"));
  $group->put('[/]', \PedidoController::class . ':ServirPedido')->add(new VerificarEmpleadoMiddleware("mozo"));
  $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(new VerificarEmpleadoMiddleware());
  $group->get('/{id_pedido}/{id_mesa}', \PedidoController::class . ':TraerUno');
  $group->put('/actualizar[/]', \PedidoController::class . ':CambiarEstado')->add(new VerificarSectorMiddleware());
});

//ESTADISTICAS y ENCUENTAS || alta encuestas y consultas de estadisticas
$app->group('/estadisticas', function (RouteCollectorProxy $group) {
  $group->post('/encuesta[/]', \EncuestaController::class . ':CargarEncuesta');
  $group->get('/mesa/mas-usada[/]', \MesaController::class . ':TraerMesaMasUsada')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('/comentarios/mejores[/]', \EncuestaController::class . ':MejoresEncuestas')->add(new VerificarEmpleadoMiddleware("socio"));
});

//CSV || altas desde archivos csv y descargar de archivos csv de la base de datos
$app->group('/csv', function (RouteCollectorProxy $group) {
  $group->post('/pedidos[/]', \PedidoController::class . ':CargarCsv')->add(new VerificarEmpleadoMiddleware("mozo"));
  $group->get('/pedidos[/]', \PedidoController::class . ':ObtenerPedidoCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('/pedidos/productos[/]', \PedidoController::class . ':ObtenerProductosDelPedidoCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->post('/mesas[/]', \MesaController::class . ':CargarCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('/mesas[/]', \MesaController::class . ':ObtenerCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->post('/empleados[/]', \EmpleadoController::class . ':CargarCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('/empleados[/]', \EmpleadoController::class . ':ObtenerCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->post('/productos[/]', \ProductoController::class . ':CargarCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('/productos[/]', \ProductoController::class . ':ObtenerCsv')->add(new VerificarEmpleadoMiddleware("socio"));
  $group->get('/encuestas[/]', \EncuestaController::class . ':ObtenerCsv')->add(new VerificarEmpleadoMiddleware("socio"));
});

$app->run();
