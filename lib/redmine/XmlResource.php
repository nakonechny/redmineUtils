<?php
namespace redmine;
use \ActiveResource;
use \Naf;

class XmlResource extends ActiveResource
{
    public $request_format = 'xml';

    public function __construct($data = array())
    {
        $this->site = 'http://'.Naf::config('redmine.api_key').'@'.Naf::config('redmine.domain').'/';
        parent::__construct($data);
    }
}