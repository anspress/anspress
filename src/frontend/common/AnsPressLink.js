import { BaseCustomElement } from './BaseCustomElement';
import { addQueryArgs } from '@wordpress/url';

export class AnsPressLink extends BaseCustomElement {
  constructor() {
    super();
    this.handleClick = this.handleClick.bind(this);
  }

  disconnectedCallback() {
    this.removeEventListener('click', this.handleClick);
  }

  updateComponent() {
    // Implement necessary updates here
  }

  handleClick(event) {
    event.preventDefault(); // Prevent default link behavior
    const href = this.getAttribute('data-href');
    const method = this.getAttribute('data-method') || 'GET';

    // Add a loading animation or state
    this.classList.add('anspress-button-loading');

    this.classList.remove('anspress-button-error');
    console.log(method)

    // Create a div to cover the button content and show the loader
    const loader = document.createElement('div');
    loader.className = 'anspress-button-loading-spinner';
    this.style.position = 'relative'; // Ensure the button itself is relatively positioned
    this.appendChild(loader);

    const options = {
      path: method === 'GET' ? addQueryArgs(href, this.data) : href,
      method,
    };

    if (method === 'POST') {
      options.data = this.data;
    }

    this.fetch(options)
      .then(response => {
        // Remove the loading state and restore button content
        this.classList.remove('anspress-button-loading');
        loader.remove();

      })
      .catch(error => {
        console.error('Error fetching link:', error);
        // Handle the error, remove loading state, and restore button content
        this.classList.add('anspress-button-error')
        this.classList.remove('anspress-button-loading');
        loader.remove();
      });
  }

  addEventListeners() {
    this.addEventListener('click', this.handleClick);
  }
}

customElements.define('anspress-link', AnsPressLink);
