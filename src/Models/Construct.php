<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Models;

use Closure;
use Swis\JsonApi\Client\Meta;
use TypedCMS\LaravelStarterKit\Models\Resolvers\Contracts\ResolvesModels;
use UnexpectedValueException;

/**
 * @property string $identifier
 */
class Construct extends Model
{
    /**
     * @var string
     */
    protected $type = 'constructs';

    protected string $blueprint;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [], private bool $global = false)
    {
        parent::__construct($attributes);
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function getBlueprint(): string
    {
        return $this->blueprint;
    }

    /**
     * @return $this
     */
    public function setMeta(?Meta $meta): static
    {
        if ($meta === null || !isset($meta['type'])) {
            throw new UnexpectedValueException('Construct meta data must contain a type attribute.');
        }

        $this->blueprint = (string) $meta['type'];

        return parent::setMeta($meta);
    }

    /**
     * @deprecated
     *
     * @return Construct|$this
     */
    public function specialize(): Construct|static
    {
        $type = $this->getMeta()['type'] ?? null;

        if ($type !== null) {

            $model = $this->getResolver()->resolve('constructs:' . $type);

            if ($model instanceof Construct) {
                return $model->hydrateSpecializedModel($this, fn () => [$this->attributes, $this->relations]);
            }
        }

        return $this;
    }

    /**
     * @deprecated
     *
     * @return $this
     */
    public function hydrateSpecializedModel(Construct $construct, Closure $pipe): static
    {
        $this->setId($construct->getId());
        $this->setMeta($construct->getMeta());
        $this->setLinks($construct->getLinks());

        [$this->attributes, $this->relations] = $pipe();

        return $this;
    }

    /**
     * @deprecated
     */
    protected function getResolver(): ResolvesModels
    {
        return app(ResolvesModels::class);
    }
}

