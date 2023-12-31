<?php

namespace App\Serializer;

use App\Entity\Note;
use App\Entity\Task;
use App\Entity\TaskList;
use Symfony\Component\Routing\RouterInterface;

class CircularReferenceHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke($object)
    {
        switch ($object){
            case $object instanceof TaskList:
                return $this->router->generate("get_list", ["id" => $object->getId()]);

            case $object instanceof Task:
                return $this->router->generate("get_task", ["id" => $object->getId()]);

            case $object instanceof Note:
                return $this->router->generate("get_note", ["id" => $object->getId()]);
        }
        return $object->getId();
    }
}