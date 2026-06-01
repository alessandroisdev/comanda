<?php

namespace Tests\Feature;

use Tests\TestCase;

class SseTestControllerTest extends TestCase
{
    /** @test */
    public function it_serves_sse_stream_with_proper_headers()
    {
        $response = $this->get('/sse/test');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->assertHeader('X-Accel-Buffering', 'no');
        $response->assertHeader('Cache-Control', 'no-cache, private');
    }
}
