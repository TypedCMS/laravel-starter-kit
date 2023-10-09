<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Swis\JsonApi\Client\Error;
use Swis\JsonApi\Client\Interfaces\DocumentInterface;
use Swis\JsonApi\Client\Interfaces\ItemInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TypedCMS\LaravelStarterKit\Repositories\Concerns\CachesResponses;
use TypedCMS\LaravelStarterKit\Repositories\Concerns\PropagatesCacheClearing;
use TypedCMS\PHPStarterKit\Repositories\Repository as BaseRepository;

abstract class Repository extends BaseRepository
{
    use CachesResponses;
    use PropagatesCacheClearing;

    protected ?string $cachePrefix = null;

    /**
     * When this repository's cache is cleared, repositories listed here will
     * also be cleared.
     *
     * @var array<class-string<Repository>>|array<class-string<Repository>, array<string>>
     */
    protected array $clears = [];

    /**
     * @var array<string>
     */
    protected array $cacheTags = [];

    /**
     * Set expiration to null to cache forever.
     */
    protected ?int $cacheExpiresAfter = 60 * 60 * 24 * 7; //7 days

    /**
     * @param array<string, mixed> $parameters
     */
    public function all(array $parameters = []): DocumentInterface
    {
        $parameters += ['all' => true];

        $key = $this->getCacheKey('all', $parameters);

        return $this->cache($key, fn () => parent::all($parameters));
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return LengthAwarePaginator<ItemInterface>
     */
    public function paginated(array $parameters = []): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        $parameters += ['all' => false, 'page[number]' => $page];

        $key = $this->getCacheKey('paginated', $parameters);

        $document = $this->cache($key, fn () => parent::all($parameters));

        return new LengthAwarePaginator(
            $document->getData(),
            $document->getMeta()['total'],
            $document->getMeta()['perPage'],
            $page,
            ['path' => request()->url()]
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function take(array $parameters = [])
    {
        $key = $this->getCacheKey('take', $parameters);

        return $this->cache($key, fn () => parent::take($parameters));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function find(string $id, array $parameters = []): DocumentInterface
    {
        $key = $this->getCacheKey('find', $parameters, $id);

        return $this->cache($key, fn () => parent::find($id, $parameters));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function findOrFail(string $id, array $parameters = []): DocumentInterface
    {
        $key = $this->getCacheKey('findOrFail', $parameters, $id);

        return $this->cache($key, fn () => parent::findOrFail($id, $parameters));
    }

    protected function handle404Error(DocumentInterface $document): void
    {
        throw new NotFoundHttpException();
    }

    protected function logError(Error $error): void
    {
        Log::error('[' . $error->getStatus() . '] ' . $error->getDetail());
    }
}

