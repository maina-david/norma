<?php

namespace App\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * PHP stan can't seem to read Eloquent mixin from an interface, so have to use a class
 * Only to be used for doc blocks for static analysis.
 *
 * @mixin \Eloquent
 *
 * @property int $id
 * @property int $ancestor
 * @property int $descendant
 * @property int $depth
 *
 * @method static insertNode($ancestorId, $descendantId)
 * @method static moveNodeTo($ancestorId = null)
 * @method static getPrefixedTable()
 * @method static getQualifiedAncestorColumn()
 * @method static getAncestorColumn()
 * @method static getQualifiedDescendantColumn()
 * @method static getDescendantColumn()
 * @method static getQualifiedDepthColumn()
 * @method static getDepthColumn()
 */
class IsClosureTable extends Pivot
{
}
