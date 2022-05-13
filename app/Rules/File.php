<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class File implements Rule
{

    public function __construct()
    {
        $this->mimeVideo = ['video/x-flv', 'video/mp4', 'video/mp2t', 'video/3gpp', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
        $this->mimeImage = ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'];
        $this->filetype = [];
    }

    public function passes($attribute, $value)
    {
        if ($value) {

            if (is_array($value)) {

                foreach ($value as $item) {
                    if (is_file($item)) {
                        if (!$this->fileValidate($item->getMimeType())) {
                            return false;
                        }
                    } else {
                        if (!$this->fileValidate('text')) {
                            return false;
                        }
                    }
                }

                if (count($this->filetype) > count(array_unique($this->filetype)) || count($this->filetype) == 1) {
                    return true;
                } else {
                    return false;
                }

            } else {
                if (is_file($value)) {
                    return $this->fileValidate($value->getMimeType());
                } else {
                    return $this->fileValidate('text');
                }
            }

        } else {
            return false;
        }
    }

    public function message()
    {
        return 'The file invalid.';
    }

    private function fileValidate($mime)
    {
        if ($mime == 'text') {
            $this->filetype[] = "text";
            return true;

        } else if (false !== mb_strpos($mime, "image")) {
            $this->filetype[] = "image";

            in_array($mime, $this->mimeImage) ? $isChk = true : $isChk = false;

            if (!$isChk) {
                return false;
            } else {
                return true;
            }

        } else if (false !== mb_strpos($mime, "video")) {
            $this->filetype[] = "video";

            in_array($mime, $this->mimeVideo) ? $isChk = true : $isChk = false;

            if (!$isChk) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
