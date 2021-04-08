<?php

namespace IrisCrm\Api;

use IrisCrm\Api\AbstractApi;

class Lead extends AbstractApi
{
    public function all()
    {
        return $this->get('/leads');
    }

    public function find($id)
    {
        return $this->get('/leads/'.$id);
    }
}
