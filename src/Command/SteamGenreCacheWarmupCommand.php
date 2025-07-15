<?php

namespace App\Command;

use App\Traits\SteamApiTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SteamGenreCacheWarmupCommand extends Command
{
    use SteamApiTrait;

    protected static $defaultName = 'app:steam-cache-warmup';

    private CacheItemPoolInterface $cache;
    private string $apiKey;
    private string $steamId;

    public function __construct(CacheItemPoolInterface $cache, string $apiKey, string $steamId)
    {
        parent::__construct();
        $this->cache = $cache;
        $this->apiKey = $apiKey;
        $this->steamId = $steamId;
    }

    protected function configure()
    {
        $this->setName('app:steam-cache-warmup');
        $this->setDescription('Pre-fetch and cache Steam game genres for the given user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Fetching owned games for SteamID {$this->steamId}...");

        $ownedGames = $this->getOwnedGames($this->apiKey, $this->steamId);
        $total = count($ownedGames);
        $output->writeln("Found {$total} games.");

        $i = 0;
        foreach ($ownedGames as $game) {
            $appId = $game['appid'];
            $cacheKey = "steam_genres_{$appId}";

            $item = $this->cache->getItem($cacheKey);
            if (!$item->isHit()) {
                $genres = $this->fetchSteamStoreGenres($appId);
                $item->set($genres);
                $item->expiresAfter(86400); // cache for 1 day
                $this->cache->save($item);
                $output->writeln("Cached genres for appId: {$appId}");
            } else {
                $output->writeln("Genres for appId {$appId} already cached.");
            }

            usleep(200000); // 0.2 seconds
        }

        $output->writeln("Cache warmup done.");

        return Command::SUCCESS;
    }
}
