@props(['loop', 'striped' => true])

<tr {{ $attributes->merge(['class' => $loop->even && $striped ? 'bg-norma-gray-50' : 'bg-white']) }}>
  {{ $slot }}
</tr>
