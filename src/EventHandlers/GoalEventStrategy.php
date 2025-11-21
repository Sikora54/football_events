<?php

namespace App\EventHandlers;

use App\StatisticsManager;

class GoalEventStrategy implements EventStrategyInterface
{
    public function __construct(
        private StatisticsManager $statisticsManager
    ) {}

    public function validate(array $data): void
    {
        if (!isset($data['player']) || $data['player'] === '') {
            throw new \InvalidArgumentException('player is required for goal events');
        }

        if (isset($data['minute'])) {
            if (!is_int($data['minute']) || $data['minute'] < 0) {
                throw new \InvalidArgumentException('minute must be a non-negative integer for goal events');
            }
        }
    }

    public function updateStats(array $data): void
    {
        if (empty($data['match_id']) || empty($data['team_id'])) {
            return;
        }

        $matchId = $data['match_id'];
        $teamId  = $data['team_id'];

        $this->statisticsManager->updateTeamStatistics(
            $matchId,
            $teamId,
            'goals'
        );

        if (!empty($data['assistant'])) {
            $this->statisticsManager->updateTeamStatistics(
                $matchId,
                $teamId,
                'assists'
            );
        }
    }
}
