import { BaseCustomElement } from './BaseCustomElement';
import { addQueryArgs } from '@wordpress/url';

export class AnsPressDropdown extends BaseCustomElement {
  constructor() {
    super();

    this.as = this.getAttribute('as') || 'dropdown';

    this.innerHTML = `
    <div class="anspress-dropdown-handle">${this.as === 'field' ? `<div class="anspress-dropdown-search"><input type="text" class="anspress-form-input anspress-dropdown-search-input" placeholder="${this.getAttribute('label-search') || 'Search'}"></div>` : this.getAttribute('label')}</div>
    <div class="anspress-dropdown-selections"></div>
    <div class="anspress-dropdown-dropdown" style="display:none">
      ${this.as !== 'field' ? `<div class="anspress-dropdown-search">
        <input type="text" class="anspress-form-input anspress-dropdown-search-input" placeholder="${this.getAttribute('label-search') || 'Search'}">
      </div>` : ''}
      <div class="anspress-dropdown-items">
        <div class="anspress-dropdown-message">Loading...</div>
      </div>
    </div>
    `;

    this.classList.add('anspress-dropdown');

    this.handle = this.querySelector('.anspress-dropdown-handle');
    this.search = this.querySelector('.anspress-dropdown-search');
    this.items = this.querySelector('.anspress-dropdown-items');
    this.selections = this.querySelector('.anspress-dropdown-selections');
    this.dropdown = this.querySelector('.anspress-dropdown-dropdown');

    // Hide search if not enabled
    if (!this.data?.search_path) {
      this.search.style.display = 'none';
    }

    this.buildSelected();
  }

  addEventListeners() {
    this.handle.addEventListener('click', this.toggleDropdownHandler.bind(this));

    this.items.addEventListener('click', (e) => {
      let item = e.target.closest('.anspress-dropdown-item');
      if (!item) {
        return;
      }

      this.addSelection(item);
    });

    this.search.addEventListener('input', (e) => {
      clearTimeout(this.debounceTimeout); // Clear previous timeout
      this.debounceTimeout = setTimeout(() => { // Set new timeout
        let value = e.target.value;
        this.fetchOptions(value);
      }, 300); // 300 milliseconds debounce
    });

    this.selections.addEventListener('click', (e) => {
      let item = e.target.closest('.anspress-dropdown-selection');
      if (!item) {
        return;
      }

      this.removeSelection(item.getAttribute('data-key'));
    });

    document.addEventListener('click', this.handleOutsideClick.bind(this));

  }

  handleOutsideClick(event) {
    event.target
    if (!this.contains(event.target) && event.target.closest('.anspress-dropdown-active') !== this) {
      this.dropdown.style.display = 'none';
      this.classList.remove('anspress-dropdown-active');
    }
  }

  isMultiple() {
    return this.data?.multiple;
  }

  toggleDropdownHandler(e) {
    e.preventDefault();
    this.toggleDropdown();
  }

  toggleDropdown() {
    const showing = this.dropdown.style.display !== 'none';
    if (showing) {
      this.dropdown.style.display = 'none';
      this.classList.remove('anspress-dropdown-active');
      return;
    }

    if (!this.data?.search_path || this.initialOptionsFetched) {
      this.buildList();
    } else {
      this.fetchOptions().then(() => {
        this.initialOptionsFetched = true;
      })
    }

    this.classList.toggle('anspress-dropdown-active');
    let topPosition = this.handle.offsetTop + this.handle.offsetHeight;

    this.dropdown.style.display = 'block';
    this.dropdown.style.top = `${topPosition}px`;
  }

  buildList() {
    let options = this.data?.options;

    this.items.innerHTML = '';

    if (!options || options.length === 0) {
      this.items.innerHTML = `<div class="anspress-dropdown-message">No data found</div>`;
      return;
    }

    const selectedKeys = this.data?.selected?.map(item => item.key) || [];
    this.items.innerHTML = options.map(item => {
      let value = item?.value || item;
      let key = item?.key || item;
      let isSelected = selectedKeys?.filter(x => x == key)?.length > 0 ? 'anspress-dropdown-item-selected' : '';

      return `<div class="anspress-dropdown-item ${isSelected}" data-value="${value}" data-key="${key}">${value}</div>`;
    }).join('');
  }

  buildSelected() {
    if (this.getAttribute('show-selected') === 'false') {
      return;
    }
    let selected = this.data?.selected;

    this.selections.innerHTML = '';

    if (!selected) {
      return;
    }

    this.selections.innerHTML = selected.map(item => {
      let value = item?.value || item;
      let key = item?.key || item;

      return `<div class="anspress-dropdown-selection" data-value="${value}" data-key="${key}">
      <div class="anspress-dropdown-value">${value}</div>
      <div class="anspress-dropdown-removeitem">&times;</div>
      </div>`;
    }).join('');

  }

  addSelection(item) {
    let value = item.getAttribute('data-value');
    let key = item.getAttribute('data-key');

    if (!this.data?.selected) {
      this.data.selected = [];
    }

    // Check if the item is already selected
    const isAlreadySelected = this.data.selected.some(selectedItem => selectedItem.key === key);

    if (!this.isMultiple() || !isAlreadySelected) {
      if (!this.isMultiple()) {
        this.data.selected = [];
      }

      if (!isAlreadySelected) {
        this.data.selected.push({ key, value });
      }

      this.buildSelected();
      this.dropdown.style.display = 'none';
    }

    // Emit event
    this.dispatchEvent(new CustomEvent('selected', { detail: { ...this.data, id: this.elementId } }));
  }

  removeSelection(key) {
    this.setDataValue('selected', this.data.selected.filter(item => item.key != key));

    this.dispatchEvent(new CustomEvent('selected', { detail: { ...this.data, id: this.elementId } }));
    this.buildSelected();
  }

  fetchOptions(searchTerm = '') {
    this.items.innerHTML = `<div class="anspress-dropdown-message">Loading...</div>`;

    // Get all data except options and search_path.
    const { options, search_path, ...data } = this.data;

    const path = addQueryArgs(search_path, { search: searchTerm, ...data });
    return this.fetch({ path })
      .then(data => {
        const options = data?.data || {};

        if (Object.keys(options).length === 0) {
          throw new Error('No data found');
        }

        this.data.options = Object.keys(options).map(key => {
          if (this.data?.key_field && this.data?.value_field) {
            return { key: options[key][this.data.key_field], value: options[key][this.data.value_field] };
          }

          return options[key];
        });

        this.buildList();
      })
      .catch((e) => {
        console.error(e);
        this.items.innerHTML = `<div class="anspress-dropdown-message">No data found</div>`;
      });
  }

  get value() {
    return this.data?.selected;
  }
}

customElements.define('anspress-dropdown', AnsPressDropdown);
