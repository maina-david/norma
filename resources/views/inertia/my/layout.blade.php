<x-layouts.app :plain-layout="true" :no-drive="true">

  @section('docHead')
{{--    <meta name="turbo-visit-control" content="reload">--}}
    @vite(['resources/js/inertia.js'])
    @inertiaHead
    <script>
    // document.addEventListener("turbo:load", () => {
    //     Turbo.session.drive = false;
    // });
    </script>
  @endsection

  @inertia('my-norma')

</x-layouts.app>
