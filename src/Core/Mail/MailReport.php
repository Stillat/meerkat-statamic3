<?php

namespace Stillat\Meerkat\Core\Mail;

use Stillat\Meerkat\Core\Support\Arr;

/**
 * Class MailReport
 *
 * Represents a collection of report items related to sending comment submission emails.
 *
 * @ince 2.1.5
 */
class MailReport
{
    const KEY_GENERATED_ON = 'generated_on';

    const KEY_ITEMS = 'items';

    const KEY_COMMENT_ID = 'comment_id';

    /**
     * The UTC timestamp the report was generated on.
     *
     * @var int
     */
    protected $generatedOn = null;

    /**
     * The report items.
     *
     * @var MailReportItem[]
     */
    protected $items = [];

    /**
     * The system comment identifier the report was generated for.
     *
     * @var string
     */
    protected $commentId = '';

    public function __construct()
    {
        $this->generatedOn = time();
    }

    /**
     * Constructs a new instance of MailReport from the provided data.
     *
     * @param  array  $data The report data.
     * @return MailReport
     */
    public static function fromArray($data)
    {
        $report = new MailReport();

        if (Arr::matches([self::KEY_GENERATED_ON, self::KEY_COMMENT_ID, self::KEY_ITEMS], $data)) {
            $report->setCommentId($data[self::KEY_COMMENT_ID]);
            $report->setGeneratedOn($data[self::KEY_GENERATED_ON]);

            $newItems = [];

            if (is_array($data[self::KEY_ITEMS])) {
                foreach ($data[self::KEY_ITEMS] as $item) {
                    if (is_array($item)) {
                        $newItems[] = MailReportItem::fromArray($item);
                    }
                }
            }

            $report->setItems($newItems);
        }

        return $report;
    }

    /**
     * Gets the mail report items.
     *
     * @return MailReportItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets the mail report items.
     *
     * @param  MailReportItem[]  $reportItems The report items.
     */
    public function setItems($reportItems)
    {
        $this->items = $reportItems;
    }

    /**
     * Converts the comment report to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_ITEMS => $this->getItemArray(),
            self::KEY_COMMENT_ID => $this->getCommentId(),
            self::KEY_GENERATED_ON => $this->getGeneratedOn(),
        ];
    }

    /**
     * Converts all report items to an array.
     *
     * @return array
     */
    protected function getItemArray()
    {
        $reportItems = [];

        foreach ($this->items as $item) {
            $reportItems[] = $item->toArray();
        }

        return $reportItems;
    }

    /**
     * Returns the report's comment system identifier.
     *
     * @return string
     */
    public function getCommentId()
    {
        return $this->commentId;
    }

    /**
     * Sets the report's comment system identifier.
     *
     * @param  string  $commentId The comment identifier.
     */
    public function setCommentId($commentId)
    {
        $this->commentId = $commentId;
    }

    /**
     * Gets the UTC timestamp the report was generated on.
     *
     * @return int
     */
    public function getGeneratedOn()
    {
        return $this->generatedOn;
    }

    /**
     * Sets the UTC timestamp the report was generated on.
     *
     * @param  int  $generatedOn The date/time the report was generated on.
     */
    public function setGeneratedOn($generatedOn)
    {
        $this->generatedOn = $generatedOn;
    }
}
