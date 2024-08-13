<?php

namespace App\Services\Arachno\Crawlers\Sources\Ch;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\TocItemDraft;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 * slug: ch-basel-stadt
 * title: Kanton Basel-Stadt
 * url: https://www.bs.ch/
 */
class SwitzerlandBaselProvincialLaw extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ch-basel-stadt',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.gesetzessammlung.bs.ch/api/de/texts_of_law/lightweight_index?show_abrogated=false',
                    ]),
                ],
                'type_' . CrawlType::SEARCH->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.gesetzessammlung.bs.ch/api/de/texts_of_law/lightweight_index?show_abrogated=false',
                    ]),
                ],
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.gesetzessammlung.bs.ch/api/de/change_documents/lightweight_index',
                    ]),
                ],
            ],
            'css' => '.structured-document strong,.structured-document strong *{font-weight:700!important;}.structured-document em,.structured-document em *{font-style:italic!important;}.structured-document u,.structured-document u *{text-decoration:underline!important;}.structured-document strike,.structured-document strike *{text-decoration:line-through!important;}.structured-document #modification_table,.structured-document .document,.structured-document .footnotes{padding:0 1rem;}.structured-document #modification_table h1,.structured-document #modification_table h2,.structured-document #modification_table h3,.structured-document #modification_table h4,.structured-document #modification_table h5,.structured-document .document h1,.structured-document .document h2,.structured-document .document h3,.structured-document .document h4,.structured-document .document h5,.structured-document .footnotes h1,.structured-document .footnotes h2,.structured-document .footnotes h3,.structured-document .footnotes h4,.structured-document .footnotes h5{color:#000!important;}.structured-document #modification_table,.structured-document .annex_list,.structured-document .annex_title,.structured-document .diff_document,.structured-document .diff_document td,.structured-document .document,.structured-document .footnotes{font-size:10pt!important;font-family:Arial Unicode MS,Arial,Calibri,Liberation Sans,DejaVu Sans,sans-serif;color:#333;}.structured-document .diff_document a.annex_link.disabled,.structured-document .document a.annex_link.disabled{color:#bbb;font-style:italic;}.structured-document .diff_document a.annex_link.abrogated,.structured-document .document a.annex_link.abrogated{color:#bbb;font-style:normal;}.structured-document .diff_document a.annex_link.not_linked,.structured-document .document a.annex_link.not_linked{cursor:text;}.structured-document .diff_document a.annex_link.not_linked:hover,.structured-document .document a.annex_link.not_linked:hover{text-decoration:underline;}.structured-document .diff_document a.annex_link.not_linked:active,.structured-document .diff_document a.annex_link.not_linked:focus,.structured-document .document a.annex_link.not_linked:active,.structured-document .document a.annex_link.not_linked:focus{color:#bbb!important;}.structured-document .diff_document h1.systematic_number,.structured-document .document h1.systematic_number{width:100%;font-size:1.33em;font-weight:700;text-align:left;margin-top:1rem;padding:0;}.structured-document .diff_document h1.title,.structured-document .document h1.title{font-size:1.33em;font-weight:700;padding:0;}.structured-document .diff_document h2.abbreviation,.structured-document .document h2.abbreviation{font-size:1.33em;padding-bottom:1.33em;}.structured-document .diff_document h2.header_text,.structured-document .document h2.header_text{color:#777;border-bottom:1px solid #777;font-size:1em;padding-bottom:.33em;margin-top:.66em;margin-bottom:.66em;}.structured-document .diff_document .enactment,.structured-document .document .enactment{clear:both;font-size:1em;padding-bottom:1.33em;}.structured-document .diff_document .ingress_action,.structured-document .diff_document .ingress_author,.structured-document .diff_document .ingress_foundation,.structured-document .document .ingress_action,.structured-document .document .ingress_author,.structured-document .document .ingress_foundation{padding-top:.66em;padding-bottom:.66em;text-align:left;}.structured-document .diff_document .ingress_action,.structured-document .diff_document .ingress_author,.structured-document .document .ingress_action,.structured-document .document .ingress_author{font-style:italic;}.structured-document .diff_document .egress_sign_off_date,.structured-document .diff_document .egress_sign_off_remarks,.structured-document .diff_document .egress_sign_off_signature,.structured-document .document .egress_sign_off_date,.structured-document .document .egress_sign_off_remarks,.structured-document .document .egress_sign_off_signature{padding-bottom:.66em;text-align:left;}.structured-document .diff_document .egress_sign_off_date,.structured-document .diff_document .egress_sign_off_signature,.structured-document .document .egress_sign_off_date,.structured-document .document .egress_sign_off_signature{width:240px;}.structured-document .diff_document .egress_sign_off_date.float,.structured-document .diff_document .egress_sign_off_signature.float,.structured-document .document .egress_sign_off_date.float,.structured-document .document .egress_sign_off_signature.float{float:left;}.structured-document .diff_document .egress_sign_off_signature.line_break,.structured-document .document .egress_sign_off_signature.line_break{margin-top:20px;}.structured-document .diff_document .paragraph_post.last-child,.structured-document .document .paragraph_post.last-child{padding-bottom:5em}.structured-document .diff_document .egress_sign_off_remarks,.structured-document .document .egress_sign_off_remarks{clear:both}.structured-document .diff_document .egress_ags_source,.structured-document .diff_document .egress_ags_source_publication,.structured-document .document .egress_ags_source,.structured-document .document .egress_ags_source_publication{clear:both;padding-top:2em;color:#888;}.structured-document .diff_document .title,.structured-document .document .title{padding-top:2em;padding-bottom:.33em;}.structured-document .diff_document .title>.number_discrete,.structured-document .document .title>.number_discrete{font-size:.9em;font-weight:400;font-style:normal;float:right;color:#777;}.structured-document .diff_document .title.level_1>.number,.structured-document .diff_document .title.level_1>.title_text,.structured-document .document .title.level_1>.number,.structured-document .document .title.level_1>.title_text{font-size:1.33em;font-weight:700}.structured-document .diff_document .title.level_1>.number,.structured-document .document .title.level_1>.number{padding-right:.2em}.structured-document .diff_document .title.level_1>.number_discrete,.structured-document .document .title.level_1>.number_discrete{padding-top:.3em}.structured-document .diff_document .title.level_2>.number,.structured-document .diff_document .title.level_2>.title_text,.structured-document .document .title.level_2>.number,.structured-document .document .title.level_2>.title_text{font-size:1.33em;font-weight:700;font-style:italic}.structured-document .diff_document .title.level_2>.number,.structured-document .document .title.level_2>.number{padding-right:.2em}.structured-document .diff_document .title.level_2>.number_discrete,.structured-document .document .title.level_2>.number_discrete{padding-top:.3em}.structured-document .diff_document .title.level_3>.number,.structured-document .diff_document .title.level_3>.title_text,.structured-document .document .title.level_3>.number,.structured-document .document .title.level_3>.title_text{font-size:1.33em;font-weight:400;font-style:italic}.structured-document .diff_document .title.level_3>.number,.structured-document .document .title.level_3>.number{padding-right:.2em}.structured-document .diff_document .title.level_3>.number_discrete,.structured-document .document .title.level_3>.number_discrete{padding-top:.323em}.structured-document .diff_document .title.level_4>.number,.structured-document .diff_document .title.level_4>.title_text,.structured-document .document .title.level_4>.number,.structured-document .document .title.level_4>.title_text{font-size:1.22em;font-weight:400;font-style:normal}.structured-document .diff_document .title.level_4>.number,.structured-document .document .title.level_4>.number{padding-right:.2em}.structured-document .diff_document .title.level_4>.number_discrete,.structured-document .document .title.level_4>.number_discrete{padding-top:.2em}.structured-document .diff_document .title.level_5>.number,.structured-document .diff_document .title.level_5>.title_text,.structured-document .document .title.level_5>.number,.structured-document .document .title.level_5>.title_text{font-size:1.11em;font-weight:400;font-style:normal}.structured-document .diff_document .title.level_5>.number,.structured-document .document .title.level_5>.number{padding-right:.2em}.structured-document .diff_document .title.level_5>.number_discrete,.structured-document .document .title.level_5>.number_discrete{padding-top:.1em}.structured-document .diff_document .article,.structured-document .document .article{padding-top:1.33em;padding-bottom:.11em}.structured-document .diff_document .article>.article_number,.structured-document .document .article>.article_number{font-weight:700}.structured-document .diff_document .article>.article_title,.structured-document .document .article>.article_title{font-size:1em}.structured-document .diff_document .paragraph,.structured-document .document .paragraph{margin-top:.33em}.structured-document .diff_document .paragraph>.number,.structured-document .document .paragraph>.number{font-size:.65em;margin-right:.5em;vertical-align:initial;float:left}.structured-document .diff_document .paragraph>.number sup,.structured-document .document .paragraph>.number sup{font-size:1em;vertical-align:top;line-height:1em}.structured-document .diff_document table.enumeration_item,.structured-document .document table.enumeration_item{width:100%;border:0}.structured-document .diff_document table.enumeration_item tr,.structured-document .document table.enumeration_item tr{border:0}.structured-document .diff_document table.enumeration_item td,.structured-document .document table.enumeration_item td{border:0;vertical-align:top;text-align:left}.structured-document .diff_document table.enumeration_item td.number,.structured-document .document table.enumeration_item td.number{width:2.5em;white-space:nowrap}.structured-document .diff_document table.enumeration_item td.right_col,.structured-document .document table.enumeration_item td.right_col{text-align:right;white-space:nowrap;vertical-align:bottom}.structured-document .diff_document table.enumeration_tabular,.structured-document .document table.enumeration_tabular{margin-top:.5em;margin-bottom:.5em}.structured-document .diff_document table.enumeration_tabular td,.structured-document .diff_document table.enumeration_tabular th,.structured-document .document table.enumeration_tabular td,.structured-document .document table.enumeration_tabular th{text-align:left;vertical-align:middle;border:1px solid #000;border-collapse:collapse;padding:0 .3em}.structured-document .diff_document table.enumeration_tabular th,.structured-document .document table.enumeration_tabular th{font-style:italic;background-color:#e5e5e5}.structured-document .diff_document a.footnote,.structured-document .document a.footnote{font-size:7.5pt;vertical-align:top;cursor:pointer}.structured-document .diff_document span.inline_footnote,.structured-document .document span.inline_footnote{background-color:#ededff;-moz-border-radius:.3em;padding-left:.2em;padding-right:.2em}.structured-document .diff_document a.law_link,.structured-document .document a.law_link{background:#0000 url(/images/icons_custom/link_external.png) no-repeat scroll 100% 0;padding-right:1rem}.structured-document .diff_document a.annex_link.not_linked.abrogated.icon,.structured-document .document a.annex_link.not_linked.abrogated.icon{padding-left:23px;background:#0000 url(/images/doc_icons/pdf.png) no-repeat 2px 0}.structured-document .diff_document .smallcaps,.structured-document .document .smallcaps{font-feature-settings:"smcp";font-variant:small-caps}.structured-document .diff_document .inline_image,.structured-document .document .inline_image{display:flex;justify-content:center}.structured-document div.footnotes .footnotes-hr{border-top:1px solid #000;margin-top:45px;margin-bottom:5px;width:100px}.structured-document div.footnotes ol{list-style-type:none}.structured-document .document{width:100%}.structured-document .document .article>.article_number{float:left;padding-right:5px}.structured-document .document .article>.article_title{margin-left:60px}.structured-document .document ul.annex_documents{padding-top:0;padding-left:1rem;margin-top:.5em}.structured-document .document ul.annex_documents>li{list-style-type:none}.structured-document .document ul.annex_documents>li>a:first-child{background:none!important;padding-left:0!important}.structured-document .document ul.annex_documents>li>a:first-child:before{content:"";padding:4px 8px;display:inline-block;font-family:Glyphicons Regular;font-style:normal;font-weight:400;line-height:1;vertical-align:top;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.structured-document .document ul.annex_documents>li a.abrogated:hover{text-decoration:none}.structured-document .document h2.annex_title,.structured-document h2.annex_title{font-size:1.33em;font-weight:700;padding-top:2em;padding-bottom:.33em;clear:both}.structured-document .document .type-table{overflow-x:auto}.structured-document .diff_document{width:960px;border:1px solid #0000;border-bottom:"1px solid #999";border-right:"1px solid #999";border-collapse:collapse}.structured-document .diff_document tr{border-bottom:"1px dotted #bbb"}.structured-document .diff_document tr:hover{background-color:"#f5f5f5"}.structured-document .diff_document tr.borderless{border-bottom:1px solid #0000!important}.structured-document .diff_document td.new,.structured-document .diff_document td.old,.structured-document .diff_document th.new,.structured-document .diff_document th.old{vertical-align:top;padding-left:.6em;padding-right:.6em}.structured-document .diff_document th.new,.structured-document .diff_document th.old{background-color:#e5e5e5;color:#777;font-weight:700}.structured-document .diff_document th.new span.proceeding_version_author,.structured-document .diff_document th.old span.proceeding_version_author{color:#aaa}.structured-document .diff_document td,.structured-document .diff_document th{border-left:1px solid #0000;border-bottom:"1px dotted #bbb";border-top:"1px dotted #bbb"}.structured-document .diff_document td.old,.structured-document .diff_document th.old{border-right:"1px solid #bbb"}.structured-document .diff_document .remark{font-style:italic}.structured-document .diff_document .remark.chd,.structured-document .diff_document .remark.del,.structured-document .diff_document .remark.mt{background-color:#ffe0e0;-moz-border-radius:.3em;padding-left:.2em;padding-right:.2em}.structured-document .diff_document ul.annex_documents{padding-top:1em;padding-bottom:.5em}.structured-document #modification_table{overflow-x:auto;white-space:nowrap}@media (min-width: 992px){.structured-document #modification_table{white-space:normal}}.structured-document #modification_table h1{font-size:1.33em;font-weight:700;padding-top:2em;padding-bottom:.33em}.structured-document #modification_table table{margin-top:.5em;margin-bottom:.5em}.structured-document #modification_table table td,.structured-document #modification_table table th{text-align:left;vertical-align:middle;border:1px solid #000;border-collapse:collapse;padding:0 .3em}.structured-document #modification_table table th{font-style:italic;background-color:#e5e5e5;white-space:nowrap}.structured-document #modification_table td.ags_source_publication .ags-link{float:left}.structured-document #modification_table td.ags_source_publication .materials{float:right}.structured-document #modification_table td.ags_source_publication .materials .halflings{top:2px}.structured-document #modification_table td.ags_source_publication .materials .materials-count{margin-left:3px}.structured-document table.cac>tbody>tr>th.lang{border-top:4px solid #333;background-color:#f3f3f2;color:#212529;border-right:1px solid #fff}.structured-document table.cac>tbody>tr>th.lang:last-child{border-right:none}.structured-document .document.cac,.structured-document .document.cac .footnotes,.structured-document .document.cac_unified,.structured-document .document.cac_unified .footnotes{padding:0}.structured-document table.cac{border-collapse:collapse;border-spacing:0;border:0;margin:0;padding:0;table-layout:fixed;width:100%}.structured-document table.cac>tbody>tr:last-child>td{padding-bottom:1rem}.structured-document table.cac>tbody>tr:last-child>td.show{padding-bottom:0}.structured-document table.cac>tbody>tr>th span{display:none}.structured-document table.cac>tbody>tr>td{vertical-align:top;border:0;margin:0;padding:0}.structured-document table.cac>tbody>tr>td.show,.structured-document table.cac>tbody>tr th.show{cursor:pointer;background-color:#eee;font-size:12px;color:#aaa;padding-left:1rem;padding-right:0}.structured-document table.cac>tbody>tr>td,.structured-document table.cac>tbody>tr th,.structured-document table.cac_unified>tbody>tr>td:not(.lang-badge){padding:0 1rem}.structured-document .hide-en table.cac_unified td.lang.en,.structured-document .hide-en table.cac_unified th.lang.en,.structured-document .hide-en table.cac td.lang.en,.structured-document .hide-en table.cac th.lang.en{display:none}.structured-document .en-0 table.cac_unified td.lang.en{background-color:#e9ecef}.structured-document .en-0 table.cac_unified td.lang.en.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .en-0 table.cac_unified td.lang.en.lang-badge div{transform:rotate(-90deg)}.structured-document .en-1 table.cac_unified td.lang.en{background-color:#dee2e6}.structured-document .en-1 table.cac_unified td.lang.en.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .en-1 table.cac_unified td.lang.en.lang-badge div{transform:rotate(-90deg)}.structured-document .en-2 table.cac_unified td.lang.en{background-color:#ced4da}.structured-document .en-2 table.cac_unified td.lang.en.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .en-2 table.cac_unified td.lang.en.lang-badge div{transform:rotate(-90deg)}.structured-document .en-3 table.cac_unified td.lang.en{background-color:#adb5bd}.structured-document .en-3 table.cac_unified td.lang.en.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .en-3 table.cac_unified td.lang.en.lang-badge div{transform:rotate(-90deg)}.structured-document .en-4 table.cac_unified td.lang.en{background-color:#6c757d}.structured-document .en-4 table.cac_unified td.lang.en.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .en-4 table.cac_unified td.lang.en.lang-badge div{transform:rotate(-90deg)}.structured-document .hide-de table.cac_unified td.lang.de,.structured-document .hide-de table.cac_unified th.lang.de,.structured-document .hide-de table.cac td.lang.de,.structured-document .hide-de table.cac th.lang.de{display:none}.structured-document .de-0 table.cac_unified td.lang.de{background-color:#e9ecef}.structured-document .de-0 table.cac_unified td.lang.de.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .de-0 table.cac_unified td.lang.de.lang-badge div{transform:rotate(-90deg)}.structured-document .de-1 table.cac_unified td.lang.de{background-color:#dee2e6}.structured-document .de-1 table.cac_unified td.lang.de.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .de-1 table.cac_unified td.lang.de.lang-badge div{transform:rotate(-90deg)}.structured-document .de-2 table.cac_unified td.lang.de{background-color:#ced4da}.structured-document .de-2 table.cac_unified td.lang.de.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .de-2 table.cac_unified td.lang.de.lang-badge div{transform:rotate(-90deg)}.structured-document .de-3 table.cac_unified td.lang.de{background-color:#adb5bd}.structured-document .de-3 table.cac_unified td.lang.de.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .de-3 table.cac_unified td.lang.de.lang-badge div{transform:rotate(-90deg)}.structured-document .de-4 table.cac_unified td.lang.de{background-color:#6c757d}.structured-document .de-4 table.cac_unified td.lang.de.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .de-4 table.cac_unified td.lang.de.lang-badge div{transform:rotate(-90deg)}.structured-document .hide-fr table.cac_unified td.lang.fr,.structured-document .hide-fr table.cac_unified th.lang.fr,.structured-document .hide-fr table.cac td.lang.fr,.structured-document .hide-fr table.cac th.lang.fr{display:none}.structured-document .fr-0 table.cac_unified td.lang.fr{background-color:#e9ecef}.structured-document .fr-0 table.cac_unified td.lang.fr.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .fr-0 table.cac_unified td.lang.fr.lang-badge div{transform:rotate(-90deg)}.structured-document .fr-1 table.cac_unified td.lang.fr{background-color:#dee2e6}.structured-document .fr-1 table.cac_unified td.lang.fr.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .fr-1 table.cac_unified td.lang.fr.lang-badge div{transform:rotate(-90deg)}.structured-document .fr-2 table.cac_unified td.lang.fr{background-color:#ced4da}.structured-document .fr-2 table.cac_unified td.lang.fr.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .fr-2 table.cac_unified td.lang.fr.lang-badge div{transform:rotate(-90deg)}.structured-document .fr-3 table.cac_unified td.lang.fr{background-color:#adb5bd}.structured-document .fr-3 table.cac_unified td.lang.fr.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .fr-3 table.cac_unified td.lang.fr.lang-badge div{transform:rotate(-90deg)}.structured-document .fr-4 table.cac_unified td.lang.fr{background-color:#6c757d}.structured-document .fr-4 table.cac_unified td.lang.fr.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .fr-4 table.cac_unified td.lang.fr.lang-badge div{transform:rotate(-90deg)}.structured-document .hide-it table.cac_unified td.lang.it,.structured-document .hide-it table.cac_unified th.lang.it,.structured-document .hide-it table.cac td.lang.it,.structured-document .hide-it table.cac th.lang.it{display:none}.structured-document .it-0 table.cac_unified td.lang.it{background-color:#e9ecef}.structured-document .it-0 table.cac_unified td.lang.it.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .it-0 table.cac_unified td.lang.it.lang-badge div{transform:rotate(-90deg)}.structured-document .it-1 table.cac_unified td.lang.it{background-color:#dee2e6}.structured-document .it-1 table.cac_unified td.lang.it.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .it-1 table.cac_unified td.lang.it.lang-badge div{transform:rotate(-90deg)}.structured-document .it-2 table.cac_unified td.lang.it{background-color:#ced4da}.structured-document .it-2 table.cac_unified td.lang.it.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .it-2 table.cac_unified td.lang.it.lang-badge div{transform:rotate(-90deg)}.structured-document .it-3 table.cac_unified td.lang.it{background-color:#adb5bd}.structured-document .it-3 table.cac_unified td.lang.it.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .it-3 table.cac_unified td.lang.it.lang-badge div{transform:rotate(-90deg)}.structured-document .it-4 table.cac_unified td.lang.it{background-color:#6c757d}.structured-document .it-4 table.cac_unified td.lang.it.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .it-4 table.cac_unified td.lang.it.lang-badge div{transform:rotate(-90deg)}.structured-document .hide-rm table.cac_unified td.lang.rm,.structured-document .hide-rm table.cac_unified th.lang.rm,.structured-document .hide-rm table.cac td.lang.rm,.structured-document .hide-rm table.cac th.lang.rm{display:none}.structured-document .rm-0 table.cac_unified td.lang.rm{background-color:#e9ecef}.structured-document .rm-0 table.cac_unified td.lang.rm.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .rm-0 table.cac_unified td.lang.rm.lang-badge div{transform:rotate(-90deg)}.structured-document .rm-1 table.cac_unified td.lang.rm{background-color:#dee2e6}.structured-document .rm-1 table.cac_unified td.lang.rm.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .rm-1 table.cac_unified td.lang.rm.lang-badge div{transform:rotate(-90deg)}.structured-document .rm-2 table.cac_unified td.lang.rm{background-color:#ced4da}.structured-document .rm-2 table.cac_unified td.lang.rm.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .rm-2 table.cac_unified td.lang.rm.lang-badge div{transform:rotate(-90deg)}.structured-document .rm-3 table.cac_unified td.lang.rm{background-color:#adb5bd}.structured-document .rm-3 table.cac_unified td.lang.rm.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .rm-3 table.cac_unified td.lang.rm.lang-badge div{transform:rotate(-90deg)}.structured-document .rm-4 table.cac_unified td.lang.rm{background-color:#6c757d}.structured-document .rm-4 table.cac_unified td.lang.rm.lang-badge{color:#212529;background-color:#f3f3f2;border-bottom:1px solid #fff;border-left:4px solid #333}.structured-document .rm-4 table.cac_unified td.lang.rm.lang-badge div{transform:rotate(-90deg)}.structured-document .document .article_number_chrono,.structured-document .document .final_clause_title,.structured-document .document .ingress_change_action,.structured-document .document .ingress_change_text,.structured-document .document .summary_main_modification{font-weight:700}.structured-document .document .title_abrogated{font-style:italic}.structured-document .document .title_changed{font-weight:700}.structured-document .document .article_abrogated{font-style:italic}.structured-document .document .article_changed{font-weight:700}.structured-document .document .paragraph_abrogated{font-style:italic}.structured-document .document .paragraph_changed{font-weight:700}.structured-document .document .listelement_abrogated{font-style:italic}.structured-document .document .listelement_changed{font-weight:700}.structured-document .document .annex_abrogated{font-style:italic}.structured-document .document .annex_changed{font-weight:700}.structured-document .document .table_abrogated{font-style:italic}.structured-document .document .table_changed{font-weight:700}.structured-document .document .section.title{font-size:1.33em;font-weight:700}.structured-document .document .ags_heading_changed{font-size:1.33em}.structured-document .document .ingress_change{padding-top:1.33em;padding-bottom:.11em}.structured-document .document hr{border-top:1px solid #000}.structured-document .diff_document table.enumeration_tabular [class$=muted],.structured-document .document table.enumeration_tabular [class$=muted]{background-color:#e5e5e5}.structured-document .diff_document table.enumeration_tabular [class$=highlight],.structured-document .document table.enumeration_tabular [class$=highlight]{background-color:#fff}.structured-document h1,.structured-document h2,.structured-document h3,.structured-document h4,.structured-document h5{color:#000!important}.structured-document .footnotes ol{padding-left:0}.structured-document p{margin-bottom:0}.structured-document a.footnote.target,.structured-document a.footnote:target{background-color:#ff9}html{height:100%}.structured-document{margin-top:1rem}',
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/api\/de\/texts_of_law/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsSearch($pageCrawl)) {
            $this->handleForSearch($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/api\/de\/texts_of_law/')) {
            $this->handleMeta($pageCrawl);
            $this->handleContent($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            $this->handleForUpdates($pageCrawl);

            if ($this->arachno->matchUrl($pageCrawl, '/download_pdf_file\?/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    protected function handleForSearch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/\/api\/de\/texts_of_law/')) {
            $items = $pageCrawl->getJson();
            foreach ($items as $category) {
                foreach ($category as $item) {
                    $id = $item['systematic_number'];
                    $link = "https://www.gesetzessammlung.bs.ch/api/de/texts_of_law/{$id}/show_as_json";
                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
                }
            }
        }
        if ($this->arachno->matchUrl($pageCrawl, '/app\/de\/texts_of_law/')) {
            $url = $pageCrawl->pageUrl->url;
            $docId = Str::of($url)->after('texts_of_law/');

            $link = "https://www.gesetzessammlung.bs.ch/api/de/texts_of_law/{$docId}/show_as_json";
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]), true);
        } else {
            if ($this->arachno->matchUrl($pageCrawl, '/api\/de\/texts_of_law/')) {
                $fullContent = $this->getFullContent($pageCrawl);

                $body = "<main id='page-content'><div class='structured-document'><div class='document'>{$fullContent['content']}</div></div></main></clex-root>";
                $pageCrawl->domCrawler->addContent($body, 'text/html');

                $this->arachno->indexForSearch(
                    $pageCrawl,
                    contentQuery: new DomQuery(['query' => '#page-content', 'type' => DomQueryType::CSS]),
                    primaryLocationId: 163459,
                    languageCode: 'deu',
                );
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $items = $pageCrawl->getJson();
        foreach ($items as $category => $group) {
            foreach ($group as $item) {
                $id = $item['systematic_number'];
                $title = $item['title'];

                $catDoc = new CatalogueDoc([
                    'source_unique_id' => $id,
                    'title' => $title,
                    'view_url' => "https://www.gesetzessammlung.bs.ch/app/de/texts_of_law/{$id}",
                    'start_url' => "https://www.gesetzessammlung.bs.ch/api/de/texts_of_law/{$id}/show_as_json",
                    'primary_location_id' => '163459',
                    'language_code' => 'deu',
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

                $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;

                //                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => 'https://www.gesetzessammlung.bs.ch/api/de/systematic_categories?show_abrogated=false']));
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string, mixed>
     */
    protected function getDocMeta(PageCrawl $pageCrawl): array
    {
        $contentItem = $pageCrawl->getJson();

        $downloadLink = $contentItem['text_of_law']['pdf_link'] ?? null;
        $docDate = $contentItem['text_of_law']['enactment'] ?? null;

        return [
            'download_link' => $downloadLink,
            'work_date' => $docDate,
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $meta = $this->getDocMeta($pageCrawl);
        $downloadUrl = $meta['download_link'];
        $workDate = $meta['work_date'];

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '163459');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');

        try {
            $workDate = Carbon::parse($workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $workDate);
        } catch (InvalidFormatException) {
        }
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $node
     * @param string               $pageURl
     * @param int                  $level
     *
     * @return array<string, mixed>
     */
    protected function extractContent(PageCrawl $pageCrawl, array $node, string $pageURl, int $level): array
    {
        $content = [];
        $toc = [];
        $forToc = ['title', 'article'];

        $id = preg_replace('/[-‐]+/', '_', $node['uid']);
        $label = strip_tags($node['html_content']['de']);

        /** @var string $label */
        $label = preg_replace('/\n+/', '', $label);
        /** @var string $label */
        $label = preg_replace('/&sect;/', '§', $label);

        $label = str_replace(['&', ';', 'nbsp'], '', $label);

        if (!empty($label) && in_array($node['type'] ?? '', $forToc)) {
            $toc[] = new TocItemDraft([
                'href' => "{$pageURl}#{$id}",
                'label' => trim($label),
                'level' => $level,
                'source_unique_id' => $id,
            ]);
        }

        $content[] = "<a id='{$id}'></a>{$node['html_content']['de']}";

        if ($node['children'] ?? '') {
            foreach ($node['children'] as $child) {
                $generated = $this->extractContent($pageCrawl, $child, $pageURl, $level + 1);
                $content[] = $generated['content'];
                $toc = [...$toc, ...$generated['toc']];
            }
        }

        if ($node['html_content_post']['de'] ?? null) {
            $content[] = $node['html_content_post']['de'];
        }

        $allContent = implode('', $content);

        return [
            'content' => str_replace('/\n+/', '', $allContent),
            'toc' => $toc,
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string, mixed>
     */
    protected function getFullContent(PageCrawl $pageCrawl): array
    {
        $content = $pageCrawl->getJson();
        $header = $content['text_of_law']['selected_version']['json_content']['document']['header']['de'] ?? '';
        $footer = $content['text_of_law']['selected_version']['json_content']['document']['footer']['de'] ?? '';
        $footnotes = $content['text_of_law']['selected_version']['json_content']['footnotes']['de'] ?? '';

        $generated = $this->extractContent($pageCrawl, $content['text_of_law']['selected_version']['json_content']['document']['content'], $pageCrawl->pageUrl->url, 0);

        return [
            'content' => $header . $generated['content'] . $footer . $footnotes,
            'toc' => $generated['toc'],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleContent(PageCrawl $pageCrawl): void
    {
        $fullContent = $this->getFullContent($pageCrawl);

        $body = "<main id='page-content'><div class='structured-document'><div class='document'>{$fullContent['content']}</div></div></main></clex-root>";

        $pageCrawl->domCrawler->addContent($body, 'text/html');

        $this->capturePageContent($pageCrawl);

        $toc = $fullContent['toc'];

        $this->arachno->captureTocFromDraftArray($pageCrawl, $toc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function capturePageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#page-content',
            ],
        ]);

        $this->arachno->capture($pageCrawl, $bodyQueries);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $items = $pageCrawl->getJson();
        $currentMonth = 'c' . Carbon::now()->format('Ym');
        $previousMonth = 'c' . Carbon::now()->subMonth()->format('Ym');
        $validMonths = [$currentMonth, $previousMonth];

        foreach ($validMonths as $validMonth) {
            if (isset($items[$validMonth])) {
                foreach ($items[$validMonth] as $item) {
                    $title = $item['document_title'];
                    $uniqueId = $item['number'];
                    $docId = $item['id'];
                    $date = $item['date_string'];

                    $docMetaDto = new DocMetaDto();

                    $docMetaDto->title = $title;
                    $docMetaDto->source_unique_id = $uniqueId;
                    $docMetaDto->primary_location = '163459';
                    $docMetaDto->language_code = 'deu';
                    $docMetaDto->source_url = "https://www.gesetzessammlung.bs.ch/app/de/change_documents/{$docId}";
                    $docMetaDto->download_url = "https://www.gesetzessammlung.bs.ch/frontend/change_document_file_dictionaries/{$docId}/download_pdf_file?locale=de";
                    $docMetaDto->work_type = 'law';
                    $docMetaDto->work_number = $uniqueId;

                    try {
                        $docMetaDto->work_date = Carbon::parse($date);
                    } catch (InvalidFormatException) {
                    }

                    $link = new UrlFrontierLink(['url' => $docMetaDto->download_url]);
                    $link->_metaDto = $docMetaDto;
                    $this->arachno->followLink($pageCrawl, $link, true);
                }
            }
        }
    }
}
