<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractFOSRestController
{

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Rest\Get("/prueba")
     * @return View
     */
    public function show(): View
    {
        dd($_SERVER["APP_ENV"]);
    }

    /**
     * @Rest\Post("login", name="api_login")
     */
    public function index(): View
    {
        // Para que no de un error 404  Invalid JSON, se tiene que hacer un post con la info correspondiente, y retorna lo del dump
        dd($this->security->getUser());

        return $this->json(["msg" => "hola"]);
    }
}
