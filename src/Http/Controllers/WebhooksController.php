<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Http\Controllers;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Contracts\HandlesWebhook;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Result;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;

use function collect;
use function config;
use function count;
use function response;

abstract class WebhooksController extends Controller
{
    protected string $name;

    /**
     * @var array<class-string<HandlesWebhook>>
     */
    protected array $handlers = [];

    public function __invoke(Request $request, Pipeline $pipeline): JsonResponse
    {
        if (!$this->checkSigningKey($request)) {

            $results = $this->prepareResults([new Result('Invalid signing key.', true)]);

            return response()->json($results, 401);
        }

        $traveler = $pipeline
            ->send(new Traveler($request->all()))
            ->through([
                ...$this->handlers,
                ...config('typedcms.webhook_handlers.'.$this->name, []),
            ])
            ->then(static function (Traveler $traveler): Closure {

                if (count($traveler->getResults()) === 0) {
                    $traveler->addResult('No action required.');
                }

                return static fn (): Traveler => $traveler;
            })();

        $results = $this->prepareResults($traveler->getResults());

        return response()->json($results, $results['status'] === 'success' ? 200 : 503);
    }

    /**
     * @param array<Result> $results
     *
     * @return array<string, mixed>
     */
    protected function prepareResults(array $results): array
    {
        $error = collect($results)
            ->filter(static fn (Result $result): bool => $result->isError())
            ->isNotEmpty();

        $messages = collect($results)
            ->map(static fn (Result $result): string => $result->getMessage())
            ->all();

        return [
            'status' => $error ? 'failure' : 'success',
            'messages' => $messages,
        ];
    }

    protected function checkSigningKey(Request $request): bool
    {
        $secret = config('typedcms.webhook_secrets.' . $this->name);

        $expectedSignature = $this->generateSigningKey($request->getContent(), $secret);

        return $request->header('Signature') === $expectedSignature;
    }

    protected function generateSigningKey(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
}
