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
use TypedCMS\LaravelStarterKit\Repositories\Concerns\TracksCacheInverse;
use TypedCMS\PHPStarterKit\Repositories\Repository as BaseRepository;

use function compact;

abstract class Repository extends BaseRepository
{
    use CachesResponses;

    use PropagatesCacheClearing;

    use TracksCacheInverse;

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
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $headers
     */
    public function all(array $parameters = [], array $headers = []): DocumentInterface
    {
        $parameters += ['all' => true];

        $key = $this->getCacheKey(__FUNCTION__, $parameters);

        $this->inverse($key, __FUNCTION__, compact('parameters', 'headers'));

        return $this->cache($key, fn () => parent::all($parameters, $headers));
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $headers
     *
     * @return LengthAwarePaginator<ItemInterface>
     */
    public function paginated(array $parameters = [], array $headers = []): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        $parameters += ['all' => false, 'page[number]' => $page];

        $key = $this->getCacheKey(__FUNCTION__, $parameters);

        $this->inverse($key, __FUNCTION__, compact('parameters', 'headers'));

        $document = $this->cache($key, fn () => parent::all($parameters, $headers));

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
     * @param array<string, mixed> $headers
     */
    public function take(array $parameters = [], array $headers = []): DocumentInterface
    {
        $key = $this->getCacheKey(__FUNCTION__, $parameters);

        $this->inverse($key, __FUNCTION__, compact('parameters', 'headers'));

        return $this->cache($key, fn () => parent::take($parameters, $headers));
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $headers
     */
    public function find(string $id, array $parameters = [], array $headers = []): DocumentInterface
    {
        $key = $this->getCacheKey(__FUNCTION__, $parameters, $id);

        $this->inverse($key, __FUNCTION__, compact('id', 'parameters', 'headers'));

        return $this->cache($key, fn () => parent::find($id, $parameters, $headers));
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $headers
     */
    public function findOrFail(string $id, array $parameters = [], array $headers = []): DocumentInterface
    {
        $key = $this->getCacheKey(__FUNCTION__, $parameters, $id);

        $this->inverse($key, __FUNCTION__, compact('id', 'parameters', 'headers'));

        return $this->cache($key, fn () => parent::findOrFail($id, $parameters, $headers));
    }

    protected function handle404Error(DocumentInterface $document): void
    {
        throw new NotFoundHttpException();
    }

    protected function logError(Error $error): void
    {
        Log::error('['.$error->getStatus().'] '.$error->getDetail(), [
            'error' => $error,
        ]);
    }
}

