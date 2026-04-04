<?php

namespace Tests\Unit;

use App\Services\VrRuleService;
use Tests\TestCase;

class VrRuleSanityTest extends TestCase
{
    /** @test */
    public function it_evaluates_sterile_breach_correctly()
    {
        $service = new VrRuleService();
        $result = $service->evaluate('sterile_breach', ['item' => 'table']);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals('GMP-ST-01', $result['rule_id']);
        $this->assertEquals('critical', $result['severity']);
    }
}
