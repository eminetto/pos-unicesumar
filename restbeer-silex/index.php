<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

//loader do Composer
$loader = require_once __DIR__.'/vendor/autoload.php';

$db = new PDO('sqlite:beers.db');
$app = new Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$cervejas = array(
    'marcas' => array('Heineken', 'Guinness', 'Skol', 'Colorado'),
    'estilos' => array('Pilsen' , 'Stout')
);

$app->get('/estilo', function () use ($cervejas, $app) {
    return new Response(implode(',', $cervejas['estilos']), 200);
});

$app->get('/cerveja/{id}', function ($id) use ($cervejas, $app) {
    if ($id == null) {
       return new Response(implode(',', $cervejas['marcas']), 200);
    }
    
    $key = array_search($id, $cervejas['marcas']);
    if (!$key) {
        return new Response('Não encontrado', 404);
    }
    return new Response($cervejas['marcas'][$key], 200);

})->value('id', null);

$app->post('/cerveja', function (Request $request) use ($app, $db) {
	$db->exec(
		"create table if not exists beer (id INTEGER PRIMARY KEY AUTOINCREMENT, name text not null, style text not null)"
	);
	if (!$request->get('name') || !$request->get('style')) {
        return new Response('Faltam parâmetros', 400);
    }
    $cerveja = [
    	'name'  => $request->get('name'),
    	'style' => $request->get('style')
    ];
    
    $stmt = $db->prepare('insert into beer (name, style) values (:name, :style)');
    $stmt->bindParam(':name',$cerveja['name']);
    $stmt->bindParam(':style', $cerveja['style']);
    $stmt->execute();
    $cerveja['id'] = $db->lastInsertId();

    return $cerveja;

});

$app->put('/cerveja/{id}', function (Request $request, $id) use ($app) {
    
});

$app->delete('/cerveja/{id}', function (Request $request, $id) use ($app) {
   
});


$app->before(function (Request $request) use ($app) {
    if( ! $request->headers->has('authorization')){
        return new Response('Unauthorized', 401);
    }

    $clients = require_once 'config/clients.php';
    if (!in_array($request->headers->get('authorization'), array_keys($clients))) {
        return new Response('Unauthorized', 401);
    }
});

$app->after(function (Request $request, Response $response) use ($app) {
	$content = explode(',', $response->getContent());

	if ($request->headers->get('accept') == 'text/json') {
		$response->headers->set('Content-Type', 'text/json');
		$response->setContent(json_encode($content));
	}

	if ($request->headers->get('accept') == 'text/html') {
		$content = $app['twig']->render('content.twig', array('content' => $content));
		$response->setContent($content);
	}

	return $response;
});


$app->run();