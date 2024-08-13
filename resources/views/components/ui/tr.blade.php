@props(['loop', 'striped' => true])

<tr {{ $attributes->merge(['class' => $loop->even && $striped ? 'bg-libryo-gray-50' : 'bg-white']) }}>
  {{ $slot }}
</tr>
