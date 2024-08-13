<script data-turbo-cache="false">
  window.setTimeout(() => {
    if (window.hj) {
      window.hj('identify', {{ $user->id }}, {
        'user_type': {{ $user->user_type }},
      });
    }
  }, 500);
</script>
