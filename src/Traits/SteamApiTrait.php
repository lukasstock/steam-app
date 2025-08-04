<?php

namespace App\Traits;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

trait SteamApiTrait
{

    public function __construct(
       private readonly LoggerInterface $logger
    )
    {
    }

    private function fetchPlayersSummaries(array $steamIds, string $apiKey): array
    {
        $idsString = implode(',', $steamIds);
        $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$apiKey}&steamids={$idsString}";
        $data = $this->fetchSteamApiUrl($url);

        return $data['response']['players'] ?? [];
    }

    private function getTotalPlaytime(string $apiKey, string $steamId): int
    {
        $games = $this->getOwnedGames($apiKey, $steamId);

        $totalPlaytimeForever = 0;
        foreach ($games as $game) {
            $totalPlaytimeForever += $game['playtime_forever'] ?? 0;
        }

        return round($totalPlaytimeForever / 60, 1);
    }

    private function getPlaytimeLast2Weeks(array $recentGames = []): int
    {
        $totalPlaytime2Weeks = 0;

        foreach ($recentGames as $game) {
            $totalPlaytime2Weeks += $game['playtime_2weeks'] ?? 0;
        }

        return round($totalPlaytime2Weeks / 60, 1);
    }

    private function getRecentGames(string $apiKey, string $selectedId): array
    {
        $url = "http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key={$apiKey}&steamid={$selectedId}&format=json";
        $data = $this->fetchSteamApiUrl($url);

        return $data['response']['games'] ?? [];
    }

    private function getPlayerSummary(string $apiKey, string $selectedId): array
    {
        $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$apiKey}&steamids={$selectedId}";
        $data = $this->fetchSteamApiUrl($url);

        return $data['response']['players'][0] ?? [];
    }

    private function fetchFriendsAvatars(array $friendSummaries): array
    {
        $friendAvatars = [];
        foreach ($friendSummaries as $friend) {
            $friendAvatars[$friend['steamid']] = $friend['avatarfull'] ?? '';
        }

        return $friendAvatars;
    }

    private function fetchSteamApiUrl(string $url): array
    {
        $response = file_get_contents($url);

        if ($response === false) {
            throw new \RuntimeException("Steam API request failed for URL: $url");
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new \RuntimeException("Failed to decode JSON response from Steam API.");
        }

        return $data;
    }

    private function fetchSteamStoreGenres(int $appId): array
    {
        $cache = new FilesystemAdapter();
        $cacheKey = "steam_app_genres_{$appId}";

        return $cache->get($cacheKey, function (ItemInterface $item) use ($appId) {
            // Cache the result for 1 day
            $item->expiresAfter(86400); // 60 * 60 * 24
            // Optional: throttle requests to avoid 429 Too Many Requests
            usleep(250_000); // 250ms

            $url = "https://store.steampowered.com/api/appdetails?appids={$appId}";
            $data = $this->fetchSteamApiUrl($url);

            if (!($data[$appId]['success'] ?? false)) {
                return [];
            }

            $genres = $data[$appId]['data']['genres'] ?? [];
            return array_map(fn($genre) => $genre['description'], $genres);
        });
    }

    private function getOwnedGames(string $apiKey,string $steamId): array
    {
        $totalGamesUrl = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=$apiKey&steamid=$steamId&include_appinfo=1&include_played_free_games=1";
        $data = $this->fetchSteamApiUrl($totalGamesUrl);

        return $data['response']['games'] ?? [];
    }
}
