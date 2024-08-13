@if (session('flash.message'))
  <script>
    window.setTimeout(function() {
      window.toast.{{ session('flash.type', 'success') }}({
        message: '{{ session('flash.message') }}'
      });
    }, 250);
  </script>
@endif
