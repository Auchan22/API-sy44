<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;


class AuthenticationSuccessListener{

    private $tokenTtl;
    private $secure = false;

    public function __construct($tokenTtl)
    {

        $this->tokenTtl = $tokenTtl;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onAuthSuccess(AuthenticationSuccessEvent $event ){
        $data = $event->getData();
        $response = $event->getResponse();

        //dd($data);

        $token = $data["token"];

        //Creacion de nueva cookie, que va a almacenar el token, con un tiempo de expiracion
        $response->headers->setCookie(
            new Cookie("BEARER", $token, (new \DateTime())->add(new \DateInterval("PT" . $this->tokenTtl . "S")), "/", null, $this->secure )
        );
    }
}