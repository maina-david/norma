@php use App\Services\Storage\MimeTypeManager; @endphp
<x-layouts.app>
  <x-slot name="header">
    @include('pages.compilation.my.context-question.applicability-header')
  </x-slot>


  <x-compilation.context-question.my.layout bordered>

    <livewire:compilation.context-question.requirements-setup :key="Str::random(64)" />

  </x-compilation.context-question.my.layout>
</x-layouts.app>


