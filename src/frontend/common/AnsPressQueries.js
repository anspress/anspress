import { BaseCustomElement } from './BaseCustomElement';
import { addQueryArgs } from '@wordpress/url';

export class AnsPressQueries extends BaseCustomElement {
  constructor() {
    super();

    this.selections = this.querySelector('.anspress-questions-queries-selections');

    this.data = {
      'keywords': this.querySelector('input[data-anspress-id="keywords"]')?.value,
      'args:filter': this.querySelector('anspress-dropdown[data-anspress-id="args:filter"]')?.data?.selected,
      'args:orderby': this.querySelector('anspress-dropdown[data-anspress-id="args:orderby"]')?.data?.selected,
      'args:order': this.querySelector('anspress-dropdown[data-anspress-id="args:order"]')?.data?.selected,
      'args:categories': this.querySelector('anspress-dropdown[data-anspress-id="args:categories"]')?.data?.selected,
      'args:tags': this.querySelector('anspress-dropdown[data-anspress-id="args:tags"]')?.data?.selected,
    };
  }

  addEventListeners() {
    this.querySelectorAll('anspress-dropdown').forEach(el => el.addEventListener('selected', this.onChange.bind(this)));

    this.selections.addEventListener('click', this.selectionRemoveHandler.bind(this));
    this.querySelector('.anspress-questions-args-submit').addEventListener('click', this.submit.bind(this));
  }

  removeEventListeners() {
    this.querySelectorAll('anspress-dropdown').forEach(el => el.removeEventListener('selected', this.onChange.bind(this)));
    this.selections.removeEventListener('click', this.selectionRemoveHandler.bind(this));
    this.querySelector('.anspress-questions-args-submit').removeEventListener('click', this.submit.bind(this));
  }

  updateComponent() {
    this.buildSelected();
    this.addClassToActiveDropdown();
  }

  onChange(e) {
    if (!this.data[e.detail.id]) {
      this.data[e.detail.id] = [];
    }
    this.setDataValue(e.detail.id, e.detail.selected);
  }

  buildSelected() {
    this.selections.innerHTML = '';

    let html = '';
    Object.keys(this.data).forEach(key => {
      const dpData = this.data[key];

      if (!dpData || !Array.isArray(dpData)) return;

      html += dpData?.map(item => {
        if (!item) return '';
        let value = item?.value || item;
        let optkey = item?.key || item;

        return `<div class="anspress-dropdown-selection" data-dp="${key}" data-value="${value}" data-key="${optkey}">
      <div class="anspress-dropdown-value">${value}</div>
      <div class="anspress-dropdown-removeitem">&times;</div>
      </div>`;
      }).join('');
    });

    this.selections.innerHTML = html;

  }

  addClassToActiveDropdown() {
    this.querySelectorAll('anspress-dropdown').forEach(el => {
      el.querySelector('.anspress-questions-queries-count')?.remove();
      el.classList.remove('anspress-questions-queries-active');
      const handle = el.querySelector('.anspress-dropdown-handle');

      if (el.data?.selected?.length) {
        el.classList.add('anspress-questions-queries-active');
        // Also append count to the dropdown
        const count = document.createElement('span');
        count.classList.add('anspress-questions-queries-count');
        count.innerText = el.data.selected.length;
        handle.appendChild(count);
      }
    });
  }

  selectionRemoveHandler(e) {
    if (e.target.classList.contains('anspress-dropdown-removeitem')) {
      const selection = e.target.closest('.anspress-dropdown-selection');
      const key = selection.getAttribute('data-key');
      const dp = selection.getAttribute('data-dp');
      console.log(dp, key);

      this.setDataValue(dp, this.data[dp].filter(item => item.key !== key));

      this.querySelector(`anspress-dropdown[data-anspress-id="${dp}"]`).removeSelection(key);
    }
  }

  submit(e) {
    e.preventDefault();

    let args = {};

    Object.keys(this.data).forEach(key => {
      if (this.data[key]) {
        if (key === 'keywords') {
          return;
        }
        args[key] = this.data[key].map(item => item.key);
      }
    });
    args.keywords = this.querySelector('input[data-anspress-id="keywords"]')?.value;

    window.location.href = addQueryArgs(window.location.href, args);

  }
}

customElements.define('anspress-queries', AnsPressQueries);
