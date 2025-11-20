<?php

namespace App\EventHandlers;

use App\StatisticsManager;

class FoulEventStrategy implements EventStrategyInterface
{
    public function __construct(
        private StatisticsManager $statisticsManager
    ) {}

    public function validate(array $data): void
    {
        if (!isset($data['match_id']) || !isset($data['team_id'])) {
            throw new \InvalidArgumentException('match_id and team_id are required for foul events');
        }

        $required = ['player', 'minute', 'second'];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                throw new \InvalidArgumentException(sprintf('%s is required for foul events', $field));
            }
        }
    }

    public function updateStats(array $data): void
    {
        $this->statisticsManager->updateTeamStatistics(
            $data['match_id'],
            $data['team_id'],
            'fouls'
        );
    }
}
