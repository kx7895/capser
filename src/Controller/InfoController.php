<?php /** @noinspection PhpUnused */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InfoController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('app_index');
        else
            return $this->redirectToRoute('login');

//        return $this->home();
    }

    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('app_index');
        else
            return $this->redirectToRoute('login');

//        return $this->render('info/home.html.twig');
    }

    #[Route('/pricing', name: 'pricing')]
    public function pricing(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('app_index');
        else
            return $this->redirectToRoute('login');

//        return $this->render('info/pricing.html.twig');
    }

    #[Route('/features', name: 'features')]
    public function features(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('app_index');
        else
            return $this->redirectToRoute('login');

//        return $this->render('info/features.html.twig');
    }

    #[Route('/imprint', name: 'imprint')]
    public function imprint(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('app_index');
        else
            return $this->redirectToRoute('login');

//        return $this->render('info/imprint.html.twig');
    }

    #[Route('/privacy', name: 'privacy')]
    public function privacy(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('app_index');
        else
            return $this->redirectToRoute('login');

//        return $this->render('info/privacy.html.twig');
    }
}
