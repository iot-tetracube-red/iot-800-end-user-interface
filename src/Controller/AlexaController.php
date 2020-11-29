<?php

namespace App\Controller;

use Alexa\Request\IntentRequest;
use Alexa\Request\LaunchRequest;
use Alexa\Request\SessionEndedRequest;
use App\Utils\AlexaService;
use App\Utils\DictionaryService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlexaController extends AbstractController implements AlexaValidatedController
{
    /**
     * @Route("/alexa", name="alexa")
     * @param Request $request
     * @param LoggerInterface $logger
     * @param DictionaryService $dictionaryService
     * @param AlexaService $alexaService
     * @return Response
     */
    public function index(
        Request $request,
        LoggerInterface $logger,
        DictionaryService $dictionaryService,
        AlexaService $alexaService
    ): Response {
        $requestArray = json_decode($request->getContent(), true);
        $alexaRequest = \Alexa\Request\Request::fromData($requestArray);

        $response = new \Alexa\Response\Response;
        $response->respond('Non ho capito niente di quello che mi hai detto, passo e chiudo!');
        $response->endSession(true);

        if ($alexaRequest instanceof LaunchRequest) {
            $response->respond('Casa Smart, cosa posso fare?');
            $response->endSession(false);
        }

        if ($alexaRequest instanceof SessionEndedRequest) {
            $response->respond('Ciao ciao');
            $response->endSession(true);
        }

        if ($alexaRequest instanceof IntentRequest) {
            $response->respond('Ops! Il backend non ha potuto mettersi in contatto con l\'appliance');
            $response->endSession(true);
            $slots = $alexaRequest->slots;
            $name = '';
            $resultStatus = null;
            if (isset($slots['appliance'])) {
                $name = $slots['appliance'];
            }
            $result = $alexaService->sendCommand($name, $alexaRequest->intentName, $resultStatus);
            if (true === $result) {
                $response->respond($dictionaryService->getCommandDoneLabel($alexaRequest->intentName, $resultStatus));
            }

        }

        return $this->json($response->render());
    }
}
