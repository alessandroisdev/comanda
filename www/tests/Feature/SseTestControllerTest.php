<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SseTestControllerTest extends TestCase
{
    #[Test]
    public function it_serves_sse_stream_with_proper_headers()
    {
        $response = $this->get('/sse/test');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->assertHeader('X-Accel-Buffering', 'no');
        $response->assertHeader('Cache-Control', 'no-cache, private');
    }
}
