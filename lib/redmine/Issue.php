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
        $redmine_config = Naf::config('redmine');
        $this->site = 'http://'.$redmine_config['api_key'].'@'.$redmine_config['domain'].'/';
        parent::__construct($data);

    }

    public function count()
    {
        return count($this->_data);
    }
}