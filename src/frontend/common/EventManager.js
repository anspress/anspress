import apiFetch from '@wordpress/api-fetch';

export class EventManager {
  constructor(containerSelector, requiredElements = []) {
    this.container = containerSelector;
    this.eventHandlers = [];
    this.requiredElements = requiredElements;

    // If no anspressel then throw an error.
    if (!this.container.getAttribute('data-anspressel')) {
      console.error('Container must have data-anspressel attribute.');
      return;
    }

    // Make sure all required elements are present.
    if (this.requiredElements.length) {
      this.requiredElements.forEach(selector => {
        // If selector starts with # then its required to be present in the container.

        if (selector.startsWith('#') && !this.elements[selector]) {
          console.error(`Required element not found: ${selector}`);
        }
      });
    }

    this.init();
  }

  eventMappings() {
    return [];
  }

  // Initialize event bindings
  init() {
    this.bindEvents();
    this.setupMutationObserver();
  }

  // Bind all events defined in eventMappings
  bindEvents() {
    this.eventMappings().forEach(mapping => {
      const elements = this.container.querySelectorAll(mapping.selector);
      elements.forEach(element => {
        let handler = (event) => {
          if (mapping?.cancel) event.preventDefault();
          mapping.handler.call(this, event, element);
        }

        this.eventHandlers.push({ element, eventType: mapping.eventType, handler });
        element.addEventListener(mapping.eventType, handler);
      });
    });
    this.bindAttributeEvents();
  }

  bindAttributeEvents() {
    const elements = this.container.querySelectorAll('[data-anspressel]');
    elements.forEach(element => {
      [...element.attributes].forEach(attr => {
        if (attr.name.startsWith('@')) {
          const [event, ...modifiers] = attr.name.slice(1).split('.');
          const handlerName = attr.value;
          const handler = this[handlerName].bind(this);
          const finalHandler = (event) => {
            if (modifiers.includes('prevent')) event.preventDefault();
            if (modifiers.includes('stop')) event.stopPropagation();
            handler(event, element);
          };
          element.addEventListener(event, finalHandler);
          this.eventHandlers.push({ element, eventType: event, handler: finalHandler });
        }
      });
    });
  }

  // Unbind all events
  unbindEvents() {
    this.eventHandlers.forEach(({ element, eventType, handler }) => {
      element.removeEventListener(eventType, handler);
    });
    this.eventHandlers = [];
  }

  setupMutationObserver() {
    let debounceTimer;
    const observer = new MutationObserver(() => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        this.unbindEvents();
        this.bindEvents();
      }, 100);
    });

    observer.observe(this.container, {
      childList: true,
      subtree: true
    });

    this.mutationObserver = observer;
  }

  // Disconnect the mutation observer and unbind all events
  destroy() {
    if (this.mutationObserver) {
      this.mutationObserver.disconnect();
    }
    this.unbindEvents();
  }

  el(selector) {
    return this.container.querySelector(selector);
  }

  apEl(selector) {
    return this.container.querySelector(`[data-anspressel="${selector}"]`);
  }

  get containerId() {
    return this.container.getAttribute('data-anspressel')
  }

  set data(data) {
    console.log(`Setting data for ${this.containerId}`)
    this._data = data;
    this.container.dataset.anspress = JSON.stringify(data);
    console.log(this.data)
    this.triggerUpdateMethods();
  }

  get data() {
    // Cache the parsed data object
    if (this._data) return this._data;

    console.log(`Setting _data for ${this.containerId}`)

    this._data = JSON.parse(this.container.dataset.anspress || '{}');

    return this._data;
  }

  get elements() {
    const elements = {};
    const nodeList = this.container.querySelectorAll('[data-anspressel]');
    nodeList.forEach(element => {
      const name = element.getAttribute('data-anspressel');
      elements[name] = element;
    });

    return elements;
  }

  updateElements() {
    return [];
  }

  triggerUpdateMethods() {
    if (!this.updateElements()) {
      return;
    }

    const elms = this.updateElements();

    Object.keys(elms).forEach((selector) => {
      const dataKey = elms[selector];
      const el = this.elements[selector];
      console.log(`Updating ${selector} with ${dataKey}`)
      if (el) {
        console.log(typeof dataKey, selector, el)
        if ('function' === typeof dataKey) {
          el.innerHTML = dataKey(selector, el);
          return;
        }
        el.innerHTML = this.data[dataKey];
      } else {
        console.error(`Element not found: ${selector}`);
      }
    });
  }

  fetch(path, options) {
    return apiFetch(path, options)
      .then(res => {
        console.log(this.containerId)
        if (res[`${this.containerId}Data`]) {
          this.data = res[`${this.containerId}Data`];
        }

        // Replace the container with the new content
        if (res[`${this.containerId}Html`]) {
          this.container.innerHTML = res[`${this.containerId}Content`];
        }
        console.log(res[`${this.containerId}Messages`])
        if (res[`${this.containerId}Messages`]) {
          for (const snackbarItem of res[`${this.containerId}Messages`]) {
            const event = new CustomEvent('anspress-snackbar', {
              detail: { message: snackbarItem.message, type: snackbarItem.type || 'success', duration: 5000 }
            });

            document.body.dispatchEvent(event);
          }
        }

        return res;
      })
      .catch(err => console.error(err));
  }
}
