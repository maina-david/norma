<x-compilation.library.library-data-table :base-query="$baseQuery"
                                          :route="route('my.settings.compilation.library.for.reference.index', ['reference' => $reference])"
                                          searchable
                                          :paginate="50">

</x-compilation.library.library-data-table>
