<?php

class PregMatchIterator implements IteratorAggregate
{
    protected $list;

    public function __construct($pattern, $list)
    {
        $this->list = static::filter($pattern, $list);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->list);
    }

    protected static function filter($pattern, $list)
    {
        $result = array();
        foreach ($list as $item) {
            $matches = null;
            if (preg_match($pattern, $item, $matches)) {
                $result[] = $matches;
            }
        }

        return $result;
    }
}