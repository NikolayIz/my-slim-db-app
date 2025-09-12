<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Car;
use App\CarRepository;
use App\CarValidator;
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;

$container = new Container();

$container->set(\PDO::class, function () {
    $conn = new \PDO('sqlite:' . __DIR__ . '/../var/database.sqlite');
    $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    return $conn;
});

$initFilePath = __DIR__ . '/../init.sql';
if (file_exists($initFilePath)) {
    $initSql = file_get_contents($initFilePath);
    $container->get(\PDO::class)->exec($initSql);
}

$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

$app->get('/cars', function ($request, $response) {
    $carRepository = $this->get(CarRepository::class);
    $cars = $carRepository->getEntities();

    $messages = $this->get('flash')->getMessages();

    $params = [
        'cars' => $cars,
        'flash' => $messages
      ];

      return $this->get('renderer')->render($response, 'cars/index.phtml', $params);
})->setName('cars.index');

$app->get('/cars/{id}', function ($request, $response, $args) {

})->setName('cars.show');

$app->get('/cars/{id}', function ($request, $response, $args) {
    $carRepository = $this->get(CarRepository::class);
    $id = $args['id'];
    $car = $carRepository->find($id);

    if (is_null($car)) {
        return $response->write('Page not found')->withStatus(404);
    }

    $messages = $this->get('flash')->getMessages();

    $params = [
        'car' => $car,
        'flash' => $messages
    ];

    return $this->get('renderer')->render($response, 'cars/show.phtml', $params);
})->setName('cars.show');

$app->get('/cars/new', function ($request, $response) {
    $params = [
        'car' => new Car(),
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'cars/new.phtml', $params);
})->setName('cars.create');

$app->post('/cars', function ($request, $response) use ($router) {
    $carRepository = $this->get(CarRepository::class);
    $carData = $request->getParsedBodyParam('car');

    $validator = new CarValidator();
    $errors = $validator->validate($carData);

    if (count($errors) === 0) {
        $car = Car::fromArray([$carData['make'], $carData['model']]);
        $carRepository->save($car);
        $this->get('flash')->addMessage('success', 'Car was added successfully');
        return $response->withRedirect($router->urlFor('cars.index'));
    }

    $params = [
        'car' => $carData,
        'errors' => $errors
    ];

    return $this->get('renderer')->render($response->withStatus(422), 'cars/new.phtml', $params);
})->setName('cars.store');
