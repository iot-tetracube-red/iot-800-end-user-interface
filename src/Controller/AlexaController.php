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

        if ($alexaRequest instanceof LaunchRequest
            || ($alexaRequest instanceof IntentRequest
                && 'AMAZON.NavigateHomeIntent' === $alexaRequest->intentName
            )) {
            $response->respond('Casa Smart, cosa posso fare?');
            $response->endSession(false);
        } else {
            if (
                $alexaRequest instanceof SessionEndedRequest
                || ($alexaRequest instanceof IntentRequest
                    && ('AMAZON.StopIntent' === $alexaRequest->intentName
                        || 'AMAZON.CancelIntent' === $alexaRequest->intentName)
                )) {
                $response->respond('Ciao ciao');
                $response->endSession(true);
            } else {
                if ($alexaRequest instanceof IntentRequest
                    && 'AMAZON.HelpIntent' === $alexaRequest->intentName
                ) {
                    $response->respond(
                        'Posso accendere, spegnere, aprire e chiudere le cose di casa. Intendi forse T.V.?'
                    );
                    $response->endSession(false);
                } else {
                    if ($alexaRequest instanceof IntentRequest) {
                        $response->endSession(true);
                        $slots = $alexaRequest->slots;
                        $name = '';
                        $deviceName = '';
                        $referenceId = $alexaRequest->user->userId;
                        if (isset($slots['appliance'])) {
                            $name = $slots['appliance'];
                        }
                        if (isset($slots['device'])) {
                            $deviceName = $slots['device'];
                        }
                        $result = $alexaService->sendCommand(
                            $deviceName,
                            $name,
                            $alexaRequest->intentName,
                            $referenceId
                        );
                        $response->respond($result);
                    }
                }
            }
        }

        return $this->json($response->render());
    }
}
