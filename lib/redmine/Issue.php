<?php
namespace redmine;
use \ActiveResource;
use \Naf;

class Issue extends ActiveResource
{
    public $request_format = 'xml'; // REQUIRED!
    public $element_name = 'issue';

    public function __construct($data = array())
    {
        $this->site = 'http://'.Naf::config('redmine.api_key').'@'.Naf::config('redmine.domain').'/';
        parent::__construct($data);
    }

    public function count()
    {
        return count($this->_data);
    }
}