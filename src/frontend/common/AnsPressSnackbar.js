import { BaseCustomElement } from "./BaseCustomElement";

export class AnsPressSnackbar extends BaseCustomElement {

  addEventListeners() {
    console.log('Initializing snackbar event listeners...')
    document.body.addEventListener('anspress-snackbar', this.showSnackbarMessage.bind(this));
  }

  disconnectedCallback() {
    document.body.removeEventListener('anspress-snackbar', this.showSnackbarMessage.bind(this));
  }

  showSnackbarMessage(event, element) {
    const { message, type, duration } = event.detail;
    const messageElement = this.createSnackbarMessage(message, type);
    this.appendChild(messageElement);

    // Show the message with animation
    setTimeout(() => {
      messageElement.classList.add('show');
    }, 10);

    // Hide the message after specified duration
    setTimeout(() => {
      this.hideSnackbarMessage(messageElement);
    }, duration || 3000); // Default duration is 3000ms
  }

  createSnackbarMessage(message, type) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('anspress-snackbar-message', `anspress-snackbar-${type}`);
    messageElement.innerText = message;
    return messageElement;
  }

  hideSnackbarMessage(messageElement) {
    messageElement.classList.remove('show');
    messageElement.classList.add('hide');

    // Remove after 2 seconds.
    setTimeout(() => {
      messageElement.remove();
    }, 2000);
  }
}

customElements.define('anspress-snackbar', AnsPressSnackbar);
