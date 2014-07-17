<?php

namespace Cerad\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction($name = 'Art')
    {
        return $this->render('@CeradApp/index.html.twig', array('name' => $name));
    }
}
