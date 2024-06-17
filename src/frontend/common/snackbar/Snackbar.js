import { EventManager } from '../EventManager';
import { doAction } from '@wordpress/hooks';

export class Snackbar extends EventManager {
  init() {
    super.init();

    // Add listener for snackbar events
    document.body.addEventListener('anspress-snackbar', this.showSnackbarMessage.bind(this));

    doAction('anspress.snackbar.init', this);
  }
  static createSnackbarContainer() {
    const container = document.createElement('div');
    container.setAttribute('data-anspressel', 'snackbar')
    container.classList.add('anspress-snackbar-container');
    document.body.appendChild(container);
    return container;
  }

  showSnackbarMessage(event, element) {
    const { message, type, duration } = event.detail;
    const messageElement = this.createSnackbarMessage(message, type);
    this.container.appendChild(messageElement);

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

    // Remove the message element after the animation is done
    messageElement.addEventListener('transitionend', () => {
      if (messageElement.parentElement) {
        messageElement.parentElement.removeChild(messageElement);
      }
    });
  }
}
