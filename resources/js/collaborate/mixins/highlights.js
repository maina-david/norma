/* eslint-disable no-param-reassign */
import {mapActions, mapState} from 'vuex';
import highlighter from '../norma-highlighter';
import LocalHighlighter from '../highlighter';
import {namesAsKey} from '../reference-types';

window.highlighter = highlighter;
export default {
  data() {
    return {
      highlighter,
      highlightListener: null,
      paragraphOverListener: null,
      paragraphOutListener: null,
      highlighterWidth: 300,
      highlighterParagraphShowing: false,
      paragraphButtonTimeout: null,
      highlighterClass: 'norma-highlight',
      highlighterIdAttributeName: 'data-highlight-id',
      highlighterDialogShowing: false,
      recentlyUsedTypes: [namesAsKey.TYPE_OBLIGATION, namesAsKey.TYPE_PROHIBITION, namesAsKey.TYPE_CONSEQUENCE_GROUP],
    };
  },
  computed: {
    ...mapState({
      showHighlighterDialog: (state) => state.annotations.showHighlighterDialog,
      currentRange: (state) => state.annotations.currentRange,
      currentTextSelection: (state) => state.annotations.currentTextSelection,
      currentParagraph: (state) => state.annotations.currentParagraph,
    }),
  },
  methods: {
    ...mapActions('annotations', ['setState']),

    selectParagraph({ clickEv, adderElement }) {
      const range = document.createRange();
      range.setStart(this.currentParagraph, 0);
      range.setEnd(this.currentParagraph, this.currentParagraph.childNodes.length);
      document.getSelection().removeAllRanges();
      highlighter.getSelection().addRange(range);
      this.handleOnHighlight({
        ev: clickEv,
        adderElement,
        x: clickEv.clientX,
      });
    },
    removeEventListeners(el, type) {
      const listeners = window.getEventListeners(el);
      if (!listeners[type]) {
        return;
      }
      listeners[type].forEach((listener) => {
        el.removeEventListener(type, listener);
      });
    },
    startListeningForSelections(contentContainerId, element) {
      this.highlightListener = highlighter.registerSelectionListener(
        document.getElementById(contentContainerId),
        () => this.setState({ showHighlighterDialog: false }),
        (ev) => this.handleOnHighlight({ ev, adderElement: element }),
      );
    },
    handleOnHighlight({ ev, adderElement, x }) {
      const r = highlighter.getSelection().getRangeAt(0);
      this.setState({
        currentRange: highlighter.anchor.range.sniff(r),
        currentTextSelection: window.getSelection().toString(),
      });
      let left = x || ev.clientX - this.highlighterWidth;
      left = left < 40 ? 40 : left;
      this.showHighlighter({
        element: adderElement,
        x: `${left}px`,
        y: `${ev.clientY}px`,
      });
    },
    showHighlighter({ element, x, y }) {
      document.body.appendChild(element);
      element.style.left = x;
      element.style.top = y;
      element.style.position = 'absolute';
      this.setState({ showHighlighterDialog: true });
    },
    hideHighlighter() {
      window.getSelection().removeAllRanges();
      this.setState({ showHighlighterDialog: false });
    },
    stopListeningForSelections(contentContainerId) {
      const el = document.getElementById(contentContainerId);
      highlighter.deregisterSelectionListener(el, this.highlightListener);
      this.highlightListener = null;
    },
    startListeningForParagraphHover(contentContainerId, element) {
      const el = document.getElementById(contentContainerId);
      const x = el.getBoundingClientRect().right - 10;
      this.paragraphOverListener = (e) => {
        let targetP = e.target;
        while (!targetP.parentNode.isSameNode(el)) {
          targetP = targetP.parentNode;
        }
        this.showHighlightParagraph({
          element,
          paragraph: targetP,
          x,
          y: targetP.getBoundingClientRect().y - 10,
        });
      };

      this.paragraphOutListener = () => {
        this.hideHighlightParagraph();
      };

      document.querySelectorAll(`#${contentContainerId}>*`).forEach((n) => {
        n.addEventListener('mouseover', this.paragraphOverListener);
        n.addEventListener('mouseout', this.paragraphOutListener);
      });
    },
    stopListeningForParagraphHover(contentContainerId) {
      document.querySelectorAll(`#${contentContainerId}>*`).forEach((n) => {
        n.removeEventListener('mouseover', this.paragraphOverListener);
        n.removeEventListener('mouseout', this.paragraphOutListener);
      });
    },
    showHighlightParagraph({
      element, paragraph, x, y,
    }) {
      document.body.appendChild(element);
      element.style.left = `${x}px`;
      element.style.top = `${y}px`;
      element.style.position = 'absolute';
      this.highlighterParagraphShowing = true;
      this.setState({ currentParagraph: paragraph });
    },
    hideHighlightParagraph() {
      if (this.paragraphButtonTimeout) {
        return;
      }

      this.paragraphButtonTimeout = window.setTimeout(() => {
        this.highlighterParagraphShowing = false;
        this.paragraphButtonTimeout = null;
      }, 4000);
    },

    scrollToSelector(contentContainerId, selectors, click = false) {
      return LocalHighlighter.anchor(document.querySelector(`#${contentContainerId}`), selectors)
        .then((range) => {
          let start = range.startContainer;
          if (start) {
            start = range.startContainer.nodeType === Node.TEXT_NODE
              ? range.startContainer.parentElement
              : range.startContainer;

            start.scrollIntoView();
            if (click) {
              start.click();
            }
          }

          return start.closest('[data-index]');
        })
        .catch(() => {
        });
    },

    scrollToHighlight(contentContainerId, id, click = false) {
      return new Promise((resolve) => {
        const cont = document.getElementById(contentContainerId);
        const element = cont.querySelector(`[${this.highlighterIdAttributeName}="${id}"]`);
        if (element) {
          setTimeout(() => element.scrollIntoView(), 250);

          if (click) {
            element.click();
          }
        }

        resolve(element);
      });
    },
    clearHighlights(contentContainerId, except = []) {
      const references = Array.from(document.querySelectorAll(`#${contentContainerId} .${this.highlighterClass}`))
        .filter((item) => !except.includes(parseInt(item.getAttribute(this.highlighterIdAttributeName), 10)));

      return highlighter.renderHighlights.removeHighlights(references);
    },
    showHighlights(references, sourceIndex = null) {
      const cont = this.$refs.content;

      return this.handleShowingHighlights(cont, references, -1, sourceIndex);
    },

    handleShowingHighlights(container, references, index = -1, sourceIndex = null) {
      const toProcess = [...references];
      const reference = toProcess.splice(0, 1);

      if (reference.length < 1) {
        return Promise.resolve();
      }

      return this.anchorAndDraw(container, reference[0], index + 1, sourceIndex)
        .then(() => {
          if (toProcess.length > 0) {
            return this.handleShowingHighlights(container, toProcess, index + 1, sourceIndex);
          }

          return true;
        });
    },

    anchorAndDraw(container, reference, index, sourceIndex = null) {
      return this.anchorReference(container, reference)
        .then((range) => {
          if (range) {
            return this.drawHighlight({
              ref: reference,
              index: sourceIndex || index,
              range: highlighter.anchor.range.sniff(range).normalize(),
            });
          }

          return false;
        })
        .catch(() => {
          //
        });
    },

    anchorReference(container, reference) {
      return LocalHighlighter.anchor(container, reference.selectors);
    },

    drawHighlight({ ref, range, index }) {
      const options = { ...highlighter.renderHighlights.optionDefaults };
      options.highlightClass = this.highlighterClass;
      options.idAttributeName = this.highlighterIdAttributeName;
      options.cssClasses = [`bg-ref-${ref.type}`, 'bg-transparent', ref.referenceable_type];
      options.customAttributes = [
        { key: 'data-index', value: index },
      ];

      const highlightElements = highlighter.renderHighlights.highlightRange(range, ref.id, options);

      highlightElements.forEach((el) => {
        if (ref.summary_id && el.parentElement && this.showIcons) {
          const div = document.createElement('div');
          div.innerHTML = '<i name="file-alt" style="position:absolute;left:-1.5rem;top:0.25rem;" class="norma-icon fal fa-file-alt fa-xs text-danger"></i>';
          el.parentElement.appendChild(div.firstChild);
        }
        el.addEventListener('click', (event) => {
          event.stopPropagation();
          if (this.onClick) {
            this.onClick({ event, id: ref.id, index });
          }
        });
      });

      return highlightElements;
    },

    createHighlight(contentContainerId, type) {
      const root = document.getElementById(contentContainerId);
      const selectors = highlighter.anchor.htmlAnchoring.describe(root, this.currentRange, {
        ignoreClasses: [this.highlighterClass],
      });
      let paragraph = this.currentRange.startContainer;

      while (paragraph && (paragraph.nodeType !== 1 || !['P', 'DIV', 'TABLE'].includes(paragraph.nodeName))) {
        paragraph = paragraph.parentNode;
      }

      if (type === namesAsKey.TYPE_CITATION) {
        return this.createCitationHighlight({ paragraph, selectors });
      }

      const content = selectors[2].exact;

      document.getSelection().removeAllRanges();
      this.hideHighlighter();
      this.pushRecentlyUsed(type);

      return {
        text: content && content.length > 0 ? content : this.currentTextSelection,
        type,
        start: selectors[1].start,
        selectors,
        indent: 0,
      };
    },

    createCitationHighlight({ paragraph, selectors }) {
      const content = selectors[2].exact;
      let usable = paragraph && paragraph.getAttribute('data-id')
        ? paragraph
        : paragraph.querySelector('[data-id]');

      if (!usable && paragraph.nodeName === 'TABLE' && paragraph.parentElement.hasAttribute('data-id')) {
        usable = paragraph.parentElement;
      }

      if (usable) {
        const dataId = usable.getAttribute('data-id');
        usable = document.querySelector(`[data-level][data-id="${dataId}"]`);
      }

      const level = usable && usable.getAttribute('data-level')
        ? usable.getAttribute('data-level')
        : paragraph.getAttribute('data-level');
      const nodeType = usable && usable.getAttribute('data-type')
        ? usable.getAttribute('data-type')
        : paragraph.getAttribute('data-type');

      if (level === null) {
        const message = 'The given selection does not contain a valid level. Please modify the source document and add the heading level';
        window.toast.error({ message, timeout: 10000 });
        this.hideHighlighter();
        throw Error(message);
      }

      document.getSelection().removeAllRanges();
      this.hideHighlighter();
      this.pushRecentlyUsed(namesAsKey.TYPE_CITATION);

      return {
        content: paragraph.outerHTML,
        text: content && content.length > 0 ? content : this.currentTextSelection,
        type: namesAsKey.TYPE_CITATION,
        start: selectors[1].start,
        selectors,
        number_citation_id: null,
        heading_citation_id: null,
        sub_heading_citation_id: null,
        position: 0,
        node_type: nodeType,
        level,
        indent: 0,
        is_toc_item: true,
        visible: true,
        is_section: true,
      };
    },

    pushRecentlyUsed(type) {
      if (this.recentlyUsedTypes.includes(type)) {
        return;
      }

      this.recentlyUsedTypes.unshift(type);
      this.recentlyUsedTypes.pop();
    },
  },
};
