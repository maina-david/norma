<x-corpus.work.work-data-table :base-query="$baseQuery"
                               searchable
                               :fields="['title_for_selector', 'status', 'locations', 'work_type']"
                               :route="$route"
                               filterable
                               :filters="['countries']" />
