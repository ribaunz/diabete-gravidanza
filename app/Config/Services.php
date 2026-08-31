<?php

namespace Config;

use App\Libraries\Auth;
use App\Libraries\MagicLink;
use App\Libraries\PdfExporter;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * @see http://codeigniter.com/user_guide/concepts/services.html
 */
class Services extends BaseService
{
    public static function auth(bool $getShared = true): Auth
    {
        if ($getShared) {
            return static::getSharedInstance('auth');
        }

        return new Auth();
    }

    public static function magicLink(bool $getShared = true): MagicLink
    {
        if ($getShared) {
            return static::getSharedInstance('magicLink');
        }

        return new MagicLink();
    }

    public static function pdfExporter(bool $getShared = true): PdfExporter
    {
        if ($getShared) {
            return static::getSharedInstance('pdfExporter');
        }

        return new PdfExporter();
    }
}
