<?php

namespace App\Services\Arachno\Crawlers\Sources\Br;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: br-sao-paulo-city
 * title: Sao Paulo City
 * url: https://legislacao.prefeitura.sp.gov.br/
 */
class SaoPauloCity extends AbstractCrawlerConfig
{
    protected string $baseDomain = 'https://legislacao.prefeitura.sp.gov.br';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'br-sao-paulo-city',
            'throttle_requests' => 300,
            'verify_ssl' => false,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://legislacao.prefeitura.sp.gov.br/',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/legislacao\.prefeitura\.sp\.gov\.br/')) {
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDocs(): array
    {
        return [
            'leis/lei-17471-de-30-de-setembro-de-2020' => [
                'title' => 'LEI Nº 17.471 DE 30 DE SETEMBRO DE 2020 - Estabelece a obrigatoriedade da implantação de logística reversa no Município de São Paulo para recolhimento dos produtos que especifica e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-17471-de-30-de-setembro-de-2020',
            ],
            'leis/lei-17261-de-13-de-janeiro-de-2020' => [
                'title' => 'LEI Nº 17.261 DE 13 DE JANEIRO DE 2020 - Dispõe sobre a proibição de fornecimento de produtos de plástico de uso único nos locais que especifica.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-17261-de-13-de-janeiro-de-2020',
            ],
            'leis/lei-17123-de-25-de-junho-de-2019' => [
                'title' => 'LEI Nº 17.123 DE 25 DE JUNHO DE 2019 - Dispõe sobre a proibição de fornecimento de canudos confeccionados em material plástico, nos locais que especifica, e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-17123-de-25-de-junho-de-2019',
            ],
            'leis/lei-16131-de-12-de-marco-de-2015' => [
                'title' => 'LEI Nº 16.131 DE 12 DE MARÇO DE 2015. -Dispõe sobre as normas aplicáveis aos motores de acionamento de grupos geradores estacionários, revoga o item 9.4.5 do Anexo I da Lei n° 11.228, de 25 de junho de 1992, na redação dada pela Lei n° 15.095, de 4 de janeiro de 2010, e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-16131-de-12-de-marco-de-2015',
            ],
            'leis/lei-14698-de-12-de-fevereiro-de-2008' => [
                'title' => 'LEI Nº 14.698 DE 12 DE FEVEREIRO DE 2008 - Dispõe sobre a proibição de destinar óleo comestível servido no meio ambiente.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-14698-de-12-de-fevereiro-de-2008',
            ],
            'leis/lei-13111-de-14-de-marco-de-2001' => [
                'title' => 'LEI Nº 13.111 DE 14 DE MARÇO DE 2001 - Dispõe sobre a obrigatoriedade do recolhimento de pilhas, baterias e congêneres, quando descarregadas.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-13111-de-14-de-marco-de-2001',
            ],
            'leis/lei-11368-de-17-de-maio-de-1993' => [
                'title' => 'LEI Nº 11.368 DE 17 DE MAIO DE 1993 - Dispõe sobre o transporte de produtos perigosos de qualquer natureza por veículos de carga no Município de São Paulo, e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-11368-de-17-de-maio-de-1993',
            ],
            'leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svma-4-de-3-de-fevereiro-de-2021' => [
                'title' => 'PORTARIA SECRETARIA MUNICIPAL DO VERDE E DO MEIO AMBIENTE - SVMA Nº 4 DE 3 DE FEVEREIRO DE 2021 - Determina procedimento de avaliação da CONSULTA PRÉVIA quanto à exigibilidade do licenciamento ambiental de empreendimentos e atividades não industriais; e dá outras providencias.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svma-4-de-3-de-fevereiro-de-2021',
            ],
            'leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svma-decont-5-de-10-de-setembro-de-2018' => [
                'title' => 'PORTARIA SECRETARIA MUNICIPAL DO VERDE E DO MEIO AMBIENTE – SVMA/DECONT Nº 5 DE 10 DE SETEMBRO DE 2018 Define os conceitos e procedimentos para o Licenciamento Ambiental de Atividades Industriais no âmbito do Município de São Paulo e estabelece a documentação necessária para autuação do respectivo processo administrativo.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svma-decont-5-de-10-de-setembro-de-2018',
            ],
            'leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svmadecont-3-de-06-de-outubro-de-2017' => [
                'title' => 'PORTARIA SECRETARIA MUNICIPAL DO VERDE E DO MEIO AMBIENTE – SVMA/DECONT Nº 3 DE 6 DE OUTUBRO DE 2017 - Estabelece diretrizes e regulamenta os procedimentos para o Licenciamento Ambiental Eletrônico de Atividades Industriais no âmbito do Município de São Paulo.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svmadecont-3-de-06-de-outubro-de-2017',
            ],
            'leis/decreto-57565-de-27-de-dezembro-de-2016' => [
                'title' => 'DECRETO Nº 57.565 DE 27 DE DEZEMBRO DE 2016 - Regulamenta procedimentos para a aplicação da Quota Ambiental, nos termos da Lei nº 16.402, de 22 de março de 2016.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/decreto-57565-de-27-de-dezembro-de-2016',
            ],
            'leis/decreto-53889-de-08-de-maio-de-2013' => [
                'title' => 'DECRETO Nº 53.889 DE 8 DE MAIO DE 2013 - Regulamenta o Termo de Compromisso Ambiental - TCA, instituído pelo artigo 251 e seguintes da Lei nº 13.430, de 13 de setembro de 2002 (Plano Diretor Estratégico).',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/decreto-53889-de-08-de-maio-de-2013',
            ],
            'leis/lei-15499-de-07-de-dezembro-de-2011' => [
                'title' => 'ILEI Nº 15.499 DE 7 DE DEZEMBRO DE 2011 - nstitui o Auto de Licença de Funcionamento Condicionado, e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-15499-de-07-de-dezembro-de-2011',
            ],
            'leis/decreto-49969-de-28-de-agosto-de-2008' => [
                'title' => 'DECRETO Nº 49.969 DE 28 DE AGOSTO DE 2008 - Regulamenta a expedição de Auto de Licença de Funcionamento, Alvará de Funcionamento, Alvará de Autorização para eventos públicos e temporários e Termo de Consulta de Funcionamento, em consonância com as Leis nº 10.205, de 4 de dezembro de 1986, e nº 13.885, de 25 de agosto de 2004; revoga os decretos e a portaria que especifica.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/decreto-49969-de-28-de-agosto-de-2008',
            ],
            'leis/decreto-41534-de-20-de-dezembro-de-2001' => [
                'title' => 'DECRETO Nº 41.534 DE 20 DE DEZEMBRO DE 2001 - Dispõe sobre a fiscalização em geral, estabelece os procedimentos de fiscalização da instalação e do funcionamento de atividades em imóveis, e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/decreto-41534-de-20-de-dezembro-de-2001',
            ],
            'leis/portaria-secretaria-municipal-de-licenciamento-sel-191-de-30-de-dezembro-de-2019' => [
                'title' => 'LEI Nº 17.202 DE 16 DE OUTUBRO DE 2019 - Dispõe sobre a regularização de edificações, condicionada, quando necessário, à realização de obras, nos termos da previsão do art. 367 do Plano Diretor Estratégico.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-de-licenciamento-sel-191-de-30-de-dezembro-de-2019',
            ],
            // 'leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svma-decont-5-de-10-de-setembro-de-2018' => [
            //     'title' => 'PORTARIA SECRETARIA MUNICIPAL DO VERDE E DO MEIO AMBIENTE – SVMA/DECONT Nº 5 DE 10 DE SETEMBRO DE 2018 - Define os conceitos e procedimentos para o Licenciamento Ambiental de Atividades Industriais no âmbito do Município de São Paulo e estabelece a documentação necessária para autuação do respectivo processo administrativo.',
            //     'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-do-verde-e-do-meio-ambiente-svma-decont-5-de-10-de-setembro-de-2018',
            // ],
            'leis/portaria-secretaria-municipal-de-urbanismo-e-licenciamento-smul-221-de-20-de-julho-de-2017' => [
                'title' => 'PORTARIA SECRETARIA MUNICIPAL DE URBANISMO E LICENCIAMENTO - SMUL Nº 221 DE 20 DE JULHO DE 2017 - Estabelece a documentação necessária e os padrões de apresentação dos projetos para a instrução dos pedidos relacionados à atividade edilícia.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-de-urbanismo-e-licenciamento-smul-221-de-20-de-julho-de-2017',
            ],
            'leis/lei-15855-de-16-de-setembro-de-2013' => [
                'title' => 'LEI Nº 15.855 DE 16 DE SETEMBRO DE 2013 - Dispõe sobre a obtenção de Auto de Licença de Funcionamento, bem como altera a Lei nº 15.499, de 7 de dezembro de 2011, que instituiu o Auto de Licença de Funcionamento Condicionado.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-15855-de-16-de-setembro-de-2013',
            ],
            // 'leis/decreto-49969-de-28-de-agosto-de-2008' => [
            //     'title' => 'DECRETO Nº 49.969 DE 28 DE AGOSTO DE 2008 - Regulamenta a expedição de Auto de Licença de Funcionamento, Alvará de Funcionamento, Alvará de Autorização para eventos públicos e temporários e Termo de Consulta de Funcionamento, em consonância com as Leis nº 10.205, de 4 de dezembro de 1986, e nº 13.885, de 25 de agosto de 2004; revoga os decretos e a portaria que especifica.',
            //     'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/decreto-49969-de-28-de-agosto-de-2008',
            // ],
            // 'leis/decreto-41534-de-20-de-dezembro-de-2001' => [
            //     'title' => 'DECRETO Nº 41.534 DE 20 DE DEZEMBRO DE 2001 - Dispõe sobre a fiscalização em geral, estabelece os procedimentos de fiscalização da instalação e do funcionamento de atividades em imóveis, e dá outras providências.',
            //     'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/decreto-41534-de-20-de-dezembro-de-2001',
            // ],
            'leis/lei-10205-de-04-de-dezembro-de-1986' => [
                'title' => 'LEI Nº 10.205 DE 4 DE DEZEMBRO DE 1986 - Disciplina a expedição de licença de funcionamento, e dá outras providências.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-10205-de-04-de-dezembro-de-1986',
            ],
            'leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-smped-cpa-28-de-2-de-abril-de-2020' => [
                'title' => 'RESOLUÇÃO SECRETARIA MUNICIPAL DA PESSOA COM DEFICIÊNCIA - SMPED/CPA Nº 28 DE 1 DE ABRIL DE 2020 - Trata de considerar como sendo sinalização tátil e visual no piso, relevos de plástico a frio à base de resina reativa de metilmetacrilato, resina esta com critérios e parâmetros definidos na Norma Brasileira ABNT NBR 15.870.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-smped-cpa-28-de-2-de-abril-de-2020',
            ],
            'leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-smped-cpa-22-de-25-de-abril-de-2018' => [
                'title' => 'RESOLUÇÃO SECRETARIA MUNICIPAL DA PESSOA COM DEFICIÊNCIA - SMPED/CPA Nº 22 DE 27 DE FEVEREIRO DE 2018 - Dispõe de parâmetros, dimensionamento e demais especificações constantes na ABNT NBR 9050, relativos aos “Assentos para Pessoas Obesas”.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-smped-cpa-22-de-25-de-abril-de-2018',
            ],
            'leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-smped-cpa-18-de-13-de-fevereiro-de-2014' => [
                'title' => 'RESOLUÇÃO SECRETARIA MUNICIPAL DA PESSOA COM DEFICIÊNCIA - SMPED/CPA Nº 18 DE 5 DE FEVEREIRO DE 2014 - Aprova a Quantificação e Características das Vagas de Veículos Reservadas à Pessoa com Deficiência em Estacionamentos de Edificações.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-smped-cpa-18-de-13-de-fevereiro-de-2014',
            ],
            'leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-cpa-16-de-15-de-agosto-de-2012' => [
                'title' => 'RESOLUÇÃO SECRETARIA MUNICIPAL DA PESSOA COM DEFICIÊNCIA - SMPED/CPA Nº 16 DE 18 DE JULHO DE 2012 - Estabelece duas especificações para vasos sanitários em banheiros de uso coletivo, para garantir mais funcionalidade e segurança às pessoas com deficiência na utilização de sanitários acessíveis.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-cpa-16-de-15-de-agosto-de-2012',
            ],
            'leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-cpa-15-de-20-de-novembro-de-2008' => [
                'title' => 'RESOLUÇÃO SECRETARIA MUNICIPAL DA PESSOA COM DEFICIÊNCIA - SMPED/CPA Nº 15 DE 14 DE NOVEMBRO DE 2008 - Aprova o documento “NORMA TÉCNICA PARA PISOS TÁTEIS – Comissão Permanente de Acessibilidade - CPA, novembro de 2008” sobre sinalização tátil de piso com textura diferenciada e contraste de cor, dirigida às pessoas com deficiência visual através de piso tátil integrado, piso tátil sobreposto e piso tátil por fixação de elementos.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-municipal-da-pessoa-com-deficiencia-cpa-15-de-20-de-novembro-de-2008',
            ],
            'leis/resolucao-secretaria-da-habitacao-e-desenvolvimento-urbano-cpa-13-de-22-de-outubro-de-2003' => [
                'title' => 'RESOLUÇÃO SECRETARIA DA HABITAÇÃO E DESENVOLVIMENTO URBANO - SEHAB/CPA Nº 13 DE 18 DE SETEMBRO DE 2003. -Aprova manual técnico de execução e instalação de rampa pré-fabricada em microconcreto armado.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-da-habitacao-e-desenvolvimento-urbano-cpa-13-de-22-de-outubro-de-2003',
            ],
            'leis/resolucao-secretaria-da-habitacao-e-desenvolvimento-urbano-cpa-10-de-5-de-agosto-de-2003' => [
                'title' => 'RESOLUÇÃO SECRETARIA DA HABITAÇÃO E DESENVOLVIMENTO URBANO - SEHAB/CPA Nº 10 DE 1 DE JULHO DE 2003 - Dispõe sobre elevador de uso específico como dispositivo complementar de acessibilidade às edificações para pessoas portadoras de deficiência ou com mobilidade reduzida.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-da-habitacao-e-desenvolvimento-urbano-cpa-10-de-5-de-agosto-de-2003',
            ],
            'leis/resolucao-secretaria-da-habitacao-e-desenvolvimento-urbano-sehab-cpa-6-de-19-de-agosto-de-2002' => [
                'title' => 'RESOLUÇÃO SECRETARIA DA HABITAÇÃO E DESENVOLVIMENTO URBANO - SEHAB/CPA Nº 6 DE 11 DE JULHO DE 2002 - Dispõe sobre equipamentos de acessibilidade em edificações.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/resolucao-secretaria-da-habitacao-e-desenvolvimento-urbano-sehab-cpa-6-de-19-de-agosto-de-2002',
            ],
            'leis/lei-12492-de-10-de-outubro-de-1997' => [
                'title' => 'LEI Nº 12.492 DE 10 DE OUTUBRO DE 1997 - Assegura o ingresso de cães guia para deficientes visuais em locais de uso público ou privado.',
                'start_url' => 'https://legislacao.prefeitura.sp.gov.br/leis/lei-12492-de-10-de-outubro-de-1997',
            ],
            // 'leis/portaria-secretaria-municipal-de-urbanismo-e-licenciamento-smul-221-de-20-de-julho-de-2017' => [
            //     'title' => 'PORTARIA SECRETARIA MUNICIPAL DE URBANISMO E LICENCIAMENTO - SMUL Nº 221 DE 20 DE JULHO DE 2017 - Estabelece a documentação necessária e os padrões de apresentação dos projetos para a instrução dos pedidos relacionados à atividade edilícia.',
            //     'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/portaria-secretaria-municipal-de-urbanismo-e-licenciamento-smul-221-de-20-de-julho-de-2017',
            // ],
            'leis/lei-13558-de-14-de-abril-de-2003' => [
                'title' => 'LEI Nº 13.558 DE 14 DE ABRIL DE 2003 - Dispõe sobre a regularização de edificações e dá outras providências.',
                'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/lei-13558-de-14-de-abril-de-2003',
            ],
            'leis/lei-8382-de-13-de-abril-de-1976' => [
                'title' => 'LEI Nº 8.382 DE 13 DE ABRIL DE 1976 - Institui o cadastro de edificações do município e dá outras providências.',
                'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/lei-8382-de-13-de-abril-de-1976',
            ],
            'leis/lei-16050-de-31-de-julho-de-2014' => [
                'title' => 'LEI Nº 16.050 DE 31 DE JULHO DE 2014 - Aprova a Política de Desenvolvimento Urbano e o Plano Diretor Estratégico do Município de São Paulo e revoga a Lei nº 13.430/2002.',
                'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/lei-16050-de-31-de-julho-de-2014',
            ],
            'leis/lei-0-de-04-de-abril-de-1990' => [
                'title' => 'LEI Nº 0 DE 4 DE ABRIL DE 1990 - Lei Orgânica do Município de São Paulo',
                'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/lei-0-de-04-de-abril-de-1990',
            ],
            'leis/lei-16642-de-09-de-maio-de-2017' => [
                'title' => 'LEI Nº 16.642 DE 9 DE MAIO DE 2017 - Aprova o Código de Obras e Edificações do Município de São Paulo; introduz alterações nas Leis nº 15.150, de 6 de maio de 2010, e nº 15.764, de 27 de maio de 2013.',
                'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/lei-16642-de-09-de-maio-de-2017',
            ],
            'leis/lei-14933-de-05-de-junho-de-2009' => [
                'title' => 'LEI Nº 14.933 DE 5 DE JUNHO DE 2009 - Institui a Política de Mudança do Clima no Município de São Paulo.',
                'start_url' => 'http://legislacao.prefeitura.sp.gov.br/leis/lei-14933-de-05-de-junho-de-2009',
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
            $title = $doc['title'];
            $startUrl = $doc['start_url'];
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $startUrl,
                'view_url' => $startUrl,
                'source_unique_id' => $id,
                'language_code' => 'por',
                'primary_location_id' => '80029',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'por');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '80029');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => ' body > section > div > div.bx-leis > div.bx-retornaLei.remove > div > div:nth-child(6)',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"static/css") and @rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'p[style="text-align: center;"]',
            ],
        ]);
        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('a[href]') as $href) {
                /** @var DOMElement $href */
                $href->removeAttribute('href');
            }
            foreach ($crawler->filter('del') as $delElem) {
                /** @var DOMElement $delElem */
                $delElem->setAttribute('style', 'display: none;');
            }

            return $crawler;
        };

        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
            preStoreCallable: $preStore,
        );
    }
}
