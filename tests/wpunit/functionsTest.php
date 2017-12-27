<?php

class functionsTest extends \Codeception\TestCase\WPTestCase
{
    use AnsPress\Tests\Testcases\Common;
    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    /**
     * @covers ::ap_get_short_link
     */
    public function testApGetShortLink()
    {
        $id = $this->insert_question();
        $url = ap_get_short_link(['ap_q' => $id]);
        $query = wp_parse_args( wp_parse_url($url)['query'] );
        $this->assertEquals('shortlink', $query['ap_page']);
        $this->go_to(ap_get_short_link(['ap_q' => $id]));
    }
}