<?php


namespace Soukicz\SeznamFbl;


class Domain {

    const STATUS_ACTIVE = 1;
    const STATUS_PENDING = 2;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string
     */
    protected $hostname, $selector, $header, $regex, $consumer;

    /**
     * @return string
     */
    public function getHostname() {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     * @return Domain
     */
    public function setHostname($hostname) {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelector() {
        return $this->selector;
    }

    /**
     * @param string $selector
     * @return Domain
     */
    public function setSelector($selector) {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * @param string $header
     * @return Domain
     */
    public function setHeader($header) {
        $this->header = $header;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegex() {
        return $this->regex;
    }

    /**
     * @param string $regex
     * @return Domain
     */
    public function setRegex($regex) {
        $this->regex = $regex;
        return $this;
    }

    /**
     * @return string
     */
    public function getConsumer() {
        return $this->consumer;
    }

    /**
     * @param string $consumer
     * @return Domain
     */
    public function setConsumer($consumer) {
        $this->consumer = $consumer;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    public function setActive() {
        $this->status = self::STATUS_ACTIVE;
        return $this;
    }

    public function setPending() {
        $this->status = self::STATUS_PENDING;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->getStatus() === self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isPending() {
        return $this->getStatus() === self::STATUS_PENDING;
    }

}
