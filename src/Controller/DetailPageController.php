<?php

namespace App\Controller;

use App\Service\SteamApiService;
use App\Traits\SteamApiTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailPageController extends AbstractController
{
    use SteamApiTrait;

    private array $friends = [
        'BestPlayerThatEverLived' => '76561198088051125',
        'Nico' => '76561198042883445',
        'Dave' => '76561197998954046',
        'Beezy' => '76561198157299521',
        'Reudo' => '76561198016847931',
        'Vanessa' => '76561199484311865',
        'Khalil' => '76561198136356576'
    ];

    #[Route('/steam', name: 'steam_index')]
    public function index(Request $request): Response
    {
        $apiKey = $_ENV['STEAM_API_KEY'];
        $selectedId = $request->query->get('id', $_ENV['STEAM_ID']);

        $player = $this->getPlayerSummary($apiKey, $selectedId);
        $recentGames = $this->getRecentGames($apiKey, $selectedId);
        $totalPlaytime2Weeks = $this->getPlaytimeLast2Weeks($recentGames);
        $totalPlayTime = $this->getTotalPlaytime($apiKey, $selectedId);

        $friendSummaries = $this->fetchPlayersSummaries(array_values($this->friends), $apiKey);
        $friendsAvatars = $this->fetchFriendsAvatars($friendSummaries);

        return $this->render('steam/detail.html.twig', [
            'player' => $player,
            'recentGames' => $recentGames,
            'friends' => $this->friends,
            'friendAvatars' => $friendsAvatars,
            'selectedId' => $selectedId,
            'totalPlaytime2Weeks' => $totalPlaytime2Weeks,
            'totalPlaytimeForever' => $totalPlayTime,
        ]);
    }
}
