<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;

/**
 * Class DataGroupBuilder
 *
 * Provides utilities and mechanisms for created grouped, and paged grouped datasets.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class DataGroupBuilder
{
    const KEY_GROUP = 'group';
    const KEY_GROUPS = 'groups';
    const KEY_GROUP_NAME = 'name';
    const KEY_GROUP_VALUES = 'values';
    const KEY_TOTAL_COUNT = 'total_count';
    const KEY_ITEM_CURRENT_INDEX = 'current_index';

    /**
     * A collection of data groups.
     *
     * @var array
     */
    protected $groups = [];

    /**
     * A collection of the group names.
     *
     * @var array
     */
    protected $groupNames = [];

    /**
     * The property name to group data by.
     *
     * @var null|string
     */
    private $groupProperty = null;

    /**
     * An optional callback to generate a dynamic grouped property.
     *
     * @var callable|null
     */
    private $groupCallback = null;

    /**
     * The name of the page collection.
     *
     * @var string
     */
    private $pageName = 'page';

    /**
     * A collection of non-grouped or paged values.
     *
     * @var array
     */
    private $dataValues = [];

    /**
     * The name of each group's inner dataset.
     *
     * @var string
     */
    private $collectionName = null;

    /**
     * The name of an individual group.
     *
     * @var string
     */
    private $individualGroupName = null;

    /**
     * The name of all groups.
     *
     * @var string
     */
    private $collectiveGroupName = null;

    /**
     * Indicates if the resulting dataset should be paginated or not.
     *
     * @var bool
     */
    private $isPaged = false;

    /**
     * The PaginatorContract implementation instance.
     *
     * @var PaginatorContract
     */
    private $paginator = null;

    /**
     * The current page, in a paged result set.
     *
     * @var int
     */
    private $currentPage = 0;

    /**
     * The number of records to skip when processing data.
     *
     * @var int
     */
    private $dataOffset = 0;

    /**
     * The number of records to restrict the result set to.
     *
     * @var int|null
     */
    private $dataLimit = null;

    /**
     * Indicates whether empty groups should be returned in the result set.
     *
     * @var bool
     */
    private $keepEmptyGroups = false;

    /**
     * Indicates whether meta data should be collected before pages are created.
     *
     * @var bool
     */
    private $getMetaDataBeforePaging = false;

    public function __construct(PaginatorContract $paginator)
    {
        $this->paginator = $paginator;
        $this->collectionName = self::KEY_GROUP_VALUES;
        $this->individualGroupName = self::KEY_GROUP_NAME;
        $this->collectiveGroupName = self::KEY_GROUPS;
    }

    /**
     * Skips the specified amount of record when processing data.
     *
     * @param int $offset The data offset when returning results.
     * @return DataGroupBuilder
     */
    public function skip($offset)
    {
        $this->dataOffset = $offset;

        return $this;
    }

    /**
     * Sets the name of an individual group's dataset.
     *
     * @param string $name The name to set.
     * @return $this
     */
    public function setCollectionName($name)
    {
        if ($name === null) {
            $this->collectionName = self::KEY_GROUP_VALUES;
        } else {
            $this->collectionName = $name;
        }

        return $this;
    }

    /**
     * Sets the name of all dataset groups.
     *
     * @param string $name The name to set.
     * @return $this
     */
    public function setCollectiveGroupName($name)
    {
        if ($name === null) {
            $this->collectiveGroupName = self::KEY_GROUPS;
        } else {
            $this->collectiveGroupName = $name;
        }

        return $this;
    }

    /**
     * Sets the name of an individual groups.
     *
     * @param string $name The name to set.
     * @return $this
     */
    public function setIndividualGroupName($name)
    {
        if ($name === null) {
            $this->individualGroupName = self::KEY_GROUP_NAME;
        } else {
            $this->individualGroupName = $name;
        }

        return $this;
    }

    /**
     * Requests data only be returned for the provided page, in a paged result set.
     *
     * @param int $page The page to return.
     * @return $this
     */
    public function forPage($page)
    {
        $this->currentPage = $page;

        return $this;
    }

    /**
     * The name of the pages to generate, in a paged dataset.
     *
     * @param string $name The name of the page.
     * @return $this
     */
    public function pageBy($name)
    {
        $this->pageName = $name;

        return $this;
    }

    /**
     * Sets whether the resulting dataset should be paginated.
     *
     * @param bool $doPaginate Whether to paginate the dataset.
     * @return $this
     */
    public function paginateResults($doPaginate)
    {
        $this->isPaged = $doPaginate;

        return $this;
    }

    /**
     * Requests data be split into a collection of pages of the provided size.
     *
     * @param int $pageSize The size of pages to generate.
     * @return $this
     */
    public function limit($pageSize)
    {
        if ($pageSize === null || $pageSize === 0 || $pageSize < 0) {
            $this->dataLimit = null;

            return $this;
        }

        $this->dataLimit = $pageSize;

        return $this;
    }

    /**
     * Sets the property to group the dataset by.
     *
     * @param string $property The property to group by.
     */
    public function setProperty($property)
    {
        $this->groupProperty = $property;
    }

    /**
     * Sets an optional callback to generate a dynamic property.
     *
     * @param callable $callback The property callback.
     */
    public function setCallback(callable $callback)
    {
        $this->groupCallback = $callback;
    }

    /**
     * Sets whether empty groups will be returned in the result set.
     *
     * @param bool $keepEmptyGroups Whether to include empty groups.
     * @return $this
     */
    public function doKeepEmptyGroups($keepEmptyGroups)
    {
        $this->keepEmptyGroups = $keepEmptyGroups;

        return $this;
    }

    /**
     * Sets whether to gather metadata before paging.
     *
     * @param bool $gatherMetadata Whether to gather metadata before paging.
     * @return $this
     */
    public function gatherMetadataBeforePaging($gatherMetadata)
    {
        $this->getMetaDataBeforePaging = $gatherMetadata;

        return $this;
    }

    /**
     * Groups the provided comments using the previously set property and state.
     *
     * @param CommentContract[] $comments The comments to group.
     * @return PagedDataSetContract|GroupedDataSetContract
     */
    public function group($comments)
    {
        foreach ($comments as $comment) {

            if ($this->groupCallback !== null) {
                call_user_func_array($this->groupCallback, [$comment]);
            }

            $groupValue = $comment->getDataAttribute($this->groupProperty);

            if (array_key_exists($groupValue, $this->groups) === false) {
                $this->groups[$groupValue] = [
                    $this->individualGroupName => $groupValue,
                    $this->collectionName => [],
                    self::KEY_TOTAL_COUNT => 0
                ];
                $this->groupNames[] = $groupValue;
            }

            $this->groups[$groupValue][$this->collectionName][] = $comment;
            $this->groups[$groupValue][self::KEY_TOTAL_COUNT]++;
            $this->dataValues[$comment->getId()] = $comment;
        }

        if ($this->isPaged === false) {
            $this->applyNonPagedOffsets();

            $nonPagedDataSet = new GroupedDataSet();
            $nonPagedDataSet->setGroupNames($this->groupNames);

            $nonPagedDataSet->setCollectiveGroupName($this->collectiveGroupName);
            $nonPagedDataSet->setGroupName($this->individualGroupName);
            $nonPagedDataSet->setGroupDatasetName($this->collectionName);

            $nonPagedDataSet->setData([
                $this->collectiveGroupName => $this->groups,
                self::KEY_TOTAL_COUNT => count($this->groups)
            ]);

            return $nonPagedDataSet;
        }

        $metadataCollection = new DataSetMetadata();

        if ($this->getMetaDataBeforePaging === true) {
            $metadataCollection->setData($this->dataValues);
            $metadataCollection->processAndUnset();
        }

        $paginatedData = $this->paginator->paginate(
            $this->dataValues,
            $this->pageName,
            $this->currentPage,
            $this->dataOffset,
            $this->dataLimit
        );

        $this->dataValues = $paginatedData->getDisplayItems();
        $this->buildDataGroups();

        $paginatedData->setDisplayItems([
            $this->collectiveGroupName => $this->groups,
            self::KEY_TOTAL_COUNT => count($this->groups)
        ]);

        $pagedGroupDataSet = new PagedGroupedDataSet();
        $pagedGroupDataSet->setData($paginatedData->toArray());

        $pagedGroupDataSet->setCollectiveGroupName($this->collectiveGroupName);
        $pagedGroupDataSet->setGroupName($this->individualGroupName);
        $pagedGroupDataSet->setGroupDatasetName($this->collectionName);

        $pagedGroupDataSet->setGroupNames($this->groupNames);
        $pagedGroupDataSet->fromPaginatorResult($paginatedData);
        $pagedGroupDataSet->setDatasetMetadata($metadataCollection);


        return $pagedGroupDataSet;
    }

    /**
     * Updates the underlying dataset by applying offsets and limits.
     */
    private function applyNonPagedOffsets()
    {
        // Process non-paged limits and offsets.
        if ($this->dataOffset !== null && $this->dataOffset > 0 && $this->dataLimit == null) {
            $this->dataValues = array_slice($this->dataValues, $this->dataOffset, null, true);
            $this->buildDataGroups();
        } elseif ($this->dataOffset !== null && $this->dataOffset > 0 && $this->dataLimit !== null && $this->dataLimit > 0) {
            $this->dataValues = array_slice($this->dataValues, $this->dataOffset, $this->dataLimit, true);
            $this->buildDataGroups();
        } elseif (($this->dataOffset === null || $this->dataOffset === 0) && $this->dataLimit !== null && $this->dataLimit > 0) {
            $this->dataValues = array_slice($this->dataValues, 0, $this->dataLimit, true);
            $this->buildDataGroups();
        }
    }

    /**
     * Creates the dataset groups.
     */
    private function buildDataGroups()
    {
        $this->groups = [];

        foreach ($this->groupNames as $group) {
            if (array_key_exists($group, $this->groups) === false) {
                $this->groups[$group] = [
                    $this->individualGroupName => $group,
                    $this->collectionName => [],
                    self::KEY_TOTAL_COUNT => 0
                ];
            }

            /** @var CommentContract $comment */
            foreach ($this->dataValues as $comment) {
                if ($comment->getDataAttribute($this->groupProperty) === $group) {
                    $this->groups[$group][$this->collectionName][] = $comment;
                    $this->groups[$group][self::KEY_TOTAL_COUNT]++;
                }
            }
        }

        if ($this->keepEmptyGroups === false) {
            $this->groups = array_filter($this->groups, function ($group) {
                return $group[self::KEY_TOTAL_COUNT] > 0;
            });

            // Reset group names.
            $this->groupNames = [];

            foreach ($this->groups as $groupName => $group) {
                $this->groupNames[] = $groupName;
            }
        }
    }

    /**
     * Gets the group names.
     *
     * @return array
     */
    public function getGroupNames()
    {
        return $this->groupNames;
    }

}
