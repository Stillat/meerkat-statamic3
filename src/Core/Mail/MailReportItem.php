<?php

namespace Stillat\Meerkat\Core\Mail;

use Stillat\Meerkat\Core\Support\Arr;

/**
 * Class MailReportItem
 *
 * Represents a single line item in a comment mail report.
 *
 * @package Stillat\Meerkat\Core\Mail
 * @since 2.1.5
 */
class MailReportItem
{
    const KEY_SENT_ON = 'sent_on';
    const KEY_ADDRESS = 'address';
    const KEY_DID_SEND = 'did_send';

    /**
     * The UTC timestamp the email was sent.
     * @var int
     */
    protected $sentOn = null;

    /**
     * The email address the email was sent to.
     *
     * @var string
     */
    protected $address = '';

    /**
     * Indicates if the mail was sent successfully.
     *
     * @var bool
     */
    protected $didSend = false;

    public function __construct()
    {
        $this->sentOn = time();
    }

    /**
     * Constructs a MailReportItem from the provided data.
     *
     * @param array $data The report data.
     * @return MailReportItem
     */
    public static function fromArray($data)
    {
        $reportItem = new MailReportItem();

        if (Arr::matches([self::KEY_SENT_ON, self::KEY_ADDRESS, self::KEY_DID_SEND], $data)) {
            $reportItem->setSentOn($data[self::KEY_SENT_ON]);
            $reportItem->setDidSend($data[self::KEY_DID_SEND]);
            $reportItem->setAddress($data[self::KEY_ADDRESS]);
        }

        return $reportItem;
    }

    /**
     * Converts the current MailReportItem to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_SENT_ON => $this->getSentOn(),
            self::KEY_ADDRESS => $this->getAddress(),
            self::KEY_DID_SEND => $this->getDidSend()
        ];
    }

    /**
     * Gets the UTC timestamp the email message was sent on.
     *
     * @return int
     */
    public function getSentOn()
    {
        return $this->sentOn;
    }

    /**
     * Sets the UTC timestamp the email message was sent on.
     *
     * @param int $sentOn The timestamp.
     */
    public function setSentOn($sentOn)
    {
        $this->sentOn = $sentOn;
    }

    /**
     * Gets the email address the message was sent to.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the email address the message was sent to.
     *
     * @param string $address The address.
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Indicates if the email message was sent successfully.
     *
     * @return bool
     */
    public function getDidSend()
    {
        return $this->didSend;
    }

    /**
     * Sets whether or not the email message was sent without error.
     *
     * @param bool $didSend Whether the email sent without errors.
     */
    public function setDidSend($didSend)
    {
        $this->didSend = $didSend;
    }

}
