<?php
use Ntb\RestAPI\BaseRestController;
use SaltedHerring\Debugger;
/**
 * @file ImageAPI.php
 *
 * Controller to present the data from forms.
 * */
class ImageAPI extends BaseRestController
{
    private static $allowed_actions = [
        'get'                   =>  true,
        'post'                  =>  "->isAuthenticated"
    ];

    public function isAuthenticated()
    {
        if ($member = Member::currentUser()) {
            if ($csrf = $this->request->postVar('csrf')) {
                return $csrf    ==  SecurityToken::getSecurityID();
            }
        }

        return false;
    }

    public function get($request)
    {
        if ($ID = $this->request->param('ID')) {
            if ($image = Image::get()->byID($ID)) {
                $width          =   $this->request->getVar('width');
                $height         =   $this->request->getVar('height');

                if (!empty($width) && !empty($height)) {
                    $image      =   $image->FillMax($width, $height);
                } elseif (!empty($width)) {
                    $image      =   $image->SetWidth($width);
                } elseif (!empty($height)) {
                    $image      =   $image->SetHeight($height);
                }

                return  [
                            'code'      =>  200,
                            'image'     =>  [
                                                'id'        =>  $image->ID,
                                                'title'     =>  $image->Title,
                                                'width'     =>  $image->getWidth(),
                                                'height'    =>  $image->getHeight(),
                                                'src'       =>  $image->getRelativePath()
                                            ]
                        ];
            }
        }

        return  [
                    'code'      =>  404,
                    'message'   =>  'image nout found'
                ];
    }

    public function post($request)
    {
        $images                     =   $request->postVar('images');

        if (!empty($images['tmp_name'])) {

            $directory              =   Config::inst()->get('Image', 'AjaxUploadDirectory');

            if (empty($directory)) {
                $directory          =   Folder::find_or_make('Uploads/mid-' . Member::currentUserID());
            }

            $files                  =   $images['tmp_name'];
            $i                      =   0;
            $image_list             =   [];
            foreach ($files as $file)
            {
                $file_name          =   $images['name'][$i];
                $ext                =   explode('.', $file_name);
                $image_name_naked   =   $ext[0];
                $ext                =   count($ext) > 0 ? ('.' . $ext[1]) : '.jpg';
                $new_file_name      =   sha1(time() . rand() . $file_name) . $ext;
                $dest               =   $directory->Fullpath . $new_file_name;
                copy($file, $dest);

                $image              =   new Image();
                $image->ParentID    =   $directory->ID;
                $image->Title       =   $image_name_naked;
                $image->Filename    =   $directory->RelativePath . $new_file_name;
                $image->write();
                $image_list[]       =   $image->getJSON();
                $i++;
            }

            return  [
                        'code'      =>  200,
                        'message'   =>  'Upload successful',
                        'images'    =>  $image_list
                    ];
        }

        return  [
                    'code'      =>  500,
                    'message'   =>  'The image hasn\'t been corrupted!'
                ];
    }
}
