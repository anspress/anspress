export class EventManager {
  constructor(containerSelector) {
    this.container = containerSelector;
    this.eventHandlers = [];
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
  }

  // Unbind all events
  unbindEvents() {
    this.eventHandlers.forEach(({ element, eventType, handler }) => {
      element.removeEventListener(eventType, handler);
    });
    this.eventHandlers = [];
  }

  // Rebind events when DOM changes
  setupMutationObserver() {
    const observer = new MutationObserver(() => {
      this.unbindEvents();
      this.bindEvents();
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

  get data() {
    // Cache the parsed data object
    if (this._data) return this._data;

    this._data = JSON.parse(this.container.dataset.anspress || '{}');

    return this._data;
  }
}
