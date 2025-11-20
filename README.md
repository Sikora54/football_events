# Football Events Application

Simple application for handling football events - recruitment task.

## Acceptance Criteria

The following business requirements must be met by the solution:

### Core business requirements
- [x] System accurately logs and updates statistics upon receiving a **goal** event, including details such as scorer, assisting player, team, minute, and match ID.
- [x] System accurately logs and updates records upon receiving a **foul** event, including details such as player at fault, affected player, team, match ID, and precise time of the foul.
- [x] All event data is permanently stored and retrievable
- [x] Relevant statistics are calculated and maintained for both event types
- [R] Clients receive information about all events in real-time
- [R] Data integrity is maintained at all times
- [x] Historical data is preserved and accessible
- [R] System can handle high volume of events

### Client communication requirements
- [R] All clients receive event notifications
- [R] Information is delivered in a timely manner
- [R] Communication is reliable and consistent

### Recruitment requirements
- [x] The solution should be provided as a GitHub repository at new branch with at least three meaningful commits
- [x] Some kind of abstraction is allowed to demonstrate the solution over a fully functioning application
- [x] Try to devote no more than 3 hours to solving the problem - anything you don't have time to do can be written as a plan for further action
- [x] Try not to use AI tools. If you do - write down how you use it
- [x] The solution presented is your colleague's PoC and is not a final contract for storing and exchanging data. If you believe the current implementation might be different, please include this in your changes
- [x] You have full responsibility and influence over the final solution; the PoC is just a teaser – show off your skills

## Requirements

- Docker
- Docker Compose

## Installation and Setup

1. Build and run the container:
```bash
docker compose up --build -d
```

2. Build and run the container:
```bash
docker exec -it football_events_app composer install
```

3. The application will be available at: `http://localhost:8000`

## Usage

### Goal Event

Send a POST request with a Goal event:
```bash
curl -X POST http://localhost:8000/event \
  -H "Content-Type: application/json" \
  -d '{ 
        "type": "goal",
        "scorer": "Bukayo Saka",
        "assistant": "Martin Ødegaard",
        "team_id": "arsenal",
        "match_id": "m1",
        "minute": 12
      }'
```

### Foul Event

Send a POST request with a foul event:

```bash
curl -X POST http://localhost:8000/event \
  -H "Content-Type: application/json" \
  -d '{"type": "foul", "player": "William Saliba", "team_id": "arsenal", "match_id": "m1", "minute": 45, "second": 34}'
```

### Example Response

Both events return a similar response structure:

```json
{
  "status": "success",
  "message": "Event saved successfully",
  "event": {
    "type": "foul",
    "timestamp": 1729599123,
    "data": {
      "type": "foul",
      "player": "William Saliba",
      "team_id": "arsenal",
      "match_id": "m1",
      "minute": 45,
      "second": 34
    }
  }
}
```

### Statistics Endpoint

Get team statistics for a specific match:

```bash
curl "http://localhost:8000/statistics?match_id=m1&team_id=arsenal"
```

Get all team statistics for a match:

```bash
curl "http://localhost:8000/statistics?match_id=m1"
```

Example response:
```json
{
  "match_id": "m1",
  "team_id": "arsenal",
  "statistics": {
    "fouls": 2
  }
}
```

Foul events automatically update team statistics (fouls counter) for the specified team in the given match.

## Tests

### PHPUnit Tests

Run PHPUnit tests inside the container:

```bash
docker exec -it football_events_app vendor/bin/phpunit tests
```

Or after entering the container:
```bash
docker exec -it football_events_app bash
vendor/bin/phpunit tests
```

### Codeception API Tests

Run Codeception API tests for comprehensive endpoint testing:

```bash
docker exec -it football_events_app vendor/bin/codecept run Api
```

Run all Codeception tests:

```bash
docker exec -it football_events_app vendor/bin/codecept run
```

### Test Coverage

The project includes:
- **Unit tests** (PHPUnit): Test individual classes and methods
- **API tests** (Codeception): Test HTTP endpoints and responses
- **Integration tests**: Test complete workflows including statistics tracking

## Project Structure

```
.
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── phpunit.xml
├── public/
│   └── index.php          # Application entry point
├── src/
│   ├── EventHandlers/        # Event handling
|   |   ├──EventStrategyInterface
│   │   ├──FoulEventStrategy
│   │   └──GoalEventStrategy
│   │
│   ├── EventHandler.php
│   ├── FileStorage.php       # File storage
│   └── StatisticsManager.php # Statistics management
├── tests/
│   ├── Unit/                 # PHPUnit tests
|   |   └── EventHandlerTest.php
│   ├── Api/                  # Codeception API tests
│   │   ├── EventApiCest.php
│   │   ├── StatisticsApiCest.php
│   │   └── RoutingApiCest.php
│   └── Support/              # Test helpers
└── storage/                  # Files with saved events and statistics
```

## Future work 
- Clients receive information about all events in real-time
    This POC does not include real-time delivery mechanisms. 
    In production systems this would be achieved by:
      - WebSockets
      - message brokers, i.e. Kafka/RabbitMQ
    Current architecture allows plugging in such mechanisms without changing core events processing logic.

- Data integrity is maintained at all times
  Data integrity in this PoC is ensured through: 
    - input validation for each events
    - atomic writes to storage
    - consistent structure of data 
    - API tests covering invalid/edge cases
    - isolated test environment 
  In production systems this would be achieved by:
    - database transactions
    - constraints
    - schema validation

- System can handle high volume of events
  This Poc is not optimized for high-volume traffic, because file-based storage has
  performance limitations.
  In production systems this would be achieved by:
    - async message queues, i.e. Kafka/RabbitMQ
    - background workers for statistics aggregations
    - batching
    - persistent db storage

- All clients receive event notifications
  This could be achieved by implementing WebSockets or message brokers - each event would be pushed
  to all subscribed clients

- Information is delivered in a timely manner
  Real-time transport ensures low-latency delivery. Event processing is separated from HTTP layer, so 
  adding async pipelines does not require changes in event validation or storage

- Communication is reliable and consistent
  In a production system this would be achieved by using durable queues, retries, idempotent consumers and guaranteed ordering