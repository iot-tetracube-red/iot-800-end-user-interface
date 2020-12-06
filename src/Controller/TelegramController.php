<?php

namespace App\Controller;


use App\Entity\Home;
use App\Utils\DictionaryService;
use App\Utils\TelegramService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Update;

class TelegramController extends AbstractController implements TelegramValidatedController
{
    /**
     * @Route("/telegram", name="telegram")
     * @param Request $request
     * @param TelegramService $telegramService
     * @param LoggerInterface $logger
     * @param DictionaryService $dictionaryService
     * @param bool $isUserEnabled
     * @return Response
     */
    public function index(
        TelegramService $telegramService,
        LoggerInterface $logger,
        DictionaryService $dictionaryService,
        bool $isUserEnabled = false
    ): Response {
        if (!$isUserEnabled) {

            return $this->json([]);
        }

        try {
            $bot = new Client($this->getParameter('TELEGRAM_TOKEN'));

            $bot->command(
                'start',
                function ($message) use ($bot, $telegramService) {

                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        '👇',
                        null,
                        false,
                        null,
                        $telegramService->featuresKeyboard()
                    );
                }
            );

            $bot->command(
                'ip',
                function ($message) use ($bot) {
                    $home = $this->getDoctrine()->getRepository(Home::class);
                    /**
                     * @var Home $smart_home
                     */
                    $smart_home = $home->findOneBy([]);
                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        'L\'indirizzo ip del backend è '.$smart_home->getIp(
                        ).' ed è stato impostato in data '.$smart_home->getDateModified()->format('d-m-Y H:i e')
                    );
                }
            );

            $bot->callbackQuery(
                function (CallbackQuery $callbackQuery) use (
                    $bot,
                    $logger,
                    $telegramService,
                    $dictionaryService
                ) {
                    $text = $callbackQuery->getData();
                    $data = explode(' - ', $text);
                    if (!isset($data[0]) || !isset($data[1])) {
                        $bot->answerCallbackQuery($callbackQuery->getId(), 'Non ho riconosciuto il comando');
                    }
                    $featureName = $data[0];
                    $command = $data[1];

                    $logger->info($featureName);
                    $logger->info($command);
                    $resultStatus = null;
                    $result = $telegramService->sendCommand($featureName, $command, $resultStatus);
                    if (true === $result) {
                        $bot->answerCallbackQuery($callbackQuery->getId(), $dictionaryService->getCommandDoneLabel($command, $resultStatus));
                    } else {
                        $bot->answerCallbackQuery($callbackQuery->getId(), '♠️ Ops! Il backend non ha potuto mettersi in contatto con l\'appliance');
                    }
                }
            );

            $bot->on(
                function (Update $update) use ($bot, $telegramService, $dictionaryService) {
                    $message = $update->getMessage();
                    $text = $message->getText();
                    $feature = trim(ltrim($text, '🔸'), ' ');

                    $textResponse = $feature;
                    $keyboard = [];
                    $featureStatus = $telegramService->getFeature($feature);

                    if (!is_null($featureStatus)) {
                        $keyboard = $telegramService->commandKeyboard($feature);
                    }

                    if (empty($keyboard)) {
                        $bot->sendMessage($message->getChat()->getId(), $textResponse);
                    } else {
                        $bot->sendMessage($message->getChat()->getId(), $textResponse, null, false, null, $keyboard);
                    }
                },
                function (Update $update) {
                    $text = $update->getMessage()->getText();
                    if (strpos($text, '🔸') === 0) {
                        return true;
                    }

                    return false;
                }
            );



            // TODO check how to enable a fallback event when other events are not been matched
//            $bot->on(function(Update $update) use ($bot, $logger) {
//                $message = $update->getMessage();
//                $logger->warning('Evento di fallback registrato col seguente messaggio: ' . $message->getText());
//                $bot->sendMessage($message->getChat()->getId(), 'eh?');
//            }, function() {
//                return true;
//            });

            $bot->run();
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }

        return $this->json([]);
    }
}
