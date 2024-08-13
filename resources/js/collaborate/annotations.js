import Highlighter from "./highlighter";

window.annotations = {
  evaluateSelector: function(el, selectors, contentRoute) {
    const volume = el.getAttribute('data-volume');
    const contentElement = document.querySelector('#work_expression_content');

    if (!contentElement || !selectors) {
      return;
    }

    const target = contentRoute + '?volume=' + volume;

    if (target === contentElement.getAttribute('src')) {
      this.scrollText(el, selectors, contentElement);
      return;
    }

    const listener = (event) => {
      const target = event.target.getAttribute('target');

      if (target === 'work_expression_content') {
        document.removeEventListener('turbo:before-stream-render', listener);

        setTimeout(() => {
          this.scrollText(el, selectors, contentElement);
        }, 1000);
      }
    };

    document.addEventListener('turbo:before-stream-render', listener);

    contentElement.setAttribute('src', target);
  },

  scrollText: function (el, selectors, contentElement, tries = 0) {
    if (selectors && tries < 3) {
      this.scrollToSection(contentElement, selectors)
        .then((resp) => {
          if (!resp) {
            setTimeout(() => {
              this.scrollText(el, selectors, contentElement, tries + 1);
            }, 500 * (tries + 1));
          }
        });
    }
  },

  refresh() {
    new Promise((resolve) => {
      localStorage.setItem('annotations_checked', JSON.stringify([]));
      resolve();
    });
  },
  getChecked() {
    return new Promise((resolve) => {
      const checked = JSON.parse(localStorage.getItem('annotations_checked') || '[]');
      const items = {};
      checked.forEach((item) => items[item] = true);

      resolve(items);
    });
  },
  setChecked(checked) {
    return new Promise((resolve) => {
      const selected = Object.keys(checked).filter((key) => checked[key])
      localStorage.setItem('annotations_checked', JSON.stringify(selected));

      resolve(selected);
    });
  },
  check(reference) {
    return new Promise((resolve) => {
      const checked = JSON.parse(localStorage.getItem('annotations_checked') || '[]');
      const index = checked.indexOf(reference);
      if (index !== -1) {
        checked.splice(index, 1);
      } else {
        checked.push(reference);
      }
      localStorage.setItem('annotations_checked', JSON.stringify(checked));

      resolve();
    })
  },
  scrollToSection(contentElement, selectors) {
    return Highlighter.anchor(contentElement, selectors)
        .then((range) => {
          if (range) {
            const start = range.startContainer.nodeType === Node.TEXT_NODE
                ? range.startContainer.parentElement
                : range.startContainer;

            start.scrollIntoView();

            return true;
          }
          return false;
        })
        .catch(() => false);
  },
}

// window.annotations.refresh()
