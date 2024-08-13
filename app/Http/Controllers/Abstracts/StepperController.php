<?php

namespace App\Http\Controllers\Abstracts;

use App\Http\Controllers\Controller;
use App\Services\TempFileManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

abstract class StepperController extends Controller
{
    /** @var bool */
    protected bool $grouped = false;

    /**
     * The field in the steps array to group by.
     *
     * @var string
     */
    protected string $groupByField = 'title';

    /**
     * Get the Form Requests classes (FormRequest::class), step title and partial for each stage.
     * The order that they are listed is the order that they will be rendered.
     * The description is optional. Follow the sample below.
     *
     * return [
     *      [
     *          'title' => __('collaborators.personal_information'),
     *          'description' => Optional,
     *          'request' => PersonalInformationRequest::class,
     *          'partial' => 'pages.collaborator-applications.partials.personal_information',
     *      ],
     * ];
     *
     * @return array<mixed>
     */
    abstract protected function getStages(): array;

    /**
     * Get the route name for the stepper resource.
     *
     * @return string
     */
    abstract protected function routeName(): string;

    /**
     * Handle the finish action. When successful, remember to clear the session data by
     * calling $flush();.
     *
     * @param Collection<string, mixed> $data
     * @param callable                  $flush
     *
     * @return RedirectResponse
     */
    abstract protected function onFinish(Collection $data, callable $flush): RedirectResponse;

    /**
     * Get the view that has the stepper implemented.
     *
     * @return View
     */
    abstract protected function getView(): View;

    /**
     * @param Request $request
     *
     * @return array<string,mixed>
     */
    protected function routeParams(Request $request): array
    {
        return [];
    }

    /**
     * Get the session key that stores the stage information.
     *
     * @param int|string $stage
     *
     * @return string
     */
    protected function getSessionKey(int|string $stage): string
    {
        return str_replace('\\', '', static::class) . $stage;
    }

    /**
     * Validate that the stage is accessible.
     *
     * @param int $stage
     *
     * @return int
     */
    protected function validateStage(int $stage): int
    {
        if ($stage > count($this->getStages()) || $stage === 1) {
            return 1;
        }

        return session()->has($this->getSessionKey($stage - 1))
            ? $stage
            : 1;
    }

    /**
     * Get the create route.
     *
     * @return string
     */
    private function createRoute(): string
    {
        return $this->routeName() . '.create';
    }

    /**
     * Get the store route.
     *
     * @return string
     */
    private function storeRoute(): string
    {
        return $this->routeName() . '.store';
    }

    /**
     * Get the redirect key used.
     *
     * @return string
     */
    protected function redirectKey(): string
    {
        return $this->getSessionKey('redirect');
    }

    /**
     * Get the redirect key used.
     *
     * @return string
     */
    protected function fixedRedirectKey(): string
    {
        return $this->getSessionKey('fixed-redirect');
    }

    /**
     * Get the fixed or standard redirect url.
     *
     * @return string|null
     */
    protected function redirectTo(): ?string
    {
        return Session::pull($this->fixedRedirectKey(), Session::pull($this->redirectKey()));
    }

    /**
     * Check if the request has a turbo frame header.
     *
     * @return bool
     */
    protected function fromTurbo(): bool
    {
        return (bool) request()->header('Turbo-Frame');
    }

    /**
     * Show creation form for the given stage.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse|\Illuminate\Http\Response
     */
    public function create(Request $request): View|RedirectResponse|Response
    {
        /** @var string $stage */
        $stage = $request->route('stage', '1');
        $stage = (int) $stage;

        $this->setPreviousURL($stage);

        $routeParams = $this->routeParams($request);

        if ($stage !== $this->validateStage($stage)) {
            return redirect()->route($this->createRoute(), [...$routeParams, 'stage' => 1]);
        }

        session()->flash('_old_input', session($this->getSessionKey($stage), session('_old_input')));

        $view = $this->getView()->with([
            'routeParams' => $routeParams,
            'active' => $stage,
            'isLast' => $stage === count($this->getStages()),
            'order' => $this->getStages(),
            'route' => $this->storeRoute(),
        ]);

        if ($this->fromTurbo()) {
            // @codeCoverageIgnoreStart
            /** @var View $view */
            $view = view('streams.single-rendered', [
                'partial' => $view,
                'target' => $request->header('Turbo-Frame'),
            ]);

            return turboStreamResponse($view);
        }
        // @codeCoverageIgnoreEnd

        return $view;
    }

    /**
     * Set the previous URL.
     *
     * @param int $stage
     *
     * @return void
     */
    protected function setPreviousURL(int $stage): void
    {
        $redirectKey = $this->redirectKey();

        $current = explode('create', URL::current())[0];
        $previous = explode('create', URL::previous())[0];
        $fixed = explode('create', app_redirect_back()->getTargetUrl())[0];

        if ($stage == 1 && $fixed !== $previous) {
            // @codeCoverageIgnoreStart
            Session::put($this->fixedRedirectKey(), app_redirect_back()->getTargetUrl());
            // @codeCoverageIgnoreEnd
        }

        if ($stage == 1 && $current !== $previous) {
            Session::put($redirectKey, URL::previous());
        }
    }

    /**
     * Validate and store the personal information.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var string $stage */
        $stage = $request->route('stage', '1');
        $stage = (int) $stage;
        $routeParams = $this->routeParams($request);

        $this->setPreviousURL($stage);

        if ($stage !== $this->validateStage($stage)) {
            return redirect()->route($this->createRoute(), [...$routeParams, 'stage' => 1]);
        }

        $stages = $this->getStages();

        $request = app($stages[$stage - 1]['request']); // index begins at 0

        session()->put(
            $this->getSessionKey($stage),
            $this->prepareForSerialisation($request->validated())
        );

        $last = count($this->getStages());

        if ($stage < $last) {
            return redirect()->route($this->createRoute(), [...$routeParams, 'stage' => ++$stage]);
        }

        /** @var Collection<string,mixed> */
        $data = collect([]);

        for ($i = 1; $i <= $last; ++$i) {
            $session = $this->getStoredStage($i)
                ->map(function ($item) {
                    if (is_string($item) && str_starts_with($item, 'fl___')) {
                        $item = json_decode(str_replace('fl___', '', $item));
                        $file = app(TempFileManager::class)->get($item->filename) ?? null;

                        // @codeCoverageIgnoreStart
                        if (!$file) {
                            return null;
                        }
                        // @codeCoverageIgnoreEnd

                        return new UploadedFile($file->path(), $item->name, $item->mime);
                    }

                    return $item;
                });

            if (!$this->grouped) {
                $data = $data->merge($session);
                continue;
            }

            $data->put($stages[$i - 1][$this->groupByField], $session);
        }

        return $this->onFinish($data, function () use ($last) {
            for ($i = 1; $i <= $last; ++$i) {
                session()->forget($this->getSessionKey($i));
            }
        });
    }

    /**
     * Get the items that have been stored for the given stage.
     *
     * @param int $stage
     *
     * @return Collection
     */
    protected function getStoredStage(int $stage): Collection
    {
        return collect(session($this->getSessionKey($stage), []));
    }

    /**
     * As we cannot serialise uploaded files, we need to store them
     * temporarily and return the path where they are stored.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, int>
     */
    protected function prepareForSerialisation(array $data): array
    {
        $processed = [];

        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) {
                $details = [
                    'filename' => app(TempFileManager::class)->store($value),
                    'name' => $value->getClientOriginalName(),
                    'mime' => $value->getMimeType(),
                ];

                $processed[$key] = 'fl___' . json_encode($details);

                continue;
            }

            $processed[$key] = $value;
        }

        return $processed;
    }
}
