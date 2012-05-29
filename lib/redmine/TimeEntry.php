<?php
namespace redmine;
use \ActiveResource;
use \Naf;

class TimeEntry extends ActiveResource
{
    public $request_format = 'xml'; // REQUIRED!
    public $element_name = 'time_entry';
    public $element_name_plural = 'time_entries';

    public function __construct($data = array())
    {
        $this->site = 'http://'.Naf::config('redmine.api_key').'@'.Naf::config('redmine.domain').'/';
        parent::__construct($data);
    }
}