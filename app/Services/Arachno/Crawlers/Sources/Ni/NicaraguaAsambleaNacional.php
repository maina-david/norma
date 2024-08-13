<?php

namespace App\Services\Arachno\Crawlers\Sources\Ni;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Corpus\ContentResourceStore;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: ni-nicaragua-asamblea-nacional
 * title: Nicaragua National Assembly
 * url: http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpMainDIL.xsp
 */
class NicaraguaAsambleaNacional extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ni-nicaragua-asamblea-nacional',
            'throttle_requests' => 500,
            'start_urls' => $this->getNicaraguaStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if (
            $this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl)
        ) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->matchCSS($pageCrawl, 'span.xspTextComputedField') || $this->arachno->matchCSS($pageCrawl, 'div.justifyText') || $this->arachno->matchCSS($pageCrawl, 'div.wrapper div.container div.row')) {
            $this->handleMeta($pageCrawl);
            $this->capturePageContent($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/\$FILE\/.*\.pdf$/')) {
            $this->handleMeta($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/.*\.pdf$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDocs(): array
    {
        return [
            '53F80DDC11A73909062587FF006BC2B3' => [
                'title' => 'TEXTO CONSOLIDADO, LEY ESPECIAL DE PRESTACIONES DE SEGURIDAD SOCIAL PARA LOS TRABAJADORES MINEROS',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=53F80DDC11A73909062587FF006BC2B3&action=?openDocument',
            ],
            'DDD3E43339DDD9AB062587FF00713F6F' => [
                'title' => 'TEXTO CONSOLIDADO, LEY ESPECIAL PARA LAS PENSIONES DE LOS SERVIDORES PÚBLICOS',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=DDD3E43339DDD9AB062587FF00713F6F&action=?openDocument',
            ],
            '9f2f848b62c936d1062577b2005f30a9' => [
                'title' => 'Reglamento de la Ley de Aguas Nacionales (Decreto No. 44-2010)',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/4c9d05860ddef1c50625725e0051e506/9f2f848b62c936d1062577b2005f30a9?OpenDocument',
            ],
            '118cd18ae56a4f320625878f006e8599' => [
                'title' => 'Reglamento de la Ley de Minería (Decreto No. 38-99)',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/118cd18ae56a4f320625878f006e8599?OpenDocument',
            ],
            'e6b32d91d9b2f6e8062578a30071fddd' => [
                'title' => 'Ley Especial de Gestión Integral de Residuos y Desechos Sólidos Peligrosos y no Peligrosos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/SILEG/Iniciativas.nsf/0/e6b32d91d9b2f6e8062578a30071fddd/$FILE/Articulado%20en%20Dictamen%20Ley%20General%20de%20Gestion%20Res%20Solidos.pdf',
            ],
            '5CF3B74FB659A05B062570A10057794B' => [
                'title' => 'Ley No. 286, Ley Especial de Exploración y Explotación de Hidrocarburos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/5CF3B74FB659A05B062570A10057794B?OpenDocument',
            ],
            '074b554afd1cf612062579d10059b7ee' => [
                'title' => 'Decreto Ejecutivo N°. 02-2012, Reglamento General de la Ley No. 765, Ley de Fomento a la Producción Agroecológica u Orgánica',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/3133c0d121ea3897062568a1005e0f89/074b554afd1cf612062579d10059b7ee?OpenDocument',
            ],
            'a35cf61591ad2d57062581f30056f9ec' => [
                'title' => 'Decreto Ejecutivo N°. 21-2017, Reglamento en el que se Establecen las Disposiciones para el Vertido de Aguas Residuales',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/a35cf61591ad2d57062581f30056f9ec?OpenDocument',
            ],
            '2aa845f404d355c6062583a0005a2819' => [
                'title' => 'Decreto Presidencial N°. 07-2019, Decreto para Establecer la Política Nacional de Mitigación y Adaptación al Cambio Climático y de Creación del Sistema Nacional de Respuesta al Cambio Climático',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/9e314815a08d4a6206257265005d21f9/2aa845f404d355c6062583a0005a2819?OpenDocument',
            ],
            // 'MjcyOTY' => [
            //     'title' => 'Decreto Presidencial N°. 24-2019, Reglamento a la Ley N°. 807, Ley de Conservación y Utilización Sostenible de la Diversidad Biológica',
            //     'start_url' => 'http://digesto.asamblea.gob.ni/consultas/normas/shownorms.php?idnorm=MjcyOTY=',
            // ],
            '8454fd4d84260fbb06257132005245a7' => [
                'title' => 'Norma Técnica N°. NTON 05-004-01, Norma Técnica Ambiental para las Estaciones de Servicios Automotor',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/8454fd4d84260fbb06257132005245a7?OpenDocument',
            ],
            '26612f4ba125429c06257478005c9aa8' => [
                'title' => 'Norma Técnica N°. NTON 14 002-03, Norma Técnica y de Seguridad para Estaciones de Servicio Automotor y Estación de Servicio Marinas',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/26612f4ba125429c06257478005c9aa8?OpenDocument',
            ],
            '295ad76ad642745f062574de00644229' => [
                'title' => 'Norma Técnica N°. NTON 14 003-04, Norma Técnica Ambiental Obligatoria Nicaragüense para las Actividades de Exploración y Explotación de Hidrocarburos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/3133c0d121ea3897062568a1005e0f89/295ad76ad642745f062574de00644229?OpenDocument',
            ],
            'ecc60a4858d69c4d06258550007b242e' => [
                'title' => 'Norma Técnica N°. NTON 05-29-06, Norma Técnica Obligatoria Nicaragüense para las Actividades Mineras No Metálicas',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/ecc60a4858d69c4d06258550007b242e?OpenDocument',
            ],
            'b4c4b19ebe41926806257a1a0061528e' => [
                'title' => 'Norma Técnica N°. NTON 05 032-10, Norma Técnica Obligatoria Nicaragüense para el Manejo Ambiental de Aceites Lubricantes Usados',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/bbe90a5bb646d50906257265005d21f8/b4c4b19ebe41926806257a1a0061528e?OpenDocument',
            ],
            '1A3A99B77290B980062573DF00594022' => [
                'title' => 'Norma Técnica N°. NTON 05 007-98, Norma Técnica Obligatoria Nicaragüense Norma para la Clasificación de los Recursos Hídricos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/1A3A99B77290B980062573DF00594022?OpenDocument',
            ],
            '29b81609b8726f49062570bc005fbb2c' => [
                'title' => 'Decreto Ejecutivo N°. 9-96, Reglamento de la Ley General del Medio Ambiente y los Recursos Naturales',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/164aa15ba012e567062568a2005b564b/29b81609b8726f49062570bc005fbb2c?OpenDocument',
            ],
            'a84a64cb33e8572d06258950005fa1e1' => [
                'title' => 'Decreto Ejecutivo N°. 32-97, Reglamento General para el Control de Emisiones de los Vehículos Automotores de Nicaragua',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/a84a64cb33e8572d06258950005fa1e1?OpenDocument',
            ],
            '92959331cbd9d177062570a100580c62' => [
                'title' => 'Decreto Ejecutivo N°. 14-99, Reglamento de Áreas Protegidas de Nicaragua',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/92959331cbd9d177062570a100580c62?OpenDocument',
            ],
            '14d2c21be4b13700062570a100580bf1' => [
                'title' => 'Decreto Ejecutivo N°. 91-2000, Reglamento Para el Control de Sustancias que Agotan la Capa de Ozono',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/14d2c21be4b13700062570a100580bf1?OpenDocument',
            ],
            'c60847e89df22a6e062570a100581b30' => [
                'title' => 'Decreto Ejecutivo N°. 90-2001, Que Establece la Política General para el Ordenamiento Territorial',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/c60847e89df22a6e062570a100581b30?OpenDocument',
            ],
            '8EF13C9151671220062570A100581043' => [
                'title' => 'Decreto Ejecutivo N°. 107-2001, Que Establece la Política Nacional de los Recursos Hídricos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/8EF13C9151671220062570A100581043?OpenDocument',
            ],
            '3978e356f78cc1db062570ce005cc0d4' => [
                'title' => 'Decreto Ejecutivo N°. 78-2002, De Normas Pautas y Criterios para el Ordenamiento Territorial',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b34f77cd9d23625e06257265005d21fa/3978e356f78cc1db062570ce005cc0d4?OpenDocument',
            ],
            '89436DD30CA2DCDF062570F9005B113B' => [
                'title' => 'Decreto Ejecutivo N°. 36-2002, Para la Administración del Sistema de Permiso y Evaluación de Impacto Ambiental en las Regiones Autónomas de la Costa Atlántica',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/89436DD30CA2DCDF062570F9005B113B',
            ],
            'd132318726051846062570ab0064017d' => [
                'title' => 'Decreto Ejecutivo N°. 47-2005, Política Nacional Sobre Gestión Integral de Residuos Sólidos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b34f77cd9d23625e06257265005d21fa/d132318726051846062570ab0064017d?OpenDocument',
            ],
            '1b5efb1e58d7618a0625711600561572' => [
                'title' => 'Ley N°. 217, Ley General del Medio Ambiente y los Recursos Naturales',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/1b5efb1e58d7618a0625711600561572?OpenDocument',
            ],
            '33CA55EBEAEC13C6062572A0006C725A' => [
                'title' => 'Decreto Ejecutivo N°. 01-2007, Reglamento de Áreas Protegidas de Nicaragua',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/%28$All%29/33CA55EBEAEC13C6062572A0006C725A?OpenDocument',
            ],
            // '2220' => [
            //     'title' => 'Decreto Ejecutivo N°. 96-2007, Reglamento de la Ley General de Higiene y Seguridad del Trabajo',
            //     'start_url' => 'https://www.ilo.org/dyn/travail/docs/2220/OSH%20REGULATIONS.pdf',
            //     // 'doc_type' => 'PDF', Also a different source
            // ],
            'bf8bfb48e8030d460625863f006d5e69' => [
                'title' => 'Ley N°. 1027, Ley del Digesto Jurídico Nicaragüense de la Materia Laboral',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/bf8bfb48e8030d460625863f006d5e69?OpenDocument',
            ],
            'C0C1931F74480A55062573760075BD4B' => [
                'title' => 'Ley N°. 620, Ley General de Aguas Nacionales',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/C0C1931F74480A55062573760075BD4B',
            ],
            'cf820e2a63b1b690062578b00074ec1b' => [
                'title' => 'Ley de Protección y Bienestar Animal',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/cf820e2a63b1b690062578b00074ec1b',
            ],
            '1A666D4D9929B0F6062570A100583F5F' => [
                'title' => 'Ley de Pesca y Acuicultura',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/1A666D4D9929B0F6062570A100583F5F?OpenDocument',
            ],
            '525593f05f79d1bd062570a100584921' => [
                'title' => 'Ley de Promoción de Energías Renovables',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/525593f05f79d1bd062570a100584921?OpenDocument',
            ],
            '40fc19c963eeca6a062575c80075b195' => [
                'title' => 'Ley Nº 677, Ley Especial para el Fomento de la Construcción de Vivienda de Acceso a la Vivienda de Interés Social',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/40fc19c963eeca6a062575c80075b195?OpenDocument',
            ],
            '762e3767c1146734062578fc00550bbe' => [
                'title' => 'Ley No. 765, Ley de Fomento a la Producción Agroecológica u Orgánica',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/bbe90a5bb646d50906257265005d21f8/762e3767c1146734062578fc00550bbe?OpenDocument',
            ],
            '3d7b0c9bf4c186790625764e005d16f4' => [
                'title' => 'Norma Técnica Obligatoria Nicaragüense Ambiental para la Gestión Integral de los Residuos Sólidos no Peligrosos y el Reciclaje',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/3d7b0c9bf4c186790625764e005d16f4?OpenDocument',
            ],
            '642289dea62037c5062570af005a1811' => [
                'title' => 'LEY BÁSICA DE SALUD ANIMAL Y SANIDAD VEGETAL',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/642289dea62037c5062570af005a1811?OpenDocument',
            ],
            '20db2ca3ec996c410625853e00593ada' => [
                'title' => 'LEY DE PROTECCIÓN FITOSANITARIA DE NICARAGUA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/20db2ca3ec996c410625853e00593ada?OpenDocument',
            ],
            '6b731be4e96f5e9406257ab4007383c6' => [
                'title' => 'LEY N°. 807 LEY DE CONSERVACIÓN Y UTILIZACIÓN SOSTENIBLE DE LA DIVERSIDAD BIOLÓGICA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/6b731be4e96f5e9406257ab4007383c6?OpenDocument',
            ],
            'dfacdd675534dace0625744b0077c73f' => [
                'title' => 'LEY N°. 648 LEY DE IGUALDAD DE DERECHOS Y OPORTUNIDADES',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/dfacdd675534dace0625744b0077c73f?OpenDocument',
            ],
            '350902febc3c96af062577a7005dbc13' => [
                'title' => 'REGLAMENTO DE LA LEY N°. 648, LEY DE IGUALDAD DE DERECHOS Y OPORTUNIDADES',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/350902febc3c96af062577a7005dbc13?OpenDocument',
            ],
            'e903224025bb7233062570a500530fb7' => [
                'title' => 'LEY GENERAL DE CATASTRO NACIONAL',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/e903224025bb7233062570a500530fb7?OpenDocument',
            ],
            'fc634ed4cea3ff2b062579180079e4c8' => [
                'title' => 'REGLAMENTO DE LA LEY DE SUMINISTRO DE HIDROCARBUROS',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b34f77cd9d23625e06257265005d21fa/fc634ed4cea3ff2b062579180079e4c8?OpenDocument',
            ],
            'cc5fab07bcb8f3ad062570a100584ee3' => [
                'title' => 'LEY DE SUMINISTRO DE HIDROCARBUROS LEY N°. 277',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/cc5fab07bcb8f3ad062570a100584ee3?OpenDocument',
            ],
            // '26612f4ba125429c06257478005c9aa8' => [
            //     'title' => 'NORMA TÉCNICA N°. NTON 14 002-03 NORMA TÉCNICA Y DE SEGURIDAD PARA ESTACIONES DE SERVICIO AUTOMOTOR Y ESTACIÓN DE SERVICIO MARINAS',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/26612f4ba125429c06257478005c9aa8?OpenDocument',
            //      Possible duplicate
            // ],
            '3c2354e26d2f6c42062589500071bacd' => [
                'title' => 'TEXTO CONSOLIDADO, PARA LA ADMINISTRACIÓN DEL SISTEMA DE EVALUACIÓN AMBIENTAL EN LAS REGIONES AUTÓNOMAS DE LA COSTA CARIBE',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/3c2354e26d2f6c42062589500071bacd?OpenDocument',
            ],
            'fa251b3c54f5baef062571c40055736c' => [
                'title' => 'Codigo del Trabajo',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/fa251b3c54f5baef062571c40055736c?OpenDocument',
            ],
            'dff5d30488273f74062570a100583551' => [
                'title' => 'LEY N°. 456 LEY DE ADICIÓN DE RIESGOS Y ENFERMEDADES PROFESIONALES A LA LEY N°. 185, CÓDIGO DEL TRABAJO',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/dff5d30488273f74062570a100583551?OpenDocument',
            ],
            'c98fed94c2b5aaf406257aed005585f1' => [
                'title' => 'Ley N°. 815, Código Procesal del Trabajo y de la Seguridad Social de Nicaragua',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/c98fed94c2b5aaf406257aed005585f1?OpenDocument',
            ],
            '748a6ecb3ad25641062578b10073144b' => [
                'title' => 'LEY N°. 757, LEY DE TRATO DIGNO Y EQUITATIVO A PUEBLOS INDÍGENAS Y AFRO-DESCENDIENTES',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/748a6ecb3ad25641062578b10073144b?OpenDocument',
            ],
            '58f5f2ed6cab86c6062574ff0054b96e' => [
                'title' => 'Ley N°. 664, Ley General de Inspección del Trabajo',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/58f5f2ed6cab86c6062574ff0054b96e?OpenDocument',
            ],
            '76181d805959b777062578b1005cd8bc' => [
                'title' => 'ACUERDO MINISTERIAL N°. JCHG-02-02-11 NORMATIVA DE ATENCIÓN A LA PERSONA ADOLESCENTE TRABAJADORA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/76181d805959b777062578b1005cd8bc?OpenDocument',
            ],
            '837ba29e434563ac06257bfe007945ce' => [
                'title' => 'LEY N°. 443 LEY DE EXPLORACIÓN Y EXPLOTACIÓN DE RECURSOS GEOTÉRMICOS',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/837ba29e434563ac06257bfe007945ce?OpenDocument',
            ],
            '4dc8e799b8201a02062570a1005777c0' => [
                'title' => 'LEY N°. 272 LEY DE LA INDUSTRIA ELÉCTRICA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/4dc8e799b8201a02062570a1005777c0?OpenDocument',
            ],
            '419eb448baabfe7d0625878f006dfeea' => [
                'title' => 'DECRETO EJECUTIVO 42-98 REGLAMENTO DE LA LEY DE LA INDUSTRIA ELÉCTRICA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/419eb448baabfe7d0625878f006dfeea?OpenDocument',
            ],
            '824ee302eb60d1c0062570a60067a3df' => [
                'title' => 'Reglamento de la Ley de Pesca y Acuicultura (Decreto No. 23-2002)',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/824ee302eb60d1c0062570a60067a3df?OpenDocument',
            ],
            'bd325486f010cc8206257c24007776d8' => [
                'title' => 'Reglamento de la Ley de Protección de los Consumidores (Decreto No. 42-2003)',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b34f77cd9d23625e06257265005d21fa/bd325486f010cc8206257c24007776d8?OpenDocument',
            ],
            '141957E76D6550C706257657005FA5CD' => [
                'title' => 'Decreto Ejecutivo N°. 50-2009, Reglamento de la Ley Nº 677, Ley Especial para el Fomento de la Construcción de Vivienda de Acceso a la Vivienda de Interés Social',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/141957E76D6550C706257657005FA5CD?',
            ],
            '907d4e65c363cc8d062583520054fe79' => [
                'title' => 'Decreto Ejecutivo N°. 20-2017, Sistema de Evaluación Ambiental de Permisos y Autorizaciones para el Uso Sostenible de los Recursos Naturales',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/907d4e65c363cc8d062583520054fe79?OpenDocument',
            ],
            'e231d4330f6d5eac062586b50075b14a' => [
                'title' => 'Decreto Ejecutivo N°. 96-2007, Reglamento de la Ley General de Higiene y Seguridad del Trabajo',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/e231d4330f6d5eac062586b50075b14a?OpenDocument',
            ],
            '16624dbd812acc1b06257347006a6c8c' => [
                'title' => 'Ley N°. 618, Ley General de Higiene y Seguridad del Trabajo',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/16624dbd812acc1b06257347006a6c8c?OpenDocument',
            ],
            'b0f724194440805d062587f8006085fb' => [
                'title' => 'Decreto-Ley N°. 974, Ley de Seguridad Social',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/09cf45d6fc893868062572650059911e/b0f724194440805d062587f8006085fb?OpenDocument',
            ],
            'b56e2a9860626a2b062588000059dd68' => [
                'title' => 'REGLAMENTO GENERAL DE LA LEY DE SEGURIDAD SOCIAL DECRETO - LEY, aprobado el 26 de octubre de 2021',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/b56e2a9860626a2b062588000059dd68?OpenDocument',
            ],
            '0f963cae75ebd5dc0625715a005c0dc9' => [
                'title' => 'Decreto Ejecutivo N°. 001-2003, Reglamento de la Ley General de Salud',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/0f963cae75ebd5dc0625715a005c0dc9?OpenDocument',
            ],
            'FF82EA58EC7C712E062570A1005810E1' => [
                'title' => 'Ley N°. 423, Ley General de Salud',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/FF82EA58EC7C712E062570A1005810E1?OpenDocument',
            ],
            '3b3583b8c7d4ee32062579bc007b7023' => [
                'title' => 'Norma Técnica N°. NTON 05 027-05, Norma Técnica Ambiental para Regular los Sistemas de Tratamiento de Aguas Residuales y su Reuso',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/3b3583b8c7d4ee32062579bc007b7023?OpenDocument',
            ],
            // 'NjY3NA' => [
            //     'title' => 'LEY Nº. 387 LEY ESPECIAL DE EXPLORACIÓN Y EXPLOTACIÓN DE MINAS',
            //     'start_url' => 'http://digesto.asamblea.gob.ni/consultas/normas/shownorms.php?idnorm=NjY3NA==#:~:text=Art%C3%ADculo%201%20La%20presente%20Ley,los%20particulares%20entre%20s%C3%AD%20que',
            // ],
            'b6ee59fb75e2e20b06257bb900763f0b' => [
                'title' => 'Ley de Protección de los Consumidores',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/3133c0d121ea3897062568a1005e0f89/b6ee59fb75e2e20b06257bb900763f0b?OpenDocument',
            ],
            '1fe476f779dd609706257a070057be1e' => [
                'title' => 'ACUERDO MINISTERIAL No. JCHG-008-05-07 SOBRE EL CUMPLIMIENTO DE LA LEY 474 LEY DE REFORMA AL TÍTULO Vl, LIBRO PRIMERO DEL CÓDIGO DEL TRABAJO',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/1fe476f779dd609706257a070057be1e?OpenDocument',
            ],
            'f55e4b88eb35d80f062570a100578787' => [
                'title' => 'DECRETO N° 432- REGLAMENTO DE INSPECCIÓN SANITARIA. GACETA DIARIO OFICIAL N° 71 17/ABRIL/1989',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/f55e4b88eb35d80f062570a100578787?OpenDocument',
            ],
            'c4c86b1d10cedc190625711b005fdf5b' => [
                'title' => 'DECRETO N° 394- LEY DE DISPOSICIONES SANITARIAS. GACETA DIARIO OFICIAL N° 200 21/OCTUBRE/1998',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/3133c0d121ea3897062568a1005e0f89/c4c86b1d10cedc190625711b005fdf5b?OpenDocument',
            ],
            '7da0e342be9c44ef062570a100578297' => [
                'title' => 'DECRETO N° 229-LEY DE EXPROPIACIÓN. GACETA DIARIO OFICIAL N° 58 09/MARZO/1976',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/7da0e342be9c44ef062570a100578297?OpenDocument',
            ],
            '96f84dcd18edd95a0625723b00615816' => [
                'title' => 'DECRETO N° 77-2003- REGULACIÓN DE DESCARGAS DE AGUAS RESIDUALES DOMESTICAS PROVENIENTES DE LOS SISTEMAS EN TRATAMIENTO EN EL LAGO XOLOTLÁN. GACETA DIARIO OFICIAL N° 218 17/NOVIEMBRE/2003',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/96f84dcd18edd95a0625723b00615816?OpenDocument',
            ],
            '138a846c29f5f0760625717900509fa4' => [
                'title' => 'DECRETO N° 33-95- DISPOSICIONES PARA EL CONTROL DE AGUA RESIDUALES. GACETA DIARIO OFICIAL N° 118 26/06/1995',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/138a846c29f5f0760625717900509fa4?OpenDocument',
            ],
            '94bccaa76eb625bd062588e90054d69d' => [
                'title' => 'Constitución Politica de Nicaragua',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/09cf45d6fc893868062572650059911e/94bccaa76eb625bd062588e90054d69d?OpenDocument',
            ],
            '3c9ce07c25d44e18062573f3005f43ba' => [
                'title' => 'NORMA TÉCNICA PARA EL CONTROL AMBIENTAL DE LAS LAGUNAS CRATÉRICAS NORMA TÉCNICA NTON 05 002-99',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/3c9ce07c25d44e18062573f3005f43ba?OpenDocument',
            ],
            '68722115e0e27f50062573610072a1ab' => [
                'title' => 'Norma Técnica N°. NTON 05 013-01, Norma Técnica para el Control Ambiental de los Rellenos Sanitarios para Desechos Sólidos No Peligrosos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/68722115e0e27f50062573610072a1ab?OpenDocument',
            ],
            'f124ab4e19e485950625728a005c2c3f' => [
                'title' => 'Norma Técnica N°. NTON 05 015-02, Norma Técnica para el Manejo y Eliminación de Residuos Sólidos Peligrosos',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/bbe90a5bb646d50906257265005d21f8/f124ab4e19e485950625728a005c2c3f?OpenDocument',
            ],
            'A23D8CBBAE46A0AE062589B80076D9CB' => [
                'title' => 'ALIMENTOS. RASTROS. REQUISITOS HIGIÉNICOS Y SANITARIOS NORMA TÉCNICA N°. NTON 03001:2021',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=A23D8CBBAE46A0AE062589B80076D9CB&action=openDocument',
            ],
            'AF95B45DC1256DE9062589B9006BEA73' => [
                'title' => 'MEDIDAS SANITARIAS. MERCANCÍAS E INSUMOS PECUARIOS. IMPORTACIÓN NORMA TÉCNICA NTON N° 11005:2022',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=AF95B45DC1256DE9062589B9006BEA73&action=openDocument',
            ],
            '0BACCC4BFECB704006258789006AEF8A' => [
                'title' => 'DISEÑO DE SISTEMAS DE ABASTECIMIENTO. AGUA POTABLE NORMA TÉCNICA N°. NTON 09 007-19',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=0BACCC4BFECB704006258789006AEF8A&action=openDocument',
            ],
            // '7DC493DDCE0BBF87062587AC00519162' => [
            //     'title' => 'BUENAS PRÁCTICAS AGRÍCOLAS. REQUISITOS Y EVALUACIÓN DE LA CONFORMIDAD NORMA TÉCNICA N°. NTON 11 051-19',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=7DC493DDCE0BBF87062587AC00519162&action=openDocument',
            // ],
            '2E0402E967C3B9300625851C00515683' => [
                'title' => 'MEDIDAS FITOSANITARIAS. PRODUCTOS Y SUB PRODUCTOS DE ORIGEN VEGETAL PARA EXPORTACIÓN. CERTIFICACIÓN FITOSANTIARIA NORMA TÉCNICA N°. NTON 11 050-18',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=2E0402E967C3B9300625851C00515683&action=openDocument',
            ],
            'B841EFB7D53B6D87062574710052E8FD' => [
                'title' => 'CARNE Y PRODUCTOS CÁRNICOS. CARNE DE CERDO CRUDA EN CORTES Y SUS VÍSCERAS NORMA TÉCNICA N°. NTON 03107-17',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=B841EFB7D53B6D87062574710052E8FD&action=openDocument',
            ],
            '7FE1A2EA43395080062584270073BCE4' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. PESCA Y ACUICULTURA. IMPORTACIÓN Y MOVILIZACIÓN DE ANIMALES ACUÁTICOS. REQUISITOS SANITARIOS NORMA TÉCNICA N°. NTON 11 003-17',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=7FE1A2EA43395080062584270073BCE4&action=openDocument',
            ],
            'E8ED933F719217A4062584B2005501D2' => [
                'title' => 'PRODUCCIÓN AVÍAR. MEDIDAS SANITARIAS. INSPECCIÓN Y AUTORIZACIÓN DE ESTABLECIMIENTOS AVÍCOLAS. NORMA TÉCNICA N°. NTON 11 030-18',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=E8ED933F719217A4062584B2005501D2&action=openDocument',
            ],
            '94E124729ECEE6FF0625839700777474' => [
                'title' => 'DOSIMETRÍA. RADIACIÓN IONIZANTE, REQUISITOS PARA LOS LABORATORIOS DE CALIBRACIÓN Y USUARIOS NORMA TÉCNICA N°. NTON 07 008-18',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=94E124729ECEE6FF0625839700777474&action=openDocument',
            ],
            '22313562F0E0C3AE0625821800614B85' => [
                'title' => 'LECHE Y PRODUCTOS LÁCTEOS. LECHE CRUDA (VACA). ESPECIFICACIONES. NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE NORMA TÉCNICA N°. NTON 03 027- 17',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=22313562F0E0C3AE0625821800614B85&action=openDocument',
            ],
            'F74D4C1D4B3631B3062582180061ACAC' => [
                'title' => 'PRIMERA REVISIÓN. MEDIDAS FITOSANITARIAS. EMBALAJE DE MADERA NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE NORMA TÉCNICA N°. NTON 11 013-16',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=F74D4C1D4B3631B3062582180061ACAC&action=openDocument',
            ],
            '5314FCFC52D5C66F0625821800622A83' => [
                'title' => 'REGLAMENTO TÉCNICO CENTROAMERICANO. PLAGUICIDAS MICROBIOLÓGICOS DE USO AGRÍCOLA. REQUISITOS PARA EL REGISTRO NORMA TÉCNICA N°. NTON 02 014- 16/ RTCA 65.05.61:16',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=5314FCFC52D5C66F0625821800622A83&action=openDocument',
            ],
            '46A8ECD8B068A0C706258218006A18BB' => [
                'title' => 'REGLAMENTO TÉCNICO CENTROAMERICANO. FERTILIZANTES Y ENMIENDAS DE USO AGRÍCOLA. REQUISITOS PARA EL REGISTRO REGLAMENTO TÉCNICO CENTROAMERICANO N°. NTON 11 023 15/ RTCA 65.05.54:15',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=46A8ECD8B068A0C706258218006A18BB&action=openDocument',
            ],
            '1EE6B2123CB17B8E062581A1006E5132' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. DEPÓSITOS DE HIDROCARBUROS LÍQUIDOS. PARTE 1: INSTALACIONES INDUSTRIALES PARA CONSUMO DIRECTO NTON 14 024- 14:1 - NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=1EE6B2123CB17B8E062581A1006E5132&action=openDocument',
            ],
            '6880ED5ADDCCA290062582B9006E9A09' => [
                'title' => 'CERTIFICACIÓN DE LA NORMA OBLIGATORIA APROBADA NTON 03101-14 1 RTCA 67.04. 70:14 NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. PRODUCTOS LÁCTEOS. QUESOS. ESPECIFICACIONES - ANEXO DE LA RESOLUCIÓN NO. 366-2015 (COMIECO-LXXII) NORMA TÉCNICA NTON 03 101-14/RTCA 67.04.70:14',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=6880ED5ADDCCA290062582B9006E9A09&action=openDocument',
            ],
            '42DE22E4E66617D4062582BA0063DEE6' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. PRODUCTOS LÁCTEOS. CREMAS (NATAS) Y CREMAS (NATAS) PREPARADAS. ESPECIFICACIONES NORMAS TÉCNICA NTON No. 03 102-14/ RTCA 67.04.71:14',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=42DE22E4E66617D4062582BA0063DEE6&action=openDocument',
            ],
            '6CB771737933A73A062582CC0063D361' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. CARNE Y PRODUCTOS CÁRNICOS. EMBUTIDOS CÁRNICOS. CARACTERÍSTICAS Y ESPECIFICACIONES NORMA TÉCNICA N°. NTON 03 103-16',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=6CB771737933A73A062582CC0063D361&action=openDocument',
            ],
            'F17F64BF8024FBD6062581E00060300F' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. REGULACIÓN DE LA ACTIVIDAD AVÍCOLA NTON 11 029- 17',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=F17F64BF8024FBD6062581E00060300F&action=openDocument',
            ],
            'FE30536344458809062582100060F7D4' => [
                'title' => 'INSUMOS AGRÍCOLAS. BIO INSUMOS Y SUSTANCIAS AFINES. REQUISITOS DE REGISTRO NTON 11 048-16',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=FE30536344458809062582100060F7D4&action=openDocument',
            ],
            '874E61383812793506257D8200724DA7' => [
                'title' => 'LECHE PASTEURIZADA (PASTERIZADA), NTON 03 034-12',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=874E61383812793506257D8200724DA7&action=openDocument',
            ],
            '28B2CD3339E66CFE06257B940061EA41' => [
                'title' => 'INSTALACIONES DE TANQUES ESTACIONARIOS PARA ALMACENAMIENTO Y DISTRIBUCIÓN DE GAS LICUADO DE PETRÓLEO (GLP). ESPECIFICACIONES TÉCNICAS Y DE SEGURIDAD NORMA TÉCNICA N°. NTON 14 023-12',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=28B2CD3339E66CFE06257B940061EA41&action=openDocument',
            ],
            '8F95712340246921062586D40051210C' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE REQUISITOS AMBIENTALES PARA LA CONSTRUCCIÓN, OPERACIÓN Y CIERRE DE POZOS DE EXTRACCIÓN DE AGUA NORMA TÉCNICA N°. NTON-09-006-11',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=8F95712340246921062586D40051210C&action=openDocument',
            ],
            '4F1841AF1961FDF006257B9F0061BE28' => [
                'title' => 'REGENCIA DE ESTABLECIMIENTOS DE INSUMOS AGRICOLAS NTON 11 036 – 11',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=4F1841AF1961FDF006257B9F0061BE28&action=openDocument',
            ],
            '32D6AD99D191B0FE06257BC200799142' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE CARACTERIZACIÓN, REGULACIÓN Y CERTIFICACIÓN DE UNIDADES DE PRODUCCIÓN AGRO ECOLÓGICA NORMA TÉCNICA N°. NTON 11 037-12',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=32D6AD99D191B0FE06257BC200799142&action=openDocument',
            ],
            '021F3552B4D5781A06257C5C0074B434' => [
                'title' => 'PRODUCTOS UTILIZADOS EN ALIMENTACIÓN ANIMAL Y ESTABLECIMIENTOS. REQUISITOS DE REGISTRO SANITARIO Y CONTROL NTON 20 003-11',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=021F3552B4D5781A06257C5C0074B434&action=openDocument',
            ],
            '9E2079F10A97AF3F06257BC7006EBFD4' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. INSPECCIÓN Y CERTIFICACIÓN DE ESTABLECIMIENTOS AVÍCOLAS NTON 11 030 - 11',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=9E2079F10A97AF3F06257BC7006EBFD4&action=openDocument',
            ],
            'E5F72BC32154FB1D06257BDB005A8EE7' => [
                'title' => 'NTON 18 001 – 12. SEGUNDA REVISIÓN. NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. MANEJO SOSTENIBLE DE LOS BOSQUES NATURALES LATIFOLIADOS Y DE CONÍFERAS',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=E5F72BC32154FB1D06257BDB005A8EE7&action=openDocument',
            ],
            'B4C4B19EBE41926806257A1A0061528E' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE PARA EL MANEJO AMBIENTAL DE ACEITES LUBRICANTES USADOS NORMA TÉCNICA NTON 05 032-10',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=B4C4B19EBE41926806257A1A0061528E&action=openDocument',
            ],
            '53C3D460F2EA84EB06257A2A006E42B3' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE. BIENESTAR DE LOS BOVINOS NTON 11 027-11',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=53C3D460F2EA84EB06257A2A006E42B3&action=openDocument',
            ],
            '2C69A0692D0D7DDC06257A2B0057F6BF' => [
                'title' => 'REGLAMENTO TÉCNICO CENTROAMERICANO. BUENAS PRÁCTICAS DE HIGIENE PARA ALIMENTOS NO PROCESADOS Y SEMIPROCESADOS NTON 03093-10/RTCA 67.06.55:09',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=2C69A0692D0D7DDC06257A2B0057F6BF&action=openDocument',
            ],
            '935A0808507AFED8062578FC00741AD3' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE INSTALACIONES DE PROTECCIÓN CONTRA INCENDIOS NORMA TÉCNICA N°. NTON 22 002-09',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=935A0808507AFED8062578FC00741AD3&action=openDocument',
            ],
            '892A33578F2D13E50625798900734A72' => [
                'title' => 'MEDIDAS DE PROTECCIÓN CONTRA INCENDIO NTON 22 003-10',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=892A33578F2D13E50625798900734A72&action=openDocument',
            ],
            '3B3583B8C7D4EE32062579BC007B7023' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE PARA REGULAR LOS SISTEMAS DE TRATAMIENTOS DE AGUAS RESIDUALES Y SU REUSO NORMA TÉCNICA N° NTON 05 027-05',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=3B3583B8C7D4EE32062579BC007B7023&action=openDocument',
            ],
            '77F14583A2819D04062574D4005D136D' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE DE PROTECCIÓN CONTRA INCENDIOS, REQUISITOS GENERALES NTON 22 001-04',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=77F14583A2819D04062574D4005D136D&action=openDocument',
            ],
            '19AE4F2290672A5506257284006B36D7' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE DE ACCESIBILIDAD NTON 12 006-04 NORMA TÉCNICA No. NTON 12006-04',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=19AE4F2290672A5506257284006B36D7&action=openDocument',
            ],
            '6E259CA15A341BAA062579C7007331CE' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE DE PROCEDIMIENTOS Y REQUISITOS PARA LA PRESTACIÓN DE LOS SERVICIOS DE TRATAMIENTOS AGROPECUARIOS NTON 11 007-02',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=6E259CA15A341BAA062579C7007331CE&action=openDocument',
            ],
            '455FCF1FFCD5EC70062570A60064AD53' => [
                'title' => 'NORMA MINISTERIAL SOBRE CONDICIONES DE HIGIENE Y SEGURIDAD PARA EL FUNCIONAMIENTO DE LOS EQUIPOS GENERADORES DE VAPOR O CALDERAS QUE OPEREN EN LOS CENTROS DE TRABAJO',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=455FCF1FFCD5EC70062570A60064AD53&action=openDocument',
            ],
            '67A8A07340D3BC42062573020055FDCE' => [
                'title' => 'NORMAS TÉCNICAS PARA DISEÑOS DE SISTEMAS DE ABASTECIMIENTO DE AGUA POTABLE EN EL MEDIO RURAL Y SANEAMIENTO BÁSICO RURAL',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=67A8A07340D3BC42062573020055FDCE&action=openDocument',
            ],
            'C2B59D6916F2C9F9062573FD0050EF87' => [
                'title' => 'NORMA MINISTERIAL SOBRE LAS DISPOSICIONES BÁSICAS DE HIGIENE Y SEGURIDAD DEL TRABAJO APLICABLES A LOS EQUIPOS E INSTALACIONES ELÉCTRICAS (RIESGOS ELÉCTRICOS)',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=C2B59D6916F2C9F9062573FD0050EF87&action=openDocument',
            ],
            '6605448E4429FC050625771A0071FB9D' => [
                'title' => 'NORMA TÉCNICA SOBRE LAS DISPOSICIONES MÍNIMAS DE HIGIENE Y SEGURIDAD DE “LOS EQUIPOS DE PROTECCIÓN PERSONAL”',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=6605448E4429FC050625771A0071FB9D&action=openDocument',
            ],
            'ca6124f9e8b1390906257d3c0059d0df' => [
                'title' => 'REGLAMENTO DE LA LEY 837 "LEY DE LA DIRECCIÓN GENERAL DE BOMBEROS DE NICARAGUA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/ca6124f9e8b1390906257d3c0059d0df?OpenDocument',
            ],
            '27E1E4FE89ACD1FC062571150051BF42' => [
                'title' => 'Norma Ministerial sobre las Disposiciones Básicas de Higiene y Seguridad de los Lugares de Trabajo, MITRAB',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/27E1E4FE89ACD1FC062571150051BF42?OpenDocument',
            ],
            'b4b204a4f87a35da06257137005ce92d' => [
                'title' => 'Norma Ministerial sobre Señalización de Higiéne y Seguridad del Trabajo, MITRAB',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/164aa15ba012e567062568a2005b564b/b4b204a4f87a35da06257137005ce92d?OpenDocument&Highlight=2,ruido',
            ],
            '8233cb4936a758a506257109005c9c87' => [
                'title' => 'Decreto 45-94 Reglamento de Permiso y Evaluación de Impacto Ambiental, MARENA',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/d0c69e2c91d9955906256a400077164a/8233cb4936a758a506257109005c9c87?OpenDocument',
            ],
            '8cb01fccb5743252062577dc005be01b' => [
                'title' => 'NTON 14 018-06/ RTCA 23.01.24:06 "Recipiente a Presión. Cilindros Portátiles para Contener Gas Licuado de Petróleo. Vehículo Terrestre de Reparto. Especificaciones de Seguridad."',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/8cb01fccb5743252062577dc005be01b?OpenDocument',
            ],
            '4695f50dc80af6a306258631005864ed' => [
                'title' => 'BUENAS PRÁCTICAS AGRÍCOLAS. REQUISITOS Y EVALUACIÓN DE LA CONFORMIDAD NORMA TÉCNICA N°. NTON 11-051-19',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/4695f50dc80af6a306258631005864ed?OpenDocument',
            ],
            '167CA77238F187D906257569006FB738' => [
                'title' => 'Norma Ministerial de Higiene y Seguridad Relativa a Procedimientos Para La Obtención de la Licencia de Operación de los Equipos Generadores de Vapor',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/167CA77238F187D906257569006FB738?OpenDocument',
            ],
            'F5B0B51AF66BEA94062570A100581FB4' => [
                'title' => 'Resolución Ministerial Relativo a los Reglamentos Técnicos Organizativos de Higiene y Seguridad del Trabajo en lasEmpresasPublicada en La Gaceta, Diario Oficial No. 175 del 17 de Septiembre del 2001',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/F5B0B51AF66BEA94062570A100581FB4?OpenDocument',
            ],
            '12340B3922E2AC00062570A100581FAE' => [
                'title' => 'Resolución Ministerial sobre Higiene y Seguridad aplicable en el Uso, Manipulación y Aplicación de los Plaguicidas y otras Sustancias Agroquímicas en los Centros de Trabajo - Publicada en La Gaceta, Diario Oficial No. 175 del 17 de Septiembre del 2001',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/12340B3922E2AC00062570A100581FAE?OpenDocument',
            ],
            'e6a0f1ef95aaeb31062570a100581fa2' => [
                'title' => 'Resolución Ministerial sobre Higiene Industrial en los Lugares de Trabajo Publicada en La Gaceta, Diario Oficial No. 173 del 12 de Septiembre del 2001',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/164aa15ba012e567062568a2005b564b/e6a0f1ef95aaeb31062570a100581fa2?OpenDocument&Highlight=2,ruido',
            ],
            '016C572067D333970625722C00650758' => [
                'title' => 'Procedimiento para Normar la Capacitación en el ámbito de la Higiene y Seguridad del Trabajo',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/016C572067D333970625722C00650758?OpenDocument',
            ],
            // Already exists in the script with a different title and link
            // '6605448E4429FC050625771A0071FB9D' => [
            //     'title' => 'Norma Ministerial sobre las Disposiciones Mínimas de Higiene y Seguridad de los “Equipos de Protección Personal”. Publicada en La Gaceta, Diario Oficial No. 21 del 30 de Enero de 1997',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/6605448E4429FC050625771A0071FB9D?OpenDocument',
            // ],
            // Duplicate
            // 'F5B0B51AF66BEA94062570A100581FB4' => [
            //     'title' => 'Resolución Ministerial Relativo a los Reglamentos Técnicos Organizativos de Higiene y Seguridad del Trabajo en las Empresas Publicada en La Gaceta, Diario Oficial No. 175 del 17 de Septiembre del 2001',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/F5B0B51AF66BEA94062570A100581FB4?OpenDocument',
            // ],
            '572956DB814EE670062570A100581E87' => [
                'title' => 'Ratificación y Vigencia de todas las Resoluciones Ministeriales en Materia de Higiene y Seguridad del Trabajo Publicada en La Gaceta, Diario Oficial No. 147 del 6 de Agosto de 2001',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/572956DB814EE670062570A100581E87?OpenDocument',
            ],
            '679C2ADEDE0EA17506257A0700576DC4' => [
                'title' => 'Resolución Ministerial sobre Las Comisiones Mixtas de Higiene y Seguridad del Trabajo en las Empresas, Reformada, aprobada y publicada en La Gaceta, Diario Oficial N0. 29 del día 9 de Febrero del 2007',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/679C2ADEDE0EA17506257A0700576DC4?OpenDocument',
            ],
            '071181C1ACA31B16062570A10058502F' => [
                'title' => 'Resolución Ministerial sobre Las Comisiones Mixtas de Higiene y Seguridad del Trabajo en las Empresas, Reformada, aprobada y publicada en La Gaceta, Diario Oficial N0. 29 del día 9 de Febrero del 2007',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/0/071181C1ACA31B16062570A10058502F?OpenDocument',
            ],
            'B7FA28898A4737C106258AA70066EEF2' => [
                'title' => 'MEDIDAS FITOSANITARIAS. ENVÍO. IMPORTACIÓN NORMA TÉCNICA S/N, aprobada el 07 de octubre de 2022',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=B7FA28898A4737C106258AA70066EEF2&action=openDocument',
            ],
            // 'a35cf61591ad2d57062581f30056f9ec' => [
            //     'title' => 'REGLAMENTO EN EL QUE SE ESTABLECE N LAS DISPOSICIONES PARA EL VERTIDO DE AGUAS RESIDUALES DECRETO EJECUTIVO N°. 21-2017, aprobado el 28 de noviembre de 2017',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/a35cf61591ad2d57062581f30056f9ec?OpenDocument',
            // ],
            // '907d4e65c363cc8d062583520054fe79' => [
            //     'title' => 'SISTEMA DE EVALUACIÓN AMBIENTAL DE PERMISOS Y AUTORIZACIONES PARA EL USO SOSTENIBLE DE LOS RECURSOS NATURALES, DECRETO EJECUTIVO N°. 20-2017, aprobado el 28 de noviembre de 2017',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/907d4e65c363cc8d062583520054fe79?OpenDocument',
            // ],
            'CE563154073C585E062589500072193B' => [
                'title' => 'TEXTO CONSOLIDADO, DE ESTABLECIMIENTO DE LAS DISPOSICIONES QUE REGULAN LAS DESCARGAS DE AGUAS RESIDUALES DOMÉSTICAS PROVENIENTES DE LOS SISTEMAS DE TRATAMIENTO EN EL LAGO XOLOTLÁN DECRETO EJECUTIVO Nº. 77-2003, aprobado el 09 de diciembre del 2020',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/CE563154073C585E062589500072193B?OpenDocument',
            ],
            'd5863f9e2cc207ac062587e60058a571' => [
                'title' => 'TEXTO CONSOLIDADO, LEY GENERAL DE SALUD LEY Nº. 423, aprobada el 10 de diciembre de 2020',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/d5863f9e2cc207ac062587e60058a571?OpenDocument',
            ],
            '10cf7b394a7719c006258ac10057df30' => [
                'title' => 'TEXTO CONSOLIDADO, LEY GENERAL DEL MEDIO AMBIENTE Y LOS RECURSOS NATURALES LEY N°. 217, aprobado el 27 de septiembre de 2023',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/09cf45d6fc893868062572650059911e/10cf7b394a7719c006258ac10057df30?OpenDocument',
            ],
            '8ee23eda73fc9df80625775200543eac' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE DE REQUISITOS PARA EL TRANSPORTE DE PRODUCTOS ALIMENTICIOS NORMA TÉCNICA N°. NTON 03 079-08. aprobada el 31 de enero del 2008',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b92aaea87dac762406257265005d21f7/8ee23eda73fc9df80625775200543eac?OpenDocument',
            ],
            '19104cde704036de06257331005328c5' => [
                'title' => 'NORMA TÉCNICA NICARAGÜENSE NORMA SANITARIA DE MANIPULACIÓN DE ALIMENTOS REQUISITOS SANITARIOS PARA MANIPULADORES NORMA TÉCNICA Nº 03 026-99; Aprobada el 5 de Noviembre de 1999.',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/3133c0d121ea3897062568a1005e0f89/19104cde704036de06257331005328c5?OpenDocument',
            ],
            'E15BC36B7364BFA1062570A1005800F5' => [
                'title' => 'NORMA TÉCNICA OBLIGATORIA NICARAGÜENSE PARA EL CONTROL DE PLAGUICIDAS DE USO DOMÉSTICO Y EN SALUD PÚBLICA NTON 02 001-98, Aprobada el 19 de Noviembre de 1998',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/($All)/E15BC36B7364BFA1062570A1005800F5?OpenDocument',
            ],
            'aeb61aeab47adfb20625711d00554830' => [
                'title' => 'LEY QUE PROHÍBE EL TRÁFICO DE DESECHOS PELIGROSOS Y SUSTANCIAS TÓXICAS LEY No. 168, aprobada el 1 de diciembre de 1993',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/b92aaea87dac762406257265005d21f7/aeb61aeab47adfb20625711d00554830?OpenDocument',
            ],
            '93e77f83f0402d1c062570a1005777d4' => [
                'title' => 'LEY BÁSICA PARA LA REGULACIÓN Y CONTROL DE PLAGUICIDAS, SUSTANCIAS TÓXICAS, PELIGROSAS Y OTRAS SIMILARES LEY N°. 274, aprobada el 5 de noviembre de 1997',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/9e314815a08d4a6206257265005d21f9/93e77f83f0402d1c062570a1005777d4?OpenDocument',
            ],
            'c505ff5bfbd6c478062574f2007946dc' => [
                'title' => 'REGLAMENTO SANITARIO DE LOS RESIDUOS SÓLIDOS, PELIGROSOS Y NO PELIGROSOS RESOLUCIÓN MINISTERIAL No. 122-2008. Aprobada el 27 de Mayo de 2008',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/0/c505ff5bfbd6c478062574f2007946dc?OpenDocument',
            ],
            'ba58507a747a5a94062572370068596f' => [
                'title' => 'REGLAMENTO DE LA LEY No. 462, LEY DE CONSERVACIÓN, FOMENTO Y DESARROLLO SOSTENIBLE DEL SECTOR FORESTAL DECRETO EJECUTIVO N°. 73-2003, aprobado el 3 de noviembre del 2003',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/d0c69e2c91d9955906256a400077164a/ba58507a747a5a94062572370068596f?OpenDocument',
            ],
            '376155b1768a24b70625723300578eda' => [
                'title' => 'LEY DE CONSERVACIÓN, FOMENTO Y DESARROLLO SOSTENIBLE DEL SECTOR FORESTAL LEY N°. 462, aprobada el 26 de junio del 2003',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/9e314815a08d4a6206257265005d21f9/376155b1768a24b70625723300578eda?OpenDocument',
            ],
            '7af3d5a2687b48940625755b0076a8dd' => [
                'title' => 'LEY DE VEDA PARA EL CORTE, APROVECHAMIENTO Y COMERCIALIZACIÓN DEL RECURSO FORESTAL LEY N°. 585, aprobada el 07 de junio de 2006',
                'start_url' => 'http://legislacion.asamblea.gob.ni/normaweb.nsf/b34f77cd9d23625e06257265005d21fa/7af3d5a2687b48940625755b0076a8dd?OpenDocument',
            ],
            'C07805332BE300D8062587AA006F6F09' => [
                'title' => 'NORMA TÉCNICA N°. NTON 11002, MEDIDAS SANITARIAS. PREVENCIÓN, CONTROL Y ERRADICACIÓN. ENFERMEDAD DE INFLUENZA AVIAR',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=C07805332BE300D8062587AA006F6F09&action=openDocument',
            ],
            'FA9E36ED8CC4184D06258A1A006CD189' => [
                'title' => 'NORMA TECNICA NTON 05001, GESTIÓN AMBIENTAL. ESTABLECIMIENTOS DE PROCESO Y RASTROS, CRITERIO TÉCNICOS',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=FA9E36ED8CC4184D06258A1A006CD189&action=openDocument',
            ],
            'B7183C9373CC2BF206258AC80056E4C1' => [
                'title' => 'LEY N°. 1192, LEY PARA LA CERTIFICACIÓN DE PERMISOS Y AUTORIZACIONES AMBIENTALES',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/($All)/B7183C9373CC2BF206258AC80056E4C1?OpenDocument',
                'download_url' => 'http://digesto.asamblea.gob.ni/consultas/util/pdf.php?type=rdd&download=false&rdd=%2BCEEV5wnr6A%3D&#page=2',
            ],
            '3BC1F8B64781C4C506258ADE00708988' => [
                'title' => 'NORMA TÉCNICA NTON 04001, EFICIENCIA ENERGÉTICA. ACONDICIONADORES DE AIRE TIPO DIVIDIDO INVERTER, CON FLUJO DE REFRIGERANTE VARIABLE DESCARGA LIBRE Y SIN DUCTOS DE AIRE. PROCEDIMIENTO PARA LA DEMOSTRACIÓN DE LA CONFORMIDAD',
                'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=3BC1F8B64781C4C506258ADE00708988&action=openDocument',
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        foreach ($this->getDocs() as $id => $doc) {
            $meta = [];
            $id = $id;
            $title = $doc['title'];
            $startUrl = $doc['start_url'];
            $meta['download_url'] = $doc['download_url'] ?? '';
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $startUrl,
                'view_url' => $startUrl,
                'source_unique_id' => (string) $id,
                'language_code' => 'spa',
                'primary_location_id' => '163810',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        $dom = $pageCrawl->domCrawler;
        $url = $pageCrawl->getFinalRedirectedUrl();
        $id = '';
        if (preg_match('/idnorm.*/', $url, $matches)) {
            $id = Str::of($url)->after('idnorm=')->before('=');
        } elseif (preg_match('/\$FILE\/([^\/]+)\.pdf$/', $url, $matches)) {
            $id = Str::of($url)->before('/$FILE')->afterLast('/');
        } elseif (preg_match('/.*?documentId=.*/', $url)) {
            $id = Str::of($url)->after('?documentId=')->before('&action=openDocument');
        } else {
            $id = Str::of($url)->afterLast('/')->before('?OpenDocument');
        }

        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '163810');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->docMeta?->doc_meta['download_url'] ?? null);
        $date = $dom->filter('div[align="center"] > font[face="Arial"]')->getNode(0)?->textContent ?? '';
        $date = str_replace('aprobado el ', '', $date);
        if ($date) {
            try {
                /** @var Carbon */
                $formattedDate = Carbon::parse($date);
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $formattedDate);
            } catch (InvalidFormatException $th) {
            }
        }

        $doc->docMeta->save();

        return $doc;
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
                'query' => 'span.xspTextComputedField',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href*=".pdf"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.justifyText',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.wrapper div.container div.row',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            // [
            //     'type' => DomQueryType::CSS,
            //     'query' => 'div[align="center"] b',
            // ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'head > title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"domjava/xsp/theme/webstandard/xspLTR.css") and @rel="stylesheet"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"../include/css/style.css")]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href, "../include/css/jquery-ui.css") and @rel="stylesheet"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href, "../include/css/jquery-ui.theme.css") and @rel="stylesheet"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href, "../bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") and @rel="stylesheet"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href, "../bootstrap-3.3.4/css/bootstrap.css") and @rel="stylesheet"]',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href*="documentId"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'img[src*=".gif"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'img[src*="/Normaweb.nsf/0/ce563154073c585e062589500072193b/TextoNorma/0.210"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'img[src*="/content"]',
            ],
            // [
            //     'type' => DomQueryType::CSS,
            //     'query' => 'img[src*="normaweb"]',
            // ],
        ]);
        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('a[href*="http://legislacion.asamblea.gob.ni"]') as $anchor) {
                /** @var DOMElement $anchor */
                if (!str_contains($anchor->getAttribute('href'), '.pdf')) {
                    $anchor->removeAttribute('href');
                }
            }
            if ($crawler->filter('a[href*="pdf"]')->count() > 0) {
                foreach ($crawler->filter('a[href*="pdf"]') as $pdfLink) {
                    /** @var DOMElement $pdfLink */
                    $fullUrl = str_contains($pdfLink->getAttribute('href'), 'http://legislacion.asamblea.gob.ni')
                        ? $pdfLink->getAttribute('href')
                        : "http://legislacion.asamblea.gob.ni{$pdfLink->getAttribute('href')}";

                    $pLink = Http::get($fullUrl);
                    $contentResourceStore = app(ContentResourceStore::class);

                    $resource = $contentResourceStore->storeResource($pLink, 'application/pdf');
                    $newHref = $contentResourceStore->getLinkForResource($resource);
                    $pdfLink->setAttribute('href', $newHref);
                }
            }

            return $crawler;
        };
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getNicaraguaStartUrls(): array
    {
        return ['type_' . CrawlType::FULL_CATALOGUE->value => [
            new UrlFrontierLink(['url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpMainDIL.xsp']),
        ]];
    }
}
