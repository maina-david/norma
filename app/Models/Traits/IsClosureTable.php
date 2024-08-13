<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @see Franzose\ClosureTable\Models\ClosureTable
 */
trait IsClosureTable
{
    /**
     * Inserts new node into closure table.
     *
     * @param mixed $ancestorId
     * @param mixed $descendantId
     *
     * @return void
     */
    public function insertNode($ancestorId, $descendantId)
    {
        $rows = $this->selectRowsToInsert($ancestorId, $descendantId);
        if (count($rows) > 0) {
            $this->insertOrIgnore($rows);
        }
    }

    /**
     * Undocumented function.
     *
     * @param int $ancestorId
     * @param int $descendantId
     *
     * @return array<mixed>
     */
    private function selectRowsToInsert($ancestorId, $descendantId)
    {
        $table = $this->getPrefixedTable();
        $ancestor = $this->getAncestorColumn();
        $descendant = $this->getDescendantColumn();
        $depth = $this->getDepthColumn();

        $select = "
            SELECT tbl.{$ancestor} AS {$ancestor}, ? AS {$descendant}, tbl.{$depth}+1 AS {$depth}
            FROM {$table} AS tbl
            WHERE tbl.{$descendant} = ?
            UNION ALL
            SELECT ? AS {$ancestor}, ? AS {$descendant}, 0 AS {$depth}
        ";

        $rows = $this->getConnection()->select($select, [
            $descendantId,
            $ancestorId,
            $descendantId,
            $descendantId,
        ]);

        return array_map(static function ($row) {
            return (array) $row;
        }, $rows);
    }

    /**
     * Make a node a descendant of another ancestor or makes it a root node.
     *
     * @param mixed $ancestorId
     *
     * @return void
     */
    public function moveNodeTo($ancestorId = null)
    {
        $table = $this->getPrefixedTable();
        $ancestor = $this->getAncestorColumn();
        $descendant = $this->getDescendantColumn();
        $depth = $this->getDepthColumn();

        // Prevent constraint collision
        if ($ancestorId !== null && $this->ancestor === $ancestorId) {
            return;
        }

        $this->unbindRelationships();

        // Since we have already unbound the node relationships,
        // given null ancestor id, we have nothing else to do,
        // because now the node is already root.
        if ($ancestorId === null) {
            return;
        }

        $query = "
            INSERT INTO {$table} ({$ancestor}, {$descendant}, {$depth})
            SELECT supertbl.{$ancestor}, subtbl.{$descendant}, supertbl.{$depth}+subtbl.{$depth}+1
            FROM {$table} as supertbl
            CROSS JOIN {$table} as subtbl
            WHERE supertbl.{$descendant} = ?
            AND subtbl.{$ancestor} = ?
        ";

        $this->getConnection()->statement($query, [
            $ancestorId,
            $this->descendant,
        ]);
    }

    /**
     * Unbinds current relationships.
     *
     * @return void
     */
    protected function unbindRelationships()
    {
        $table = $this->getPrefixedTable();
        $ancestorColumn = $this->getAncestorColumn();
        $descendantColumn = $this->getDescendantColumn();

        $query = "
            DELETE FROM {$table}
            WHERE {$descendantColumn} IN (
              SELECT d FROM (
                SELECT {$descendantColumn} AS d FROM {$table}
                WHERE {$ancestorColumn} = ?
              ) AS dct
            )
            AND {$ancestorColumn} IN (
              SELECT a FROM (
                SELECT {$ancestorColumn} AS a FROM {$table}
                WHERE {$descendantColumn} = ?
                AND {$ancestorColumn} <> ?
              ) AS ct
            )
        ";

        $this->getConnection()->delete($query, [
            $this->descendant,
            $this->descendant,
            $this->descendant,
        ]);
    }

    /**
     * Get table name with custom prefix for use in raw queries.
     *
     * @return string
     */
    public function getPrefixedTable()
    {
        return $this->getConnection()->getTablePrefix() . $this->getTable();
    }

    /**
     * Get value of the "ancestor" attribute.
     *
     * @return Attribute
     */
    public function ancestor(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getAttributeFromArray($this->getAncestorColumn()),
            set: fn ($value) => $this->attributes[$this->getAncestorColumn()] = $value,
        );
    }

    /**
     * Get the fully qualified "ancestor" column.
     *
     * @return string
     */
    public function getQualifiedAncestorColumn()
    {
        return $this->getTable() . '.' . $this->getAncestorColumn();
    }

    /**
     * Get the short name of the "ancestor" column.
     *
     * @return string
     */
    public function getAncestorColumn()
    {
        return 'ancestor';
    }

    public function descendant(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getAttributeFromArray($this->getDescendantColumn()),
            set: fn ($value) => $this->attributes[$this->getDescendantColumn()] = $value,
        );
    }

    /**
     * Get the fully qualified "descendant" column.
     *
     * @return string
     */
    public function getQualifiedDescendantColumn()
    {
        return $this->getTable() . '.' . $this->getDescendantColumn();
    }

    /**
     * Get the short name of the "descendant" column.
     *
     * @return string
     */
    public function getDescendantColumn()
    {
        return 'descendant';
    }

    public function depth(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getAttributeFromArray($this->getDepthColumn()),
            set: fn ($value) => $this->attributes[$this->getDepthColumn()] = (int) $value,
        );
    }

    /**
     * Gets the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedDepthColumn()
    {
        return $this->getTable() . '.' . $this->getDepthColumn();
    }

    /**
     * Get the short name of the "depth" column.
     *
     * @return string
     */
    public function getDepthColumn()
    {
        return 'depth';
    }
}
