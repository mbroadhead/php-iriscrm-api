<?php

namespace IrisCrm\Tests\Api;

class LeadTest extends TestCase
{
    /**
     * @test
     */
    public function shouldFindUser()
    {
        $expectedArray = [
            'id' => 1,
            'name' => 'Bob',
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('/leads/1')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->find(1));
    }

    /**
     * @return string
     */
    protected function getApiClass()
    {
        return \IrisCrm\Api\Lead::class;
    }
}
