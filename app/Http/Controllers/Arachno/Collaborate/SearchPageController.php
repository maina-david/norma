<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * @codeCoverageIgnore
 * This is still a PoC, so not writing tests yet....
 */
class SearchPageController extends Controller
{
    /**
     * @param Request $request
     *
     * @return View
     */
    public function search(Request $request): View
    {
        $q = $request->input('q');
        $q = strip_tags($q);
        if (empty($q)) {
            return view('pages.arachno.collaborate.search-page.results', [
                'query' => '',
                'results' => [],
                'sourceId' => null,
            ]);
        }

        $endpoint = config('services.libryo_ai.host');

        /** @var bool $enabled */
        $enabled = config('services.libryo_ai.enabled');

        $filters = [];
        $sourceId = null;
        if ($sourceId = $request->input('source_id')) {
            $filters['source_id'] = [$sourceId];
        }
        try {
            $response = Http::baseUrl($endpoint)
                ->asJson()
                ->acceptJson()
                ->post('/libryo-ai/pages/search', [
                    'query' => $q,
                    'limit' => 400,
                    'filters' => $filters,
                    'weights' => [
                        'title_weight' => 1,
                        'text_weight' => 1,
                        'text_embedding_weight' => 100,
                    ],
                ]);

            $data = $response->json();
        } catch (Throwable $th) {
            $data = [];
        }

        $out = [];
        foreach (($data['results'] ?? []) as $result) {
            $arr = [];
            foreach ($result['documents'] as $index => $document) {
                $doc = $document['document'];
                if (!isset($doc['page_url'])) {
                    continue;
                }

                $arr['title'] = $doc['title'] ?? $doc['page_url'];
                $arr['page_url'] = $doc['page_url'];
                $arr['url_host'] = $doc['url_host'];
                $arr['documents'][] = $doc;
            }
            if (!empty($arr)) {
                $out[] = $arr;
            }
        }

        return view('pages.arachno.collaborate.search-page.results', [
            'query' => $data['query'],
            'results' => $out,
            'sourceId' => $sourceId,
        ]);
    }
}
