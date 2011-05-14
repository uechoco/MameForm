<?php
require __DIR__.'/silex.phar';
require_once __DIR__.'/vendor/swiftmailer/lib/swift_required.php';
require_once __DIR__.'/Contact.php';

use Silex\Application;
use Silex\Extension\TwigExtension;
use Silex\Extension\ValidatorExtension;
use Silex\Extension\SwiftmailerExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$app = new Application();
// extension
$app->register(new TwigExtension(), array(
    'twig.path'       => __DIR__.'/views',
    'twig.class_path' => __DIR__.'/vendor/twig/lib',
));
$app->register(new ValidatorExtension(), array(
    'validator.class_path'    => __DIR__.'/vendor',
));
$app->register(new SwiftmailerExtension(), array(
    'swiftmailer.class_path' => __DIR__.'/vendor/swiftmailer/lib',
));
// set your mailer config.
$app['mailer.subject'] = "Email from MameForm";
$app['mailer.address_from'] = "example@example.com";

// entry
$app->get('/', function() use ($app) {
    return $app['twig']->render('entry.twig', array());
});
// send email
$app->post('/', function() use ($app) {
    // validate
    $errors = array();
    // check empty
    $contact = new Contact();
    foreach (array('name', 'email', 'message') as $k) {
        $contact->$k = $app['request']->get($k);
    }
    $errors = $app['validator']->validate($contact);
    // send email
    if (count($errors) === 0) {
        $body = $app['twig']->render('mail.twig');
        $app['mailer']->send($app['mailer']
            ->createMessage()
            ->setFrom($app['request']->get('email'))
            ->addTo($app['mailer.address_from'])
            ->setSubject($app['mailer.subject'])
            ->setBody($body)
        );
        return $app->redirect($app['request']->getBaseUrl() .'/complete');
    }
    return $app['twig']->render('entry.twig', compact('errors'));
});
// complete
$app->get('/complete', function() use($app) {
    return $app['twig']->render('complete.twig');
});
// filter
$app->before(function() use($app){
    // assign request parameters to "request" for Twig templates with shorter name
    $app['twig']->addGlobal('request', $app['request']->request);
});
// error (via. Sismo)
$app->error(function (\Exception $e) use ($app) {
    $error = null;
    if ($e instanceof NotFoundHttpException || in_array($app['request']->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'))) {
        $error = $e->getMessage();
    }
    return new Response(
        $app['twig']->render('error.twig', array('error' => $error)),
        $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500
    );
});
// run app
$app->run();