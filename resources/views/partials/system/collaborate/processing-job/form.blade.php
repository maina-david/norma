{{-- <x-storage.collaborate.uploader :route="route('collaborate.processing-jobs.store')" required>
  <x-slot:formBody>
  </x-slot:formBody>
</x-storage.collaborate.uploader> --}}

File upload:
<input
       id="file"
       name="file"
       type="file"
       accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />

<x-ui.button class="mt-10" type="submit">Upload</x-ui.button>
