<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Swis\JsonApi\Client\Interfaces\DocumentInterface;
use Swis\JsonApi\Client\Interfaces\ItemInterface;
use Swis\JsonApi\Client\InvalidResponseDocument;
use Swis\JsonApi\Client\Repository as BaseRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TypedCMS\LaravelStarterKit\Repositories\Concerns\CachesResponses;
use TypedCMS\LaravelStarterKit\Repositories\Concerns\DeterminesEndpoint;
use TypedCMS\LaravelStarterKit\Repositories\Concerns\PropagatesCacheClearing;

abstract class Repository extends BaseRepository
{
    use DeterminesEndpoint;
    use CachesResponses;
    use PropagatesCacheClearing;

    /**
     * By default, repositories make requests to the delivery api. Set this to
     * true if you wish to use the management api by default.
     */
    protected bool $mapi = false;

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

        return $this->cache($key, fn () => $this->handleErrors(parent::all($parameters), strict: true));
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return LengthAwarePaginator<ItemInterface>
     */
    public function paginated(array $parameters = []): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        $parameters += ['page[number]' => $page];

        $key = $this->getCacheKey('paginated', $parameters);

        $document = $this->cache($key, fn () => $this->handleErrors(parent::all($parameters), strict: true));

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

        return $this->cache($key, fn () => $this->handleErrors(parent::take($parameters), strict: true));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function find(string $id, array $parameters = []): DocumentInterface
    {
        $key = $this->getCacheKey('find', $parameters, $id);

        return $this->cache($key, fn () => $this->handleErrors(parent::find($id, $parameters)));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function findOrFail(string $id, array $parameters = []): DocumentInterface
    {
        $key = $this->getCacheKey('findOrFail', $parameters, $id);

        return $this->cache($key, fn () => $this->handleErrors(parent::find($id, $parameters), fail: true));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function save(ItemInterface $item, array $parameters = []): DocumentInterface
    {
        $this->mapi();

        return parent::save($item, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function delete(string $id, array $parameters = []): DocumentInterface
    {
        $this->mapi();

        return parent::delete($id, $parameters);
    }

    protected function handleErrors(DocumentInterface $document, bool $fail = false, bool $strict = false): DocumentInterface
    {
        if ($document instanceof InvalidResponseDocument || $document->hasErrors()) {

            if (!$strict && $document->getResponse()->getStatusCode() === 404) {

                if ($fail) {
                    throw new NotFoundHttpException();
                }

                return $document;
            }

            foreach ($document->getErrors() as $error) {
                Log::error('[' . $error->getStatus() . '] ' . $error->getDetail());
            }

            throw new RuntimeException('Errors occurred whilst fetching data from the API. See log.');
        }

        return $document;
    }
}

