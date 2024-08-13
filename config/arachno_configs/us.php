<?php

use App\Services\Arachno\Crawlers\Sources\Us\AMLegal;
use App\Services\Arachno\Crawlers\Sources\Us\Ca\CityOfSanRamon;
use App\Services\Arachno\Crawlers\Sources\Us\Ca\SacramentoMetropolitan;
use App\Services\Arachno\Crawlers\Sources\Us\Ca\SanMateoLawLibrary;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerAK;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerAZ;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerCA;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerCD;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerCO;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerDE;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerFL;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerHI;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerIA;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerID;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerIL;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerIN;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerMA;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerMD;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerME;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerMN;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerMT;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerND;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerNE;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerNH;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerNJ;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerNM;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerNV;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerNY;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerOH;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerOR;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerPA;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerRI;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerSD;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerTX;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerUS;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerUSCFR;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerUT;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerVA;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerVT;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerWA;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerWI;
use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerWV;
use App\Services\Arachno\Crawlers\Sources\Us\CodePublishing;
use App\Services\Arachno\Crawlers\Sources\Us\DouglasCountyColorado;
use App\Services\Arachno\Crawlers\Sources\Us\Ecode360Ordinances;
use App\Services\Arachno\Crawlers\Sources\Us\FederalRegister;
use App\Services\Arachno\Crawlers\Sources\Us\LegiscanBills;
use App\Services\Arachno\Crawlers\Sources\Us\Municode;
use App\Services\Arachno\Crawlers\Sources\Us\MunicodeOrdinances;
use App\Services\Arachno\Crawlers\Sources\Us\Or\ClackamasCountyCode;
use App\Services\Arachno\Crawlers\Sources\Us\Qcode;
use App\Services\Arachno\Crawlers\Sources\Us\QcodeOrdinances;
use App\Services\Arachno\Crawlers\Sources\Us\QcodeOrdinancesCustomCodes;
use App\Services\Arachno\Crawlers\Sources\Us\SandiegoCityOrdinances;
use App\Services\Arachno\Crawlers\Sources\Us\StateOfCalifornia;

return [
    'us-amlegal' => AMLegal::class,
    'us-casemaker-ak' => CaseMakerAK::class,
    'us-casemaker-az' => CaseMakerAZ::class,
    'us-casemaker-ca' => CaseMakerCA::class,
    'us-casemaker-cd' => CaseMakerCD::class,
    'us-casemaker-co' => CaseMakerCO::class,
    'us-casemaker-de' => CaseMakerDE::class,
    'us-casemaker-fl' => CaseMakerFL::class,
    'us-casemaker-hi' => CaseMakerHI::class,
    'us-casemaker-ia' => CaseMakerIA::class,
    'us-casemaker-id' => CaseMakerID::class,
    'us-casemaker-il' => CaseMakerIL::class,
    'us-casemaker-in' => CaseMakerIN::class,
    'us-casemaker-ma' => CaseMakerMA::class,
    'us-casemaker-md' => CaseMakerMD::class,
    'us-casemaker-me' => CaseMakerME::class,
    'us-casemaker-mn' => CaseMakerMN::class,
    'us-casemaker-mt' => CaseMakerMT::class,
    'us-casemaker-nd' => CaseMakerND::class,
    'us-casemaker-ne' => CaseMakerNE::class,
    'us-casemaker-nh' => CaseMakerNH::class,
    'us-casemaker-nj' => CaseMakerNJ::class,
    'us-casemaker-nm' => CaseMakerNM::class,
    'us-casemaker-nv' => CaseMakerNV::class,
    'us-casemaker-ny' => CaseMakerNY::class,
    'us-casemaker-oh' => CaseMakerOH::class,
    'us-casemaker-or' => CaseMakerOR::class,
    'us-casemaker-pa' => CaseMakerPA::class,
    'us-casemaker-ri' => CaseMakerRI::class,
    'us-casemaker-sd' => CaseMakerSD::class,
    'us-casemaker-tx' => CaseMakerTX::class,
    'us-casemaker-us-admin' => CaseMakerUSCFR::class,
    'us-casemaker-us-stat' => CaseMakerUS::class,
    'us-casemaker-ut' => CaseMakerUT::class,
    'us-casemaker-va' => CaseMakerVA::class,
    'us-casemaker-vt' => CaseMakerVT::class,
    'us-casemaker-wa' => CaseMakerWA::class,
    'us-casemaker-wi' => CaseMakerWI::class,
    'us-casemaker-wv' => CaseMakerWV::class,
    'us-clackamas' => ClackamasCountyCode::class,
    'us-codebook' => CodePublishing::class,
    'us-dc-colorado' => DouglasCountyColorado::class,
    'us-ecode-360-ordinances' => Ecode360Ordinances::class,
    'us-federal-register' => FederalRegister::class,
    'us-law-cityofsanmateo' => SanMateoLawLibrary::class,
    'us-legiscan-bills' => LegiscanBills::class,
    'us-municode' => Municode::class,
    'us-municode-ordinances' => MunicodeOrdinances::class,
    'us-online-encodeplus' => CityOfSanRamon::class,
    'us-qcode' => Qcode::class,
    'us-qcode-ordinances' => QcodeOrdinances::class,
    'us-qcode-ordinances-custom-codes' => QcodeOrdinancesCustomCodes::class,
    'us-sacramento-airquality' => SacramentoMetropolitan::class,
    'us-sandiego-city-ordinances' => SandiegoCityOrdinances::class,
    'us-state-of-california' => StateOfCalifornia::class,
];
