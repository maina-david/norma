<?php

namespace App\Services\Arachno\Crawlers\Sources\Sp;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
            'slug' => 'sp-nicaragua-asamblea-nacional',
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
        if ($this->arachno->matchCSS($pageCrawl, 'span.xspTextComputedField') || $this->arachno->matchCSS($pageCrawl, 'div.justifyText')) {
            $this->handleMeta($pageCrawl);
            $this->capturePageContent($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '\$FILE\/.*\.pdf$')) {
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
            // 'e6b32d91d9b2f6e8062578a30071fddd' => [
            //     'title' => 'Ley Especial de Gestión Integral de Residuos y Desechos Sólidos Peligrosos y no Peligrosos',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/SILEG/Iniciativas.nsf/0/e6b32d91d9b2f6e8062578a30071fddd/$FILE/Articulado%20en%20Dictamen%20Ley%20General%20de%20Gestion%20Res%20Solidos.pdf',
            //     // 'file_type' => 'PDF',
            // ],
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
            //      Different Source
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
            $id = $id;
            $title = $doc['title'];
            $startUrl = $doc['start_url'];
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $startUrl,
                'view_url' => $startUrl,
                'source_unique_id' => (string) $id,
                'language_code' => 'spa',
                'primary_location_id' => '163810',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
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
        $id = (string) Str::of($url)
            ->afterLast('/')
            ->before('?OpenDocument');
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '163810');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);
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
                'query' => 'span.xspTextComputedField, div.justifyText',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.justifyText',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.justifyText',
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
                'query' => 'img[src*="/content"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'img[src*="normaweb"]',
            ],
        ]);
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries);
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
