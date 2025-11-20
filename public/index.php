<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\EventHandler;
use App\StatisticsManager;

header('Content-Type: application/json');

// Simple routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$eventsPath = __DIR__ . '/../storage/events.txt';
$statsPath = __DIR__ . '/../storage/statistics.txt';

$body = null;
$status = 200;

if ($method === 'POST' && $path === '/event') {    
    [$body, $status] = handleEventRequest($eventsPath);
} elseif ($method === 'GET' && $path === '/statistics') {
    [$body, $status] = handleStatisticsRequest($statsPath);
} else {
    $body = ['error' => 'Not found'];
    $status = 404;
}

http_response_code($status);
echo json_encode($body);
exit;




function handleStatisticsRequest(string $statsPath): array
{    
    $matchId = $_GET['match_id'] ?? null;
    $teamId = $_GET['team_id'] ?? null;

    if (!$matchId) {
        return [
            ['error' => 'match_id is required'],
            400,
        ];
    }

    $statsManager = new StatisticsManager($statsPath);

    try {
        if ($teamId) { // Get team statistics for specific match 
            $stats = $statsManager->getTeamStatistics($matchId, $teamId);
            return [[
                'match_id' => $matchId,
                'team_id' => $teamId,
                'statistics' => $stats
            ], 200];
        }   
        // Get all team statistics for specific match    
        $stats = $statsManager->getMatchStatistics($matchId);
        return [[
                'match_id' => $matchId,
                'statistics' => $stats
            ], 200];
         
    } catch (Exception $e) {
        return [
            ['error' => $e->getMessage()],
            500
        ];
    }
} 

function handleEventRequest(string $eventsPath): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            ['error' => 'Invalid JSON'],
            400
        ];
        exit;
    }
    
    $handler = new EventHandler($eventsPath);
    
    try {
        $result = $handler->handleEvent($data);
        return [
            $result, 
            201
        ];
    } catch (Exception $e) {
        return [
            ['error' => $e->getMessage()], 
            400
        ];
    }
}