<?php

namespace App\Models\Traits;

use App\Contracts\Models\IsClosureTable;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use Throwable;

/**
 * Took this from Franzose\ClosureTable to convert to a trait rather than an model that needs to be extended.
 *
 * @see Franzose\ClosureTable\Models\Entity
 */
trait HasClosureTable
{
    /**
     * Cached "previous" (i.e. before the model is moved) direct ancestor id of this model.
     *
     * @var int|null
     */
    private $_previousParentId = null;

    /**
     * Cached "previous" (i.e. before the model is moved) model position.
     *
     * @var int|null
     */
    private $_previousPosition = null;

    /**
     * Whether this node is being moved to another parent node.
     *
     * @var bool
     */
    private $_isMoved = false;

    /**
     * Gets value of the "parent id" attribute.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    abstract public function getClosureTableClass(): string;

    abstract public function closureTableUsesPositions(): bool;

    public function parentId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getAttributeFromArray($this->getParentIdColumn()),
            set: function ($value) {
                $parentId = $this->getParentIdColumn();
                $this->_previousParentId = $this->original[$parentId] ?? null;

                return $value;
            }
        );
    }

    /**
     * Gets the fully qualified "parent id" column.
     *
     * @return string
     */
    public function getQualifiedParentIdColumn(): string
    {
        return $this->getTable() . '.' . $this->getParentIdColumn();
    }

    /**
     * Gets the short name of the "parent id" column.
     *
     * @return string
     */
    public function getParentIdColumn(): string
    {
        return 'parent_id';
    }

    /**
     * Gets the fully qualified "position" column.
     *
     * @return string
     */
    public function getQualifiedPositionColumn(): string
    {
        return $this->getTable() . '.' . $this->getPositionWithinParentColumn();
    }

    /**
     * Gets the short name of the "position" column.
     *
     * @return string
     */
    public function getPositionWithinParentColumn(): string
    {
        return 'position';
    }

    /**
     * Gets the short name of the "position" column.
     *
     * @return int|null
     */
    public function getPositionWithinParentValue(): ?int
    {
        return $this->getAttribute($this->getPositionWithinParentColumn());
    }

    /**
     * Gets the short name of the "position" column.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setPositionWithinParentValue($value): mixed
    {
        $position = $this->getPositionWithinParentColumn();
        $this->_previousPosition = $this->original[$position] ?? null;

        return $this->setAttribute($this->getPositionWithinParentColumn(), $value);
    }

    /**
     * Gets the "children" relation index.
     *
     * @return string
     */
    public function getChildrenRelationIndex(): string
    {
        return 'children';
    }

    public function getClosureDepthColumn(): string
    {
        $closureClass = $this->getClosureTableClass();
        /** @var IsClosureTable */
        $instance = (new $closureClass());

        return $instance->getDepthColumn();
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootHasClosureTable()
    {
        static::saving(static function ($entity) {
            static::handleSavingForClosure($entity);
        });

        // When entity is created, the appropriate
        // data will be put into the closure table.
        static::created(static function ($entity) {
            static::handleCreatedForClosure($entity);
        });

        static::saved(static function ($entity) {
            static::handleSavedForClosure($entity);
        });
    }

    /**
     * @param static $entity
     *
     * @return void
     */
    public static function handleSavingForClosure(self $entity): void
    {
        // Eloquent mixin can't be read from an interface so have to use a model
        /** @var static $entity */
        if ($entity->closureTableUsesPositions() && $entity->isDirty($entity->getPositionWithinParentColumn())) {
            $latest = static::getLatestPosition($entity);

            if (!$entity->_isMoved) {
                $latest--;
            }

            $entity->setPositionWithinParentValue(
                max(0, min($entity->getPositionWithinParentValue(), $latest))
            );
        } elseif ($entity->closureTableUsesPositions() && !$entity->exists) {
            $pos = static::getLatestPosition($entity);
            $entity->setPositionWithinParentValue($pos);
        }
    }

    /**
     * @param static $entity
     *
     * @return void
     */
    public static function handleSavedForClosure(self $entity): void
    {
        /** @var static $entity */
        $parentIdChanged = $entity->isDirty($entity->getParentIdColumn());

        if ($entity->closureTableUsesPositions() && ($parentIdChanged || $entity->isDirty($entity->getPositionWithinParentColumn()))) {
            $entity->reorderSiblings();
        }

        // Eloquent mixin can't be read from an interface/trait so have to use this dummy class
        /** @var IsClosureTable */
        $pivotTable = $entity->getClosureTableClass();
        $closure = (new $pivotTable());
        if ($closure->ancestor === null) {
            $primaryKey = $entity->getKey();
            $closure->ancestor = $primaryKey;
            $closure->descendant = $primaryKey;
            $closure->depth = 0;
        }

        if ($parentIdChanged) {
            $closure->moveNodeTo($entity->parent_id);
        }
    }

    /**
     * @param static $entity
     *
     * @return void
     */
    public static function handleCreatedForClosure(self $entity): void
    {
        // Eloquent mixin can't be read from an interface/trait so have to use dummy class
        /** @var static $entity */
        $entity->_previousParentId = null;
        $entity->_previousPosition = null;

        $descendant = $entity->getKey();
        $ancestor = $entity->parent_id ?? $descendant;

        // Eloquent mixin can't be read from an interface/trait so have to use dummy class
        /** @var IsClosureTable */
        $pivotTable = $entity->getClosureTableClass();
        (new $pivotTable())->insertNode($ancestor, $descendant);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    public function descendantsWithSelf(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->descendantsWithSelfUnordered()
            ->orderByPivot($this->getClosureDepthColumn());
    }

    public function ancestorsWithSelf(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->ancestorsWithSelfUnordered()
            ->orderByPivot($this->getClosureDepthColumn(), 'desc');
    }

    public function descendantsWithSelfUnordered(): BelongsToMany
    {
        $closureClass = $this->getClosureTableClass();
        /** @var IsClosureTable */
        $instance = (new $closureClass());

        /** @var BelongsToMany */
        return $this->belongsToMany(get_class($this), $instance->getTable(), $instance->getAncestorColumn(), $instance->getDescendantColumn())
            ->using($closureClass);
    }

    public function ancestorsWithSelfUnordered(): BelongsToMany
    {
        $closureClass = $this->getClosureTableClass();
        /** @var IsClosureTable */
        $instance = (new $closureClass());

        /** @var BelongsToMany */
        return $this->belongsToMany(get_class($this), $instance->getTable(), $instance->getDescendantColumn(), $instance->getAncestorColumn())
            ->using($closureClass);
    }

    public function descendants(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->descendantsWithSelf()
            ->wherePivot($this->getClosureDepthColumn(), '>', 0);
    }

    public function ancestors(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->ancestorsWithSelf()
            ->wherePivot($this->getClosureDepthColumn(), '>', 0);
    }

    public function descendantsUnordered(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->descendantsWithSelfUnordered()
            ->wherePivot($this->getClosureDepthColumn(), '>', 0);
    }

    public function ancestorsUnordered(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->ancestorsWithSelfUnordered()
            ->wherePivot($this->getClosureDepthColumn(), '>', 0);
    }

    /**
     * Returns one-to-many relationship to child nodes.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(get_class($this), $this->getParentIdColumn());
    }

    /**
     * Returns many-to-one relationship to the direct ancestor.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(get_class($this), $this->getParentIdColumn());
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Returns a number of model's ancestors.
     *
     * @return int
     */
    public function countAncestors()
    {
        return $this->ancestors()->count();
    }

    /**
     * Indicates whether a model has ancestors.
     *
     * @return bool
     */
    public function hasAncestors()
    {
        return (bool) $this->countAncestors();
    }

    /**
     * Returns a number of model's descendants.
     *
     * @return int
     */
    public function countDescendants()
    {
        return $this->descendants()->count();
    }

    /**
     * Indicates whether a model has descendants.
     *
     * @return bool
     */
    public function hasDescendants()
    {
        return (bool) $this->countDescendants();
    }

    /**
     * Returns a number of model's children.
     *
     * @return int
     */
    public function countChildren()
    {
        return $this->children()->count();
    }

    /**
     *  Indicates whether a model has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return (bool) $this->countChildren();
    }

    /**
     * Returns query builder for child nodes.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeChildNode(Builder $builder)
    {
        return $this->scopeChildNodeOf($builder, $this->getKey());
    }

    /**
     * Returns query builder for child nodes of the node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeChildNodeOf(Builder $builder, $id)
    {
        $parentId = $this->getParentIdColumn();

        /** @var Builder */
        return $builder
            ->whereNotNull($parentId)
            ->where($parentId, '=', $id);
    }

    /**
     * Returns query builder for a child at the given position.
     *
     * @param Builder $builder
     * @param int     $position
     *
     * @return Builder
     */
    public function scopeChildAt(Builder $builder, $position)
    {
        /** @var Builder */
        return $this
            ->scopeChildNode($builder)
            ->where($this->getPositionWithinParentColumn(), '=', $position);
    }

    /**
     * Returns query builder for a child at the given position of the node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     * @param int     $position
     *
     * @return Builder
     */
    public function scopeChildOf(Builder $builder, $id, $position)
    {
        /** @var Builder */
        return $this
            ->scopeChildNodeOf($builder, $id)
            ->where($this->getPositionWithinParentColumn(), '=', $position);
    }

    /**
     * Retrieves a child with given position.
     *
     * @param int           $position
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getChildAt($position, array $columns = ['*'])
    {
        /** @var static|null */
        return $this->childAt($position)->first($columns);
    }

    /**
     * Returns query builder for the first child node.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeFirstChild(Builder $builder)
    {
        /** @var Builder */
        return $this->scopeChildAt($builder, 0);
    }

    /**
     * Returns query builder for the first child node of the node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeFirstChildOf(Builder $builder, $id)
    {
        /** @var Builder */
        return $this->scopeChildOf($builder, $id, 0);
    }

    /**
     * Retrieves the first child.
     *
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getFirstChild(array $columns = ['*'])
    {
        return $this->getChildAt(0, $columns);
    }

    /**
     * Returns query builder for the last child node.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeLastChild(Builder $builder)
    {
        /** @var Builder */
        return $this->scopeChildNode($builder)->orderByDesc($this->getPositionWithinParentColumn());
    }

    /**
     * Returns query builder for the last child node of the node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeLastChildOf(Builder $builder, $id)
    {
        /** @var Builder */
        return $this->scopeChildNodeOf($builder, $id)->orderByDesc($this->getPositionWithinParentColumn());
    }

    /**
     * Retrieves the last child.
     *
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getLastChild(array $columns = ['*'])
    {
        /** @var static|null */
        return $this->lastChild()->first($columns);
    }

    /**
     * Returns query builder to child nodes in the range of the given positions.
     *
     * @param Builder  $builder
     * @param int      $from
     * @param int|null $to
     *
     * @return Builder
     */
    public function scopeChildrenRange(Builder $builder, $from, $to = null)
    {
        $position = $this->getPositionWithinParentColumn();
        $query = $this->scopeChildNode($builder)->where($position, '>=', $from);

        if ($to !== null) {
            $query->where($position, '<=', $to);
        }

        return $query;
    }

    /**
     * Returns query builder to child nodes in the range of the given positions for the node of the given ID.
     *
     * @param Builder  $builder
     * @param mixed    $id
     * @param int      $from
     * @param int|null $to
     *
     * @return Builder
     */
    public function scopeChildrenRangeOf(Builder $builder, $id, $from, $to = null)
    {
        $position = $this->getPositionWithinParentColumn();
        $query = $this->scopeChildNodeOf($builder, $id)->where($position, '>=', $from);

        if ($to !== null) {
            $query->where($position, '<=', $to);
        }

        return $query;
    }

    /**
     * Retrieves children within given positions range.
     *
     * @param int           $from
     * @param int           $to
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public function getChildrenRange($from, $to = null, array $columns = ['*'])
    {
        /** @var Collection<static> */
        return $this->childrenRange($from, $to)->get($columns);
    }

    /**
     * Appends a child to the model.
     *
     * @param static $child
     * @param int    $position
     * @param bool   $returnChild
     *
     * @return static
     */
    public function addChild($child, $position = null, $returnChild = false)
    {
        if ($this->exists) {
            $position = $position ?? $this->getLatestChildPosition();

            $child->moveTo($position, $this);
        }

        return $returnChild === true ? $child : $this;
    }

    /**
     * Returns the latest child position.
     *
     * @return int
     */
    private function getLatestChildPosition(): int
    {
        /** @var static|null */
        $lastChild = $this->lastChild()->first([$this->getPositionWithinParentColumn()]);

        return $lastChild !== null ? $lastChild->getPositionWithinParentValue() + 1 : 0;
    }

    /**
     * Appends a collection of children to the model.
     *
     * @param static[] $children
     * @param int      $from
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     *
     * @return static
     */
    public function addChildren(array $children, $from = null)
    {
        if (!$this->exists) {
            return $this;
        }

        $this->transactional(function () use (&$from, $children) {
            foreach ($children as $child) {
                $this->addChild($child, $from);
                $from++;
            }
        });

        return $this;
    }

    /**
     * Appends the given entity to the children relation.
     *
     * @param static $entity
     *
     * @return void
     */
    public function appendChild($entity): void
    {
        $this->getChildrenRelation()->add($entity);
    }

    /**
     * @return Collection<static>
     */
    private function getChildrenRelation()
    {
        $relationName = $this->getChildrenRelation();
        if (!$this->relationLoaded($relationName)) {
            $this->setRelation($relationName, $this->newCollection());
        }

        return $this->getRelation($relationName);
    }

    /**
     * Removes a model's child with given position.
     *
     * @param int|null $position
     * @param bool     $forceDelete
     *
     * @throws Throwable
     *
     * @return static
     */
    public function removeChild($position = null, $forceDelete = false)
    {
        if (!$this->exists) {
            return $this;
        }

        $child = $this->getChildAt($position ?? 0, [
            $this->getKeyName(),
            $this->getParentIdColumn(),
            $this->getPositionWithinParentColumn(),
        ]);

        if ($child === null) {
            return $this;
        }

        $this->transactional(function () use ($child, $forceDelete) {
            $action = ($forceDelete === true ? 'forceDelete' : 'delete');

            $child->{$action}();

            $child->nextSiblings()->decrement($this->getPositionWithinParentColumn());
        });

        return $this;
    }

    /**
     * Removes model's children within a range of positions.
     *
     * @param int      $from
     * @param int|null $to
     * @param bool     $forceDelete
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     *
     * @return static
     */
    public function removeChildren(int $from, ?int $to = null, $forceDelete = false)
    {
        if (!$this->exists) {
            return $this;
        }

        $this->transactional(function () use ($from, $to, $forceDelete) {
            $action = ($forceDelete === true ? 'forceDelete' : 'delete');

            $this->childrenRange($from, $to)->{$action}();

            if ($to !== null) {
                $this
                    ->childrenRange($to)
                    ->decrement($this->getPositionWithinParentColumn(), $to - $from + 1);
            }
        });

        return $this;
    }

    /**
     * Returns sibling query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeSibling(Builder $builder)
    {
        return $builder->where($this->getParentIdColumn(), '=', $this->parent_id);
    }

    /**
     * Returns query builder for siblings of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeSiblingOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id);
    }

    /**
     * Returns siblings query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeSiblings(Builder $builder)
    {
        return $this
            ->scopeSibling($builder)
            ->where($this->getKeyName(), '<>', $this->getKey());
    }

    /**
     * Return query builder for siblings of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeSiblingsOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id, function ($position) {
            return function (Builder $builder) use ($position) {
                $builder->where($this->getPositionWithinParentColumn(), '<>', $position);
            };
        });
    }

    /**
     * Retrives all siblings of a model.
     *
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public function getSiblings(array $columns = ['*'])
    {
        /** @var Collection<static> */
        return $this->siblings()->get($columns);
    }

    /**
     * Returns number of model's siblings.
     *
     * @return int
     */
    public function countSiblings()
    {
        return $this->siblings()->count();
    }

    /**
     * Indicates whether a model has siblings.
     *
     * @return bool
     */
    public function hasSiblings()
    {
        return (bool) $this->countSiblings();
    }

    /**
     * Returns neighbors query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNeighbors(Builder $builder)
    {
        $position = $this->getPositionWithinParentValue();

        /** @var Builder */
        return $this
            ->scopeSiblings($builder)
            ->whereIn($this->getPositionWithinParentColumn(), [$position - 1, $position + 1]);
    }

    /**
     * Returns query builder for the neighbors of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeNeighborsOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id, function ($position) {
            return function (Builder $builder) use ($position) {
                return $builder->whereIn($this->getPositionWithinParentColumn(), [$position - 1, $position + 1]);
            };
        });
    }

    /**
     * Retrieves neighbors (immediate previous and immediate next models) of a model.
     *
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public function getNeighbors(array $columns = ['*'])
    {
        /** @var Collection<static> */
        return $this->neighbors()->get($columns);
    }

    /**
     * Returns query builder for a sibling at the given position.
     *
     * @param Builder $builder
     * @param int     $position
     *
     * @return Builder
     */
    public function scopeSiblingAt(Builder $builder, $position)
    {
        return $this
            ->scopeSiblings($builder)
            ->where($this->getPositionWithinParentColumn(), '=', $position);
    }

    /**
     * Returns query builder for a sibling at the given position of a node of the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     * @param int     $position
     *
     * @return Builder
     */
    public function scopeSiblingOfAt(Builder $builder, $id, $position)
    {
        return $this
            ->scopeSiblingOf($builder, $id)
            ->where($this->getPositionWithinParentColumn(), '=', $position);
    }

    /**
     * Retrieves a model's sibling with given position.
     *
     * @param int           $position
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getSiblingAt($position, array $columns = ['*'])
    {
        /** @var static|null */
        return $this->siblingAt($position)->first($columns);
    }

    /**
     * Returns query builder for the first sibling.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeFirstSibling(Builder $builder)
    {
        return $this->scopeSiblingAt($builder, 0);
    }

    /**
     * Returns query builder for the first sibling of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeFirstSiblingOf(Builder $builder, $id)
    {
        return $this->scopeSiblingOfAt($builder, $id, 0);
    }

    /**
     * Retrieves the first model's sibling.
     *
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getFirstSibling(array $columns = ['*'])
    {
        return $this->getSiblingAt(0, $columns);
    }

    /**
     * Returns query builder for the last sibling.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeLastSibling(Builder $builder)
    {
        /** @var Builder */
        return $this->scopeSiblings($builder)->orderByDesc($this->getPositionWithinParentColumn());
    }

    /**
     * Returns query builder for the last sibling of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeLastSiblingOf(Builder $builder, $id)
    {
        /** @var Builder */
        return $this
            ->scopeSiblingOf($builder, $id)
            ->orderByDesc($this->getPositionWithinParentColumn())
            ->limit(1);
    }

    /**
     * Retrieves the last model's sibling.
     *
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getLastSibling(array $columns = ['*'])
    {
        /** @var static|null */
        return $this->lastSibling()->first($columns);
    }

    /**
     * Returns query builder for the previous sibling.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePrevSibling(Builder $builder)
    {
        return $this
            ->scopeSibling($builder)
            ->where($this->getPositionWithinParentColumn(), '=', $this->getPositionWithinParentValue() - 1);
    }

    /**
     * Returns query builder for the previous sibling of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopePrevSiblingOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id, function ($position) {
            return function (Builder $builder) use ($position) {
                return $builder->where($this->getPositionWithinParentColumn(), '=', $position - 1);
            };
        });
    }

    /**
     * Retrieves immediate previous sibling of a model.
     *
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getPrevSibling(array $columns = ['*'])
    {
        /** @var static|null */
        return $this->prevSibling()->first($columns);
    }

    /**
     * Returns query builder for the previous siblings.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePrevSiblings(Builder $builder)
    {
        return $this
            ->scopeSibling($builder)
            ->where($this->getPositionWithinParentColumn(), '<', $this->getPositionWithinParentValue());
    }

    /**
     * Returns query builder for the previous siblings of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopePrevSiblingsOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id, function ($position) {
            return function (Builder $builder) use ($position) {
                return $builder->where($this->getPositionWithinParentColumn(), '<', $position);
            };
        });
    }

    /**
     * Retrieves all previous siblings of a model.
     *
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public function getPrevSiblings(array $columns = ['*'])
    {
        /** @var Collection<static> */
        return $this->prevSiblings()->get($columns);
    }

    /**
     * Returns number of previous siblings of a model.
     *
     * @return int
     */
    public function countPrevSiblings()
    {
        return $this->prevSiblings()->count();
    }

    /**
     * Indicates whether a model has previous siblings.
     *
     * @return bool
     */
    public function hasPrevSiblings()
    {
        return (bool) $this->countPrevSiblings();
    }

    /**
     * Returns query builder for the next sibling.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNextSibling(Builder $builder)
    {
        return $this
            ->scopeSibling($builder)
            ->where($this->getPositionWithinParentColumn(), '=', $this->getPositionWithinParentValue() + 1);
    }

    /**
     * Returns query builder for the next sibling of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeNextSiblingOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id, function ($position) {
            return function (Builder $builder) use ($position) {
                return $builder->where($this->getPositionWithinParentColumn(), '=', $position + 1);
            };
        });
    }

    /**
     * Retrieves immediate next sibling of a model.
     *
     * @param array<string> $columns
     *
     * @return static|null
     */
    public function getNextSibling(array $columns = ['*'])
    {
        /** @var static|null */
        return $this->nextSibling()->first($columns);
    }

    /**
     * Returns query builder for the next siblings.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNextSiblings(Builder $builder)
    {
        return $this
            ->scopeSibling($builder)
            ->where($this->getPositionWithinParentColumn(), '>', $this->getPositionWithinParentValue());
    }

    /**
     * Returns query builder for the next siblings of a node with the given ID.
     *
     * @param Builder $builder
     * @param mixed   $id
     *
     * @return Builder
     */
    public function scopeNextSiblingsOf(Builder $builder, $id)
    {
        return $this->buildSiblingQuery($builder, $id, function ($position) {
            return function (Builder $builder) use ($position) {
                return $builder->where($this->getPositionWithinParentColumn(), '>', $position);
            };
        });
    }

    /**
     * Retrieves all next siblings of a model.
     *
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public function getNextSiblings(array $columns = ['*'])
    {
        /** @var Collection<static> */
        return $this->nextSiblings()->get($columns);
    }

    /**
     * Returns number of next siblings of a model.
     *
     * @return int
     */
    public function countNextSiblings()
    {
        return $this->nextSiblings()->count();
    }

    /**
     * Indicates whether a model has next siblings.
     *
     * @return bool
     */
    public function hasNextSiblings()
    {
        return (bool) $this->countNextSiblings();
    }

    /**
     * Returns query builder for a range of siblings.
     *
     * @param Builder  $builder
     * @param int      $from
     * @param int|null $to
     *
     * @return Builder
     */
    public function scopeSiblingsRange(Builder $builder, $from, $to = null)
    {
        $position = $this->getPositionWithinParentColumn();

        $query = $this
            ->scopeSiblings($builder)
            ->where($position, '>=', $from);

        if ($to !== null) {
            $query->where($position, '<=', $to);
        }

        return $query;
    }

    /**
     * Returns query builder for a range of siblings of a node with the given ID.
     *
     * @param Builder  $builder
     * @param mixed    $id
     * @param int      $from
     * @param int|null $to
     *
     * @return Builder
     */
    public function scopeSiblingsRangeOf(Builder $builder, $id, $from, $to = null)
    {
        $position = $this->getPositionWithinParentColumn();

        $query = $this
            ->buildSiblingQuery($builder, $id)
            ->where($position, '>=', $from);

        if ($to !== null) {
            $query->where($position, '<=', $to);
        }

        return $query;
    }

    /**
     * Retrieves siblings within given positions range.
     *
     * @param int           $from
     * @param int           $to
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public function getSiblingsRange($from, $to = null, array $columns = ['*'])
    {
        /** @var Collection<static> */
        return $this->siblingsRange($from, $to)->get($columns);
    }

    /**
     * Builds query for siblings.
     *
     * @param Builder       $builder
     * @param mixed         $id
     * @param callable|null $positionCallback
     *
     * @return Builder
     */
    private function buildSiblingQuery(Builder $builder, $id, ?callable $positionCallback = null)
    {
        $parentIdColumn = $this->getParentIdColumn();
        $positionColumn = $this->getPositionWithinParentColumn();

        /** @var static|null */
        $entity = $this
            ->select([$this->getKeyName(), $parentIdColumn, $positionColumn])
            ->from($this->getTable())
            ->where($this->getKeyName(), '=', $id)
            ->limit(1)
            ->first();

        if ($entity === null) {
            return $builder;
        }

        if ($entity->parent_id === null) {
            $builder->whereNull($parentIdColumn);
        } else {
            $builder->where($parentIdColumn, '=', $entity->parent_id);
        }

        if (is_callable($positionCallback)) {
            $builder->where($positionCallback($entity->getPositionWithinParentValue()));
        }

        return $builder;
    }

    /**
     * Appends a sibling within the current depth.
     *
     * @param static   $sibling
     * @param int|null $position
     * @param bool     $returnSibling
     *
     * @return static
     */
    public function addSibling($sibling, $position = null, $returnSibling = false)
    {
        if ($this->exists) {
            $position = $position ?? static::getLatestPosition($this);

            $sibling->moveTo($position, $this->parent_id);

            if ($position < $this->getPositionWithinParentValue()) {
                $this->setPositionWithinParentValue(
                    $this->getPositionWithinParentValue() + 1
                );
            }
        }

        return $returnSibling === true ? $sibling : $this;
    }

    /**
     * Appends multiple siblings within the current depth.
     *
     * @param static[] $siblings
     * @param int|null $from
     *
     * @throws Throwable
     *
     * @return static
     */
    public function addSiblings(array $siblings, $from = null)
    {
        if (!$this->exists) {
            return $this;
        }

        $from = $from ?? static::getLatestPosition($this);

        $this->transactional(function () use ($siblings, &$from) {
            foreach ($siblings as $sibling) {
                $this->addSibling($sibling, $from);
                $from++;
            }
        });

        return $this;
    }

    /**
     * Retrieves root (with no ancestors) models.
     *
     * @param array<string> $columns
     *
     * @return Collection<static>
     */
    public static function getRoots(array $columns = ['*'])
    {
        /**
         * @var static $instance
         */
        // @phpstan-ignore-next-line
        $instance = new static();

        /** @var Collection<static> */
        return $instance->whereNull($instance->getParentIdColumn())->get($columns);
    }

    /**
     * Makes model a root with given position.
     *
     * @param int $position
     *
     * @return static
     */
    public function makeRoot($position)
    {
        return $this->moveTo($position, null);
    }

    // /**
    //  * Saves models from the given attributes array.
    //  *
    //  * @param array  $tree
    //  * @param static $parent
    //  *
    //  * @throws Throwable
    //  *
    //  * @return Collection<static>
    //  */
    // public static function createFromArray(array $tree, $parent = null)
    // {
    //     $entities = [];

    //     foreach ($tree as $item) {
    //         $children = $item[(new static())->getChildrenRelation()] ?? [];

    //         /**
    //          * @var static $entity
    //          */
    //         $entity = new static($item);
    //         $entity->parent_id = $parent ? $parent->getKey() : null;
    //         $entity->save();

    //         if ($children !== null) {
    //             $entity->addChildren(static::createFromArray($children, $entity)->all());
    //         }

    //         $entities[] = $entity;
    //     }

    //     return (new static())->newCollection($entities);
    // }

    /**
     * Makes the model a child or a root with given position. Do not use moveTo to move a node within the same ancestor (call position = value and save instead).
     *
     * @param int        $position
     * @param static|int $ancestor
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function moveTo($position, $ancestor = null)
    {
        $parentId = $ancestor instanceof self ? $ancestor->getKey() : $ancestor;

        if ($this->parent_id === $parentId && $this->parent_id !== null) {
            return $this;
        }

        if ($this->getKey() === $parentId) {
            throw new InvalidArgumentException('Target entity is equal to the sender.');
        }

        $this->parent_id = $parentId;
        $this->setPositionWithinParentValue($position);

        $this->_isMoved = true;
        $this->save();
        $this->_isMoved = false;

        return $this;
    }

    /**
     * Gets the next sibling position after the last one.
     *
     * @param static $entity
     *
     * @return int
     */
    public static function getLatestPosition($entity)
    {
        $positionColumn = $entity->getPositionWithinParentColumn();
        $parentIdColumn = $entity->getParentIdColumn();

        /** @var static|null */
        $latest = $entity->select($positionColumn)
            ->where($parentIdColumn, '=', $entity->parent_id)
            ->latest($positionColumn)
            ->first();

        $position = $latest !== null ? $latest->getPositionWithinParentValue() : -1;

        return $position + 1;
    }

    /**
     * Reorders node's siblings when it is moved to another position or ancestor.
     *
     * @return void
     */
    private function reorderSiblings()
    {
        $position = $this->getPositionWithinParentColumn();

        if ($this->_previousPosition !== null) {
            $this
                ->where($this->getKeyName(), '<>', $this->getKey())
                ->where($this->getParentIdColumn(), '=', $this->_previousParentId)
                ->where($position, '>', $this->_previousPosition)
                ->decrement($position);
        }

        $this
            ->sibling()
            ->where($this->getKeyName(), '<>', $this->getKey())
            ->where($position, '>=', $this->getPositionWithinParentValue() ?? 0)
            ->increment($position);
    }

    /**
     * Deletes a subtree from database.
     *
     * @param bool $withSelf
     * @param bool $forceDelete
     *
     * @throws Exception
     *
     * @return void
     */
    public function deleteSubtree($withSelf = false, $forceDelete = false)
    {
        $action = ($forceDelete === true ? 'forceDelete' : 'delete');

        $query = $withSelf ? $this->descendantsWithSelf() : $this->descendants();
        $ids = $query->pluck($this->getKeyName());

        if ($forceDelete) {
            /** @var IsClosureTable */
            $pivotTable = $this->getClosureTableClass();
            $closure = (new $pivotTable());
            $closure->whereIn($closure->getDescendantColumn(), $ids)->delete();
        }

        $this->whereIn($this->getKeyName(), $ids)->{$action}();
    }

    /**
     * Executes queries within a transaction.
     *
     * @param Closure $callable
     *
     * @throws Throwable
     *
     * @return mixed
     */
    private function transactional(Closure $callable)
    {
        // @phpstan-ignore-next-line
        return $this->getConnection()->transaction($callable);
    }

    /**
     * Indicates whether the model is a parent.
     *
     * @return bool
     */
    public function isParent()
    {
        return $this->exists && $this->hasChildren();
    }

    /**
     * Indicates whether the model has no ancestors.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->exists && $this->parent_id === null;
    }
}
