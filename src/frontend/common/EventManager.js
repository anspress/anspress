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
    // this.setupMutationObserver();
  }

  rebindEvents() {
    this.unbindEvents();
    this.bindEvents();
  }

  // Bind all events defined in eventMappings
  bindEvents() {
    this.eventMappings().forEach(mapping => {
      const elements = this.container.querySelectorAll(mapping.selector);
      elements?.forEach(element => {
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
    console.log(`Binding attribute events for ${this.containerId}`)
    let elements = this.container.querySelectorAll('[data-anspressel]');

    // Also include the container itself if it has any event attributes.
    if (this.container.attributes) {
      [...this.container.attributes].forEach(attr => {
        if (attr.name.startsWith('@')) {
          elements = [...elements, this.container];
        }
      });
    }

    elements.forEach(element => {
      [...element.attributes].forEach(attr => {
        if (attr.name.startsWith('@')) {
          console.log(`Binding attribute event for ${attr.name} on ${element}`)
          const [event, ...modifiers] = attr.name.slice(1).split('.');
          const handlerName = attr.value;

          if (!this[handlerName]) {
            console.error(`Handler ${handlerName} not found.`, element);
            return;
          }

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
    const observer = new MutationObserver((mutations) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        let shouldRebind = false;

        mutations.forEach((mutation) => {
          console.log(mutation.addedNodes)
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('data-anspressel')) {
              shouldRebind = true;
            }
          });

          mutation.removedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('data-anspressel')) {
              shouldRebind = true;
            }
          });
        });

        if (shouldRebind) {
          console.log('Mutation detected. Rebinding events: ' + this.containerId);
          this.unbindEvents();
          this.bindEvents();
        }
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

  static dispatchSnackbar(message, type = 'success', duration = 5000) {
    const event = new CustomEvent('anspress-snackbar', {
      detail: { message, type, duration }
    });

    document.body.dispatchEvent(event);
  }

  replaceContainer(html) {
    this.destroy();

    // Create a temporary container to hold the new HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html.trim();

    // Get the new container element from the temporary container
    const newContainer = tempDiv.firstChild;

    // Find the parent of the current container
    const parent = this.container.parentNode;

    // Replace the old container with the new one
    parent.replaceChild(newContainer, this.container);

    // Update the container reference
    this.container = newContainer;

    // Reinitialize the class
    this.init();
  }

  fetch(path, options) {
    return apiFetch(path, options)
      .then(res => {
        if (res[`${this.containerId}Data`]) {
          this.data = res[`${this.containerId}Data`];
        }

        // Replace the container with the new content
        if (res[`${this.containerId}Html`]) {
          this.replaceContainer(res[`${this.containerId}Html`]);
        }

        if (res[`${this.containerId}Messages`]) {
          for (const snackbarItem of res[`${this.containerId}Messages`]) {
            EventManager.dispatchSnackbar(snackbarItem.message, snackbarItem.type || 'success', 5000);
          }
        }

        if (res.errors && Array.isArray(res.errors) && res.errors.length) {
          res.errors.map(snackbarItem => this.dispatchSnackbar(snackbarItem.message, 'error', 5000));
        }

        if (res?.appendHtmlTo) {
          Object.keys(res.appendHtmlTo).forEach(key => {
            const appendTo = document.querySelector(key);
            console.log(appendTo)
            if (appendTo) {
              appendTo.insertAdjacentHTML('beforeend', res.appendHtmlTo[key]);
            }
          })
        }

        // Handle replaceHtml.
        if (res?.replaceHtml) {
          Object.keys(res.replaceHtml).forEach(key => {
            const replaceEl = document.querySelector(key);
            console.log(replaceEl)
            if (replaceEl) {
              replaceEl.outerHTML = res.replaceHtml[key];
            }
          });
        }

        return res;
      })
      .catch(err => {
        console.error(err)

        if (err.errors && Array.isArray(err.errors) && err.errors.length) {
          err.errors.map(snackbarItem => EventManager.dispatchSnackbar(snackbarItem, 'error', 5000));
        } else if (
          err.message &&
          typeof err.message === 'string' &&
          err.message.length
        ) {
          EventManager.dispatchSnackbar(err.message, 'error', 5000);
        }

        throw err;
      });
  }
}
