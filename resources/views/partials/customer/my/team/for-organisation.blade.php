<x-customer.team.team-data-table :base-query="$baseQuery"
                                 :route="route('my.settings.teams.for.organisation.index', ['organisation' => $organisation->id])"
                                 searchable
                                 :paginate="50">
</x-customer.team.team-data-table>
