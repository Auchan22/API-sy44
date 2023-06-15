<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractController
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
     * @Route("/default", name="api_ping")
     */
    public function index()
    {
        // Para que no de un error 404  Invalid JSON, se tiene que hacer un post con la info correspondiente, y retorna lo del dump
        dd($this->security->getUser());

        return $this->json(["msg" => "hola"]);
    }
}
