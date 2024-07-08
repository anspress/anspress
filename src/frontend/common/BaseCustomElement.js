import { fetch } from './AnsPressCommon.js';

export class BaseCustomElement extends HTMLElement {
  constructor() {
    super();
    this._cachedData = null;
  }

  static get observedAttributes() {
    return ['data-anspress'];
  }

  connectedCallback() {
    this.updateComponent();
    this.addEventListeners();
  }

  disconnectedCallback() {
    this.removeEventListeners();
  }

  attributeChangedCallback(name, oldValue, newValue) {
    if (name === 'data-anspress') {
      this.updateComponent();
      this._cachedData = null; // Invalidate cache
    }
  }

  get data() {
    if (!this._cachedData) {
      this._cachedData = JSON.parse(this.getAttribute('data-anspress'));
    }
    return this._cachedData;
  }

  set data(data) {
    console.log('Setting data', data)
    this._cachedData = data;
    this.setAttribute('data-anspress', JSON.stringify(data));
  }

  get elementId() {
    return this.getAttribute('data-anspress-id');
  }

  setDataValue(key, value) {
    this.data[key] = value;
    this.setAttribute('data-anspress', JSON.stringify(this.data));
  }

  updateComponent() {
    // throw new Error('updateComponent method must be implemented by subclasses');
  }

  addEventListeners() {
    throw new Error('addEventListeners method must be implemented by subclasses');
  }

  removeEventListeners() {
  }

  fetch(options) {
    return fetch(options, { templateId: this.getTemplateId(), blockName: this.getBlockName() }).then(res => {
      return res || {};
    })
  }

  getElData(el, key) {
    const data = el.getAttribute(`data-${key}`);

    if (!data) {
      return {};
    }

    return JSON.parse(data);
  }

  getTemplateId() {
    return this.closest('[data-anspress-template]')?.getAttribute('data-anspress-template') || '';
  }

  getBlockName() {
    return this.closest('[data-anspress-block]')?.getAttribute('data-anspress-block') || '';
  }
}
