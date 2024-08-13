<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Cleans up HTML before being saved.
 **/
class HTMLPurifierService
{
    /**
     * @param string $html
     *
     * @return string
     */
    public function cleanComment(string $html): string
    {
        $settings = [
            'HTML.Allowed' => 'a[href],p,br,b,div',
            'CSS.AllowedProperties' => '',
        ];
        $config = HTMLPurifier_Config::create($settings);
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function cleanTaskDescription(string $html): string
    {
        $settings = [
            'HTML.Allowed' => 'div,p[style],br,b,u,i,ul,li,strike,ol,a[href],strong,em',
            'CSS.AllowedProperties' => 'text-align,text-indent,padding-left',
        ];
        $config = HTMLPurifier_Config::create($settings);
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function cleanSummary(string $html): string
    {
        return $this->cleanTaskDescription($html);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function cleanSection(string $html): string
    {
        $settings = [
            'HTML.Allowed' => 'a[href|target|rel],p,span,br,h1,h2,h3,h4,h5,h6,pre,sup,sub,s,u,em,strong,b,ol,li,ul,hr,img[src|width],table[border|cellpadding|cellspacing|width],tbody,tr,td,th,thead,*[style],*[class],div[id]',
            'CSS.AllowedProperties' => 'text-align,text-indent,font-size,font-weight,margin-left,margin-right,margin,margin-top,margin-bottom,width',
        ];
        $config = HTMLPurifier_Config::create($settings);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_top', '_self', '_parent']);
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function cleanSectionForPrinting(string $html): string
    {
        $settings = [
            'HTML.Allowed' => 'p,span,h1,h2,h3,h4,h5,h6,pre,sup,sub,s,u,em,strong,b,ol,li,ul,hr,table[border|cellpadding|cellspacing|width],tbody,tr,td,th,thead,*[style],*[class]',
            'CSS.AllowedProperties' => 'text-align,text-indent,font-size,font-weight,margin-left,margin-right,margin,margin-top,margin-bottom,width',
        ];
        $config = HTMLPurifier_Config::create($settings);
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    public function cleanBasicWysiwyg(string $html): string
    {
        $settings = [
            'HTML.Allowed' => 'div,p[style],br,b,u,i,ul,li,strike,ol,a[href]',
            'CSS.AllowedProperties' => 'text-align,text-indent',
        ];
        $config = HTMLPurifier_Config::create($settings);
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
