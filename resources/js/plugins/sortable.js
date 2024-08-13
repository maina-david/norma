import Sortable from 'sortablejs';

window.initSortable = () => {
  document.querySelectorAll('.sortable[data-update-input]').forEach((el) => {
    el._sortable = Sortable.create(el, {
      onUpdate: (ev) => {
        const values = Array.from(ev.target.querySelectorAll(':scope > [data-value]'))
          .map((node) => node.getAttribute('data-value'))
          .join(',');

        const toUpdate = el.getAttribute('data-update-input');

        document.querySelectorAll(el.getAttribute('data-update-input')).forEach((input) => input.value = values);
      }
    });
  });
};
