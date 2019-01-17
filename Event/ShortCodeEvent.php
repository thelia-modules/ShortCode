<?php


namespace ShortCode\Event;


use Thelia\Core\Event\ActionEvent;

class ShortCodeEvent extends ActionEvent
{
    /** @var string  */
    protected $content;

    /** @var array  */
    protected $attributes;

    /** @var string  */
    protected $result;

    /**
     * @param string $content
     * @param array $attributes
     */
    public function __construct($content, $attributes)
    {
        $this->content = $content;
        $this->attributes = $attributes;
        $this->result = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}