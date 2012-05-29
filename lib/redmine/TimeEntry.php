<?php
namespace redmine;

class TimeEntry extends XmlResource
{
    public $element_name = 'time_entry';
    public $element_name_plural = 'time_entries';
    protected $id; // defined to check wether id was set on object save
}