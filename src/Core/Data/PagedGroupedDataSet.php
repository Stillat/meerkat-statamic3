<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\PagedGroupedDataSetContract;
use Stillat\Meerkat\Core\Data\Concerns\ContainsGroups;
use Stillat\Meerkat\Core\Data\Concerns\IteratesDataSets;
use Stillat\Meerkat\Core\Data\Concerns\ManagesGroupMetaData;
use Stillat\Meerkat\Core\Data\Helpers\GroupFlattener;

/**
 * Class PagedGroupedDataSet
 *
 * Represents a paginated dataset that contains groups.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class PagedGroupedDataSet extends PagedDataSet implements PagedGroupedDataSetContract
{
    use ContainsGroups, ManagesGroupMetaData, IteratesDataSets;

    /**
     * Flattens the grouped dataset into one-dimensional array.
     *
     * @return array
     */
    public function flattenDataset()
    {
        if ($this->flattenedData === null) {
            $this->flattenedData = GroupFlattener::flatten(
                $this->displayItems,
                $this->getCollectiveGroupName(),
                $this->getGroupName(),
                $this->getGroupDatasetName()
            );
        }

        return $this->flattenedData;
    }

}
