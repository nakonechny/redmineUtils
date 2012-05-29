<?php
namespace redmine;

class Issue extends XmlResource
{
    public $element_name = 'issue';

    public function count()
    {
        return count($this->_data);
    }
}