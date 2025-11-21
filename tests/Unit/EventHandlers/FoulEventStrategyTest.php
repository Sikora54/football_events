<?php

namespace Tests\EventHandlers;

use App\EventHandlers\FoulEventStrategy;
use App\StatisticsManager;
use PHPUnit\Framework\TestCase;

class FoulEventStrategyTest extends TestCase
{
    private string $testStatsFile;
    private StatisticsManager $statisticsManager;
    private FoulEventStrategy $strategy;

    protected function setUp(): void
    {
        $this->testStatsFile = sys_get_temp_dir() . '/test_stats_fouls_' . uniqid() . '.txt';
        $this->statisticsManager = new StatisticsManager($this->testStatsFile);
        $this->strategy = new FoulEventStrategy($this->statisticsManager);
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
            'match_id' => 'm1',
            'team_id' => 'arsenal',
            'player' => 'name',
            'minute' => 45,
            'second' => 34,
        ];

        $this->strategy->validate($data);

        $this->assertTrue(true);
    }

    public function testValidateThrowsExceptionWhenMatchIdOrTeamIdMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('match_id and team_id are required for foul events');

        $data = [
            // 'match_id' => 'm1',
            // 'team_id' => 'arsenal',
            'player' => 'name',
            'minute' => 45,
            'second' => 34,
        ];

        $this->strategy->validate($data);
    }
    
    public function testValidateThrowsExceptionWhenRequiredFieldMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('player is required for foul events');

        $data = [
            'match_id' => 'm1',
            'team_id' => 'arsenal',
            // 'player' => 'name',
            'minute' => 45,
            'second' => 34,
        ];

        $this->strategy->validate($data);
    }

    public function testUpdateStatsIncrementsFouls(): void
    {
        $data = [
            'match_id' => 'm1',
            'team_id' => 'arsenal',
            'player' => 'name',
            'minute' => 45,
            'second' => 34,
        ];

        $this->strategy->updateStats($data);

        $teamStats = $this->statisticsManager->getTeamStatistics('m1', 'arsenal');

        $this->assertArrayHasKey('fouls', $teamStats);
        $this->assertEquals(1, $teamStats['fouls']);
    }

    public function testUpdateStatsIncrementsFoulsOnMultipleCalls(): void
    {
        $data = [
            'match_id' => 'm1',
            'team_id' => 'arsenal',
            'player' => 'name',
            'minute' => 45,
            'second' => 34,
        ];

        $this->strategy->updateStats($data);
        $this->strategy->updateStats($data);

        $teamStats = $this->statisticsManager->getTeamStatistics('m1', 'arsenal');

        $this->assertArrayHasKey('fouls', $teamStats);
        $this->assertEquals(2, $teamStats['fouls']);
    }

}