<?php

namespace App\Actions\System\ApiLog;

use App\Models\System\ApiLog;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

class LogApiCall
{
    use AsAction;

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function handle(Request $request, Response $response): void
    {
        $headers = $request->headers->all();
        if (isset($headers['authorization'])) {
            $authHeader = (string) $headers['authorization'][0];
            $headers['authorization'] = ['redacted...' . substr($authHeader, strlen($authHeader) - 50, strlen($authHeader) - 1)];
        }
        ApiLog::create([
            'route' => $request->route()?->getName(),
            'method' => $request->getMethod(),
            'query' => $request->getUri(),
            'headers' => $headers,
            'body' => $request->getContent(),
            'user_id' => $request->user()?->id,
            'response_status' => $response->getStatusCode(),
        ]);
    }
}
