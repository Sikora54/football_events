<?php

namespace Tests\Api;

use Tests\Support\ApiTester;


class RoutingApiCest
{
    public function testNotFoundGET(ApiTester $I)
    {
        $I->sendGET('/not-exists');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Not found'
        ]);
    }

    public function testNotFoundPOST(ApiTester $I)
    {
        $I->sendPOST('/also-doesnt-exist', []);
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Not found'
        ]);
    }
}