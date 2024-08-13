export default {
  methods: {
    cleanStyles(element) {
      element.querySelectorAll('style').forEach((style) => {
        // eslint-disable-next-line no-param-reassign
        style.textContent = style.textContent.replace(/\.libryo-legislation/gm, '');
      });

      return element;
    },

    constrainStyles(element) {
      element.querySelectorAll('style').forEach((style) => {
        // eslint-disable-next-line no-param-reassign
        style.textContent = this.generateCssFromRules(style.sheet.cssRules);
      });
    },

    generateCssFromRules(rules) {
      const { length } = rules;
      const css = [];

      /* eslint-disable no-continue, no-plusplus */
      for (let i = 0; i < length; i++) {
        const rule = rules.item(i);

        if (rule instanceof CSSMediaRule || rule instanceof CSSSupportsRule) {
          const extracted = this.generateCssFromRules(rule.cssRules);
          const type = rule instanceof CSSMediaRule ? '@media' : '@supports';
          css.push(`${type} ${rule.conditionText} {${extracted}}`);

          continue;
        }

        if (rule instanceof CSSStyleRule) {
          css.push(this.generateCssFromRule(rule));

          continue;
        }

        css.push(rule.cssText);
      }

      return css.join('\n');
    },

    generateCssFromRule(rule) {
      const selector = rule.selectorText.replace(/([^,]+)(\s*,?\s*)/g, '.libryo-legislation $1$2');
      const selectorText = rule.selectorText.replace(/([*])/g, '\\$1');

      return rule.cssText.replace(new RegExp(`^${selectorText}`), selector);
    },
  },
};
