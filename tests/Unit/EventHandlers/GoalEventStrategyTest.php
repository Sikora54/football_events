<?php

namespace Tests\EventHandlers;

use App\EventHandlers\GoalEventStrategy;
use App\StatisticsManager;
use PHPUnit\Framework\TestCase;

class GoalEventStrategyTest extends TestCase
{
    private string $testStatsFile;
    private StatisticsManager $statisticsManager;
    private GoalEventStrategy $strategy;

    protected function setUp(): void
    {
        $this->testStatsFile = sys_get_temp_dir() . '/test_stats_goals_' . uniqid() . '.txt';
        $this->statisticsManager = new StatisticsManager($this->testStatsFile);
        $this->strategy = new GoalEventStrategy($this->statisticsManager);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testStatsFile)) {
            unlink($this->testStatsFile);
        }
    }

    public function testValidateWithValidDataDoesNotThrow(): void
    {
        $data = [
            'player' => 'John Doe',
            'minute' => 12,
            'second' => 30,
        ];

        $this->strategy->validate($data);

        $this->assertTrue(true);
    }

    public function testValidateThrowsExceptionWhenPlayerMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('player is required for goal events');

        $data = [
            'minute' => 10,
            'second' => 20,
        ];

        $this->strategy->validate($data);
    }

    public function testValidateThrowsExceptionWhenMinuteInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('minute must be a non-negative integer for goal events');

        $data = [
            'player' => 'John Doe',
            'minute' => -5,
        ];

        $this->strategy->validate($data);
    }

    public function testUpdateStatsIncrementsGoals(): void
    {
        $data = [
            'match_id' => 'm1',
            'team_id' => 'arsenal',
            'player' => 'John Doe',
        ];

        $this->strategy->updateStats($data);

        $teamStats = $this->statisticsManager->getTeamStatistics('m1', 'arsenal');

        $this->assertArrayHasKey('goals', $teamStats);
        $this->assertEquals(1, $teamStats['goals']);
    }

    public function testUpdateStatsIncrementsAssistsWhenAssistantProvided(): void
    {
        $data = [
            'match_id' => 'm1',
            'team_id' => 'arsenal',
            'player' => 'John Doe',
            'assistant' => 'Jane Smith',
        ];

        $this->strategy->updateStats($data);

        $teamStats = $this->statisticsManager->getTeamStatistics('m1', 'arsenal');

        $this->assertArrayHasKey('assists', $teamStats);
        $this->assertEquals(1, $teamStats['assists']);
    }

    public function testUpdateStatsDoesNothingWhenMatchOrTeamMissing(): void
    {
        $data = [
            'player' => 'John Doe',
        ];

        $this->strategy->updateStats($data);

        $this->assertTrue(true);
    }
}
