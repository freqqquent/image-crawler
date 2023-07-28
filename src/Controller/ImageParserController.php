<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Service\MainService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ImageParserController extends AbstractController
{
    public function __construct(
        private MainService $mainService,
    ) {}

    public function index(Request $request): Response {
        try {
            return $this->renderSuccess($request);
        } catch (Throwable $exception) {
            return $this->renderError($exception);
        }
    }

    private function renderSuccess(Request $request): Response {
        $address = $request->request->get('address');
        $parameters = $this->mainService->getParameters($address);
        return $this->render('success.html.twig', [
            'urls'=>$parameters['urls'],
            'totalImages'=>$parameters['totalImages'],
            'size'=>$parameters['size'],
        ]);
    }

    private function renderError(Throwable $exception): Response {
        return $this->render('error.html.twig', [
            'exception'=>$exception->getMessage()
        ]);
    }
}
