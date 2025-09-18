import TomSelect from 'tom-select/dist/js/tom-select.popular';
import { debounce } from 'lodash';

const getPlugins = (select) => {
  const plugins = [];

  if (select.hasAttribute('data-with-remove')) {
    plugins.push('remove_button');
  }

  return plugins;
};

/**
 * Sets up all selects with class .remote-select to allow for remote loading.
 * The data-load attribute set on the select elemeent specifies the remote url to fetch.
 */
const initRemoteSelect = (select) => {
  if (!select.classList.contains('remote-select') || select.tomselect !== undefined) {
    return false;
  }
  const startSearching = Number.parseInt(select.getAttribute('data-load-start-searching') || '3', 10);
  const fetchOnLoad = select.hasAttribute('data-fetch-on-load');
  const allowCreate = select.getAttribute('data-load-create') === 'true';
  const searchField = select.getAttribute('data-load-search-field');

  return new TomSelect(select, {
    plugins: getPlugins(select),
    allowEmptyOption: !!select.getAttribute('data-allow-empty'),
    valueField: select.getAttribute('data-load-value-field') || 'id',
    labelField: select.getAttribute('data-load-label-field') || 'title',
    searchField: searchField === 'no-filter' ? false : searchField || 'title',
    placeholder: select.getAttribute('placeholder') || '',
    maxOptions: null,
    create: allowCreate,
    onInitialize: function () {
      if (fetchOnLoad) {
        this.load(null);
      }
    },
    shouldLoad: (query) => {
      return fetchOnLoad || query.length >= startSearching;
    },
    load: function (query, callback) {
      const url = `${select.getAttribute('data-load')}`;
      axios.get(url, {
        params: {
          search: query,
        },
      })
        .then((response) => response.data)
        .then((data) => {
          this.clearOptions();

          callback(data);

          // since we've loaded the items, no need to fetch again.
          if (fetchOnLoad) {
            this.settings.load = null;
          }
        }).catch(()=>{
          callback();
        });
    },
    render: {
      not_loading: (data, escape) => {
        const text =select.getAttribute('data-keep-typing') || 'Keep typing to search....';

        return `<div class="p-2 italic">${ text }</div>`;
      },
      no_results: function (data,escape){
        const text = `${select.getAttribute('data-no-results') || 'No results found for'} "'${escape(data.input)}'"`;

        return `<div class="p-2 italic">${ text }</div>`;
      },
      option: function (data, escape) {
        const detailStr = data.details ? '<div class="text-norma-gray-500">' + escape(data.details) + '</div>' : '';
        return '<div>' +
            '<div>' + escape(data.title) + '</div>' +
            detailStr +
            '</div>';
      },
      item: function (data, escape) {
        return '<div title="' + escape(data.details || '') + '">' + escape(data.title) + '</div>';
      },
    },
  });
};

const initialiseTom = (select) => {
  if (select.tomselect && select.hasAttribute('data-tomselect-no-reinitialise')) {
    return;
  }

  if (select.tomselect) {
    select.tomselect.destroy();
  }

  //bit of a hack for when a turbo history restoration visit happens.
  //as the select already has the elements added because the page is retrieved from cache,
  //it causes problems when trying to init the TomSelect on the element. TomSelect.destroy() doesn't work
  //either because there is no tomselect element on the select
  select.classList.remove('tomselected');
  select.classList.remove('ts-hidden-accessible');
  if (select.nextElementSibling && select.nextElementSibling.classList.contains('ts-wrapper')) {
    select.nextElementSibling.remove();
  }

  let tom = initRemoteSelect(select);

  if (tom === false && select.tomselect === undefined) {
    tom = new TomSelect(select, {
      plugins: getPlugins(select),
      allowEmptyOption: !!select.getAttribute('data-allow-empty'),
      placeholder: select.getAttribute('placeholder') || '',
      maxOptions: null,
      render: {
        option: function (data, escape) {
          let detailStr = data.$option.getAttribute('data-detail') || '';
          detailStr = detailStr.length > 0 ? '<div class="text-norma-gray-500">' + detailStr + '</div>' : '';

          return '<div>' +
              '<div>' + data.$option.innerHTML + '</div>' +
              detailStr +
              '</div>';
        },
        item: function (data, escape) {
          let detailStr = data.$option.getAttribute('data-detail') || '';

          return '<div title="' + escape(detailStr || '') + '">' + data.$option.innerHTML + '</div>';
        },
      },
    });
  }

  if (tom) {
    tom.on('change', (detail) => {
      const event = new CustomEvent('tom-changed', { detail });
      select.dispatchEvent(event);
    });
  }
};

window.findAndInitSelects = debounce((selector) => {
  selector = selector || document;
  selector.querySelectorAll('select[data-tomselect="true"]').forEach((select) => {
    initialiseTom(select);
  });
}, 200);
