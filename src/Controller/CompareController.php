<?php

namespace App\Controller;

use App\Traits\SteamApiTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompareController extends AbstractController
{
    use SteamApiTrait;

    #[Route('/steam/compare', name: 'steam_compare')]
    public function compare(Request $request): Response
    {
        /** @var string $id1 */
        $id1 = $request->query->get('id1');
        /** @var string $id2 */
        $id2 = $request->query->get('id2');
        /** @var string $apiKey */
        $apiKey = $_ENV['STEAM_API_KEY'] ?? null;

        if (!$apiKey || !$id1 || !$id2) {
            throw new \RuntimeException('Missing required parameters or API key');
        }

        $player1 = $this->getPlayerSummary($apiKey, $id1);
        $player2 = $this->getPlayerSummary($apiKey, $id2);

        $gamesPlayer1 = $this->getRecentGames($apiKey, $id1);
        $gamesPlayer2 = $this->getRecentGames($apiKey, $id2);

        $playtimePlayer1 = $this->getPlaytimeLast2Weeks($gamesPlayer1);
        $playtimePlayer2 = $this->getPlaytimeLast2Weeks($gamesPlayer2);

        $totalPlaytimePlayer1 = $this->getTotalPlaytime($apiKey, $id1);
        $totalPlaytimePlayer2 = $this->getTotalPlaytime($apiKey, $id2);

        return $this->render('steam/compare.html.twig', [
            'player1' => $player1,
            'player2' => $player2,
            'playtime1' => $playtimePlayer1,
            'playtime2' => $playtimePlayer2,
            'totalPlaytime1' => $totalPlaytimePlayer1,
            'totalPlaytime2' => $totalPlaytimePlayer2,
        ]);
    }
}
