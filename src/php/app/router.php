<?php

use Slim\App;
use Twig\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Controller;
use App\Models\User;
use App\Models\Document;



/**
 * Регистрирует маршруты приложения
 *
 * @param App $app
 * @param Environment $twig
 * @param array $commonData
 * @param User $user
 * @return void
 */
function router(App $app, Environment $twig, array $commonData, $user): void {

  $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData, $user) {

    if (isset($_SESSION ["valid_user"])) {

      $documents = $user->documents;

      $html = $twig->render('index.twig', array_merge([
        'pageTitle' => 'Личный кабинет',
        'documents' => $documents
      ], $commonData));
    }else{
      $html = $twig->render('login.twig', array_merge([
        'pageTitle' => 'Вход',
      ], $commonData));
    }

    $response->getBody()->write($html);
    return $response;
  });

  $app->get('/sign-new', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {

    if (isset($_SESSION ["valid_user"])) {
      $html = $twig->render('sign-new.twig', array_merge([
        'pageTitle' => 'Подписать новый документ',
      ], $commonData));

    }else{
      $html = $twig->render('login.twig', array_merge([
        'pageTitle' => 'Вход',
      ], $commonData));
    }

    $response->getBody()->write($html);
    return $response;
  });

  $app->post('/sign-new', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData, $user) {

    $data = $request->getParsedBody();

    $url = $data['url'] ?? null;

    if (!isCorrectFileExtension($url)) {
      $html = $twig->render('sign-new.twig', array_merge([
        'pageTitle' => 'Подписать новый документ',
        'message' => 'Неправильное расширение файла',
      ], $commonData));
      $response->getBody()->write($html);
      return $response;
    }

    $file_name = basename(parse_url($url, PHP_URL_PATH));

    if($user->settings->saveSignedDocuments){

      downloadFile($url, LOCAL_FILES. '/' . $file_name);

    }

    $id = generateUuidV4();
    $document = new Document([
      'id' => $id,
      'name' => $file_name,
      'path' => $url,
      'local_path' => '',
      'hash' => '',
      'signed' => false,
      'signed_at' => date('Y-m-d H:i:s'),
//      'signature' => ["hash_signed"=>hash('sha256', $dataToSign),"ip"=>$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ,"user_agent"=> $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'],
      'signature' => null
    ]);


    $user->addDocument($document);

    $controller = new Controller();
    $controller->save_user_by_email($user->email, $user);

    return $response
      ->withHeader('Location', '/document/' . $id)
      ->withStatus(302);

  });

  $app->get('/signup', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {
    $html = $twig->render('signup.twig', array_merge([
      'pageTitle' => 'Зарегистрироваться',
    ], $commonData));
    $response->getBody()->write($html);
    return $response;
  });

  $app->post('/signup', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {

    $data = $request->getParsedBody();

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $name = $data['name'] ?? null;

    $controller = new Controller();

    $user =$controller->createNewUser($email, $name, $password);

    if($user == null){

      $html = $twig->render('signup.twig', array_merge([
        'pageTitle' => 'Пользователь уже зарегистрирован',
        'message' => 'Пользователь с такой электронной почтой уже зарегистрирован',
      ], $commonData));
      $response->getBody()->write($html);
      return $response;

    }
    $_SESSION ["valid_user"] = $email;

    return $response
      ->withHeader('Location', '/settings')
      ->withStatus(302);
  });



  $app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {
    $html = $twig->render('login.twig', array_merge([
      'pageTitle' => 'Вход',
    ], $commonData));
    $response->getBody()->write($html);
    return $response;
  });

  $app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {

    $data = $request->getParsedBody();

    $email = $data['email'] ?? null;

    $name = $data['name'] ?? null;

    $controller = new Controller();

    $user = $controller->load_user_by_email($email);

    if($user == null){

      $html = $twig->render('login.twig', array_merge([
        'pageTitle' => 'Пользователя не найдено',
        'message' => 'Не правильные электронная почта или пароль',
      ], $commonData));
      $response->getBody()->write($html);
      return $response;

    }

    $_SESSION ["valid_user"] = $email;


    return $response
      ->withHeader('Location', '/')
      ->withStatus(302);
  });


  $app->get('/settings', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {
    if (!isset($_SESSION ["valid_user"])) {
      $html = $twig->render('404.twig', array_merge([
        'pageTitle' => 'Страница не найдена',
      ], $commonData));
      $response->getBody()->write($html);
      return $response;
    }

    $html = $twig->render('settings.twig', array_merge([
      'pageTitle' => 'Настройки',
    ], $commonData));
    $response->getBody()->write($html);
    return $response;
  });


  $app->post('/settings', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData) {
    if (!isset($_SESSION["valid_user"])) {
      $html = $twig->render('404.twig', array_merge([
        'pageTitle' => 'Страница не найдена',
      ], $commonData));
      $response->getBody()->write($html);
      return $response;
    }
    $data = $request->getParsedBody();
    $name = $data['name'] ?? null;
    $password = $data['password'] ?? null;
    $verification = $data['verification'] ?? null;
    $save_signed_documents = $data['save_signed_documents'] == 'on';

    $account = $_SESSION["valid_user"];
    $controller = new Controller();
    $user = $controller->load_user_by_email( $account);
    $user->name = $name;
    if($password){
      $user->passwordHash = getPasswordHash($password);
    }
    $user->settings->verification = $verification;
    $user->settings->saveSignedDocuments = $save_signed_documents;
    $controller->save_user_by_email( $account, $user);

    return $response
      ->withHeader('Location', '/settings')
      ->withStatus(302);
  });


  $app->get('/logout', function (ServerRequestInterface $request, ResponseInterface $response) {

    unset($_SESSION["valid_user"]);
    setcookie("PHPSESSID", "", time() - 3600, "/");
    session_unset();
    session_destroy();
    return $response
      ->withHeader('Location', '/')
      ->withStatus(302);
  });

  $app->get('/user', function (ServerRequestInterface $request, ResponseInterface $response) {

    $controller = new Controller();
    $user = $controller->load_user_by_email('ra3wok@mail.ru');
    $controller->save_user_by_email('ra3wok@mail.ru', $user);
    $payload = json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
  });


  $app->get('/document/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($twig, $commonData, $user) {
    $id = $args['id'];
    $document = $user->findDocumentById($id);
    $document['tempUrl']= $document['local_path'];

    if($user->settings->saveSignedDocuments == false){

      downloadFile($document['path'], LOCAL_FILES. '/temp.pdf');
      $document['tempUrl']= '/local/temp.pdf';
    }


    $html = $twig->render('document.twig', array_merge([
      "document" => $document,
    ], $commonData));

    $response->getBody()->write($html);
    return $response;
  });


  $app->post('/document/{id}', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $commonData, $user) {
    $data = $request->getParsedBody();

    $id = $data['signDocId'] ?? null;
    $document = $user->findDocumentById($id);
    if(!$document){
      $html = $twig->render('404.twig', array_merge([
        'pageTitle' => 'Страница не найдена',
      ], $commonData));
      $response->getBody()->write($html);
      return $response;
    }


    if($user->settings->saveSignedDocuments == false){

      downloadFile($document->path, LOCAL_FILES. '/temp.pdf');

      $dataToSign = file_get_contents(LOCAL_FILES. '/temp.pdf');
    }else{
      $dataToSign = file_get_contents($document['local_path']);
    }
    $document['signed']=true;
    $document['signed_at']=date('Y-m-d H:i:s');
    $document['signature'] = ["hash_signed"=>hash('sha256', $dataToSign),"ip"=>$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ,"user_agent"=> $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'];

    $user->updateDocumentById($id, $document);
    $controller = new Controller();
    $controller->save_user_by_email( $user->email, $user);

    return $response
      ->withHeader('Location', '/document/' . $id)
      ->withStatus(302);
  });
};
