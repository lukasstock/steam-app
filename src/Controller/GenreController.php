<?php

namespace App\Controller;

use App\Traits\SteamApiTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GenreController extends AbstractController
{
    use SteamApiTrait;

    #[Route('/steam/genres', name: 'steam_genre')]
    public function index(Request $request): Response
    {
        $apiKey = $_ENV['STEAM_API_KEY'];
        $ownedGames = $this->getOwnedGames($apiKey, '76561198088051125');
        $tagCounts = [];

        foreach ($ownedGames as $game) {
            $appId = $game['appid'];
            $playtimeHours = ($game['playtime_forever'] ?? 0) / 60;
            $genres = $this->fetchSteamStoreGenres($appId);

            foreach ($genres as $genre) {
                $tagCounts[$genre] = ($tagCounts[$genre] ?? 0) + $playtimeHours;
            }
        }

        arsort($tagCounts);
        $topTags = array_slice(array_keys($tagCounts), 0, 10);

        $username = 'Lukas';
        $tagList = implode(', ', $topTags);
        $message = "{$username} prefers games with the tags: {$tagList}";

        return $this->render('steam/genre.html.twig', [
            'message' => $message,
            'topTags' => $topTags,
            'userProfile' => [
                'name' => $username,
                'avatar' => 'https://cdn.akamai.steamstatic.com/steamcommunity/public/images/items/1940340/07a1c1fe7ad0bb4806a013264d68665c0e8f01a1.gif',
                'favoriteGame' => 'Clair Obscur: Expedition 33',
                'favoriteGameId' => '1903340'
            ],
            ]);
    }
}
