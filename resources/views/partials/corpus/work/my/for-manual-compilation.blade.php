<x-corpus.work.work-data-table :base-query="$baseQuery"
                               searchable
                               :fields="['title_manual_compilation', 'locations', 'work_type']"
                               :route="route('my.settings.compilation.works.for.manual.compilation')"
                               filterable
                               :filters="['countries']" />
