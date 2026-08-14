<?php

namespace Tests\Unit;

use App\Services\NineRouterService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class NineRouterServiceTest extends TestCase
{
    public function test_it_uses_local_endpoint_without_requiring_a_key(): void
    {
        config()->set('services.ninerouter', ['api_key' => '', 'base_url' => 'http://127.0.0.1:20128/v1', 'model' => 'test-model', 'connect_timeout' => 2, 'timeout' => 5]);
        Http::fake(['127.0.0.1:20128/*' => Http::response([
            'model' => 'test-model',
            'choices' => [['message' => ['content' => '{"summary":"Ringkasan kandidat.","strengths":["Pengalaman relevan"],"gaps":[],"follow_up":[],"disclaimer":"HR"}']]],
        ])]);

        $result = app(NineRouterService::class)->analyze(['position' => 'Designer'], 'candidate_summary');

        $this->assertSame('Ringkasan kandidat.', $result['summary']);
        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:20128/v1/chat/completions' && ! $request->hasHeader('Authorization'));
    }

    public function test_it_sends_a_configured_proxy_key_from_the_backend(): void
    {
        config()->set('services.ninerouter', ['api_key' => 'local-proxy-secret', 'base_url' => 'http://127.0.0.1:20128/v1', 'model' => 'test-model', 'connect_timeout' => 2, 'timeout' => 5]);
        Http::fake(['*' => Http::response(['model' => 'test-model', 'choices' => [['message' => ['content' => '{"summary":"OK","strengths":[],"gaps":[],"follow_up":[],"disclaimer":"HR"}']]]])]);

        app(NineRouterService::class)->analyze([], 'data_review');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer local-proxy-secret'));
    }

    public function test_it_returns_actionable_message_for_unauthorized_proxy_request(): void
    {
        config()->set('services.ninerouter', ['api_key' => '', 'base_url' => 'http://127.0.0.1:20128/v1', 'model' => 'test-model']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'Missing API key']], 401)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NINEROUTER_API_KEY');

        app(NineRouterService::class)->analyze([], 'candidate_summary');
    }
}
