<?php

namespace App;

use App\EventHandlers\EventStrategyInterface;
use App\EventHandlers\FoulEventStrategy;
use App\EventHandlers\GoalEventStrategy;

class EventHandler
{
    private FileStorage $storage;
    private StatisticsManager $statisticsManager;

    private array $strategies;

    public function __construct(
        string $storagePath,
        ?StatisticsManager $statisticsManager = null,
        ?array $strategies = null
    ) {
        $this->storage = new FileStorage($storagePath);
        $this->statisticsManager = $statisticsManager ?? new StatisticsManager(__DIR__ . '/../storage/statistics.txt');

        $this->strategies = $strategies ?? [
            'foul' => new FoulEventStrategy($this->statisticsManager),
            'goal' => new GoalEventStrategy($this->statisticsManager),
        ];
    }

    public function handleEvent(array $data): array
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Event type is required');
        }

        $type = $data['type'];

        if (!isset($this->strategies[$type])) {
            throw new \InvalidArgumentException('Unsupported event type: ' . $type);
        }

        $strategy = $this->strategies[$type];

        $strategy->validate($data);
        $strategy->updateStats($data);

        $event = [
            'type' => $type,
            'timestamp' => time(),
            'data' => $data,
        ];

        $this->storage->save($event);

        return [
            'status' => 'success',
            'message' => 'Event saved successfully',
            'event' => $event,
        ];
    }
}
