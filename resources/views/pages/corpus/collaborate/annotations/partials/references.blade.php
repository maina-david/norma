@php
  $showBulkActions = !($previewing ?? false) && auth()->user()->can('collaborate.corpus.work-expression.use-bulk-actions');
@endphp

<div
    class="flex flex-col overflow-hidden h-full"
    x-data="{
    selectedRefs: 'undefined' === typeof selectedRefs ? {} : selectedRefs,
    toggleOpenRef: function (el) {
      document.querySelectorAll('.active').forEach(function (n) { n.classList.remove('active') });
      el.closest('.rfr').classList.add('active');
    },
    toggleRef(ref) {
      if (this.selectedRefs[ref]) {
        delete this.selectedRefs[ref];
        return;
      }

      this.selectedRefs[ref] = true;
    },
    selectAll: function($event) {
      var value = $event.target.checked;
      var that = this;
      if (Object.keys(this.selectedRefs).length < 1) {
        document.querySelectorAll('.reference_action').forEach(function (item) { that.selectedRefs[item.getAttribute('data-id')] = true });
      } else {
        Object.keys(this.selectedRefs).forEach(function (key) { delete that.selectedRefs[key] });
      }
{{--      window.annotations.setChecked(this.selectedRefs);--}}
    },
    showMeta(ref, activeTab) {
      document.querySelectorAll('.ref-meta .ref-meta-holder').forEach(function (element) {
        element.parentElement.innerHTML = '';
      });

      var el = document.querySelector('#meta_' + ref + '_attach');
      var source = document.querySelector('#annotation-meta-template');

      if (el && source) {
        var content = document.createElement('div');
        var holder = document.createElement('div');
        holder.classList.add('ref-meta-holder');
        holder.appendChild(content);
        el.appendChild(holder);

        var replaceWith = source.innerHTML
          .replace(/\/met_temp\//g, '/' + ref + '/')
          .replace(/\s*tab:\s*'assessmentItems'\s*/, 'tab:\'' + activeTab + '\'')

        Alpine.morph(content, replaceWith);
        setTimeout(function () {
          window.findAndInitSelects(el);
        }, 500);
      }
    },
  }"
>
  @include('pages.corpus.collaborate.annotations.partials.reference-filters')


  <div class="flex-grow w-full overflow-hidden">
    <div class="h-full flex flex-col" id="annotation-references">
      <div class="flex-grow bg-white roundedw-full pt-1 pb-4 pl-4 pr-2 custom-scroll overflow-auto space-y-2">
        @foreach($references as $reference)
          @php
            if ($reference->id == $active) {
              $reference->load(['consequences']);
            }
          @endphp
          @include('pages.corpus.collaborate.annotations.partials.reference-card', ['active' => $reference->id == $active])
        @endforeach
      </div>

      <div class="flex-shrink-0 bg-white px-2 pb-1 overflow-x-auto custom-scroll">
        {{ $references->links() }}
      </div>
    </div>
  </div>
</div>

@if(auth()->user()->cannot('collaborate.corpus.work-expression.use-bulk-actions'))
  <div
      class="meta-loader"
      x-init="function () {
        this.loading = true;
        var that = this;
        setTimeout(function () {
          that.loading = false;
          document.querySelectorAll('.meta-loader').forEach(function (loader) {
            loader.remove();
          });
       }, 2000);
      }"
  ></div>
@endif

