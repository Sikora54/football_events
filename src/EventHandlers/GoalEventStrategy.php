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
        $required = ['scorer', 'team_id', 'match_id', 'minute'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \InvalidArgumentException(sprintf('%s is required for goal events', $field));
            }
        }

        if (!is_int($data['minute']) || $data['minute'] < 0) {
            throw new \InvalidArgumentException('minute must be a non-negative integer for goal events');
        }
    }

    public function updateStats(array $data): void
    {
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
