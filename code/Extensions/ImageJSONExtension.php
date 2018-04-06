<?php

class ImageJSONExtension extends DataExtension
{
    public function getJSON()
    {
        return  [
                    'id'        =>  $this->owner->ID,
                    'title'     =>  $this->owner->Title,
                    'width'     =>  $this->owner->getWidth(),
                    'height'    =>  $this->owner->getHeight(),
                    'src'       =>  $this->owner->getRelativePath()
                ];
    }
}
