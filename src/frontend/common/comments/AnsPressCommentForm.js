import { clearFormErrors, handleFormErrors, loadForm, removeForm } from "../AnsPressCommon";
import { BaseCustomElement } from "../BaseCustomElement";


class AnsPressCommentForm extends BaseCustomElement {
  addEventListeners() {
    this.querySelector('[data-anspressel="load-form"]')?.addEventListener('click', this.loadCommentForm.bind(this));

    this.querySelector('form')?.addEventListener('submit', this.handleSubmit.bind(this));

    this.querySelector('[data-anspress-id="comment:button:cancel"]')?.addEventListener('click', this.handleCancel.bind(this));
  }

  disconnectedCallback() {
    this.querySelector('[data-anspressel="load-form"]')?.removeEventListener('click', this.loadCommentForm.bind(this));

    this.querySelector('form')?.removeEventListener('submit', this.handleSubmit.bind(this));

    this.querySelector('[data-anspress-id="comment:button:cancel"]')?.removeEventListener('click', this.handleCancel.bind(this));
  }

  loadCommentForm() {
    loadForm(this.data,);
  }

  async handleSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    clearFormErrors(this.querySelector('form'));

    try {
      const response = await this.fetch({
        path: this.data.form_action,
        method: 'POST',
        body: formData,
      });

      this.removeCommentForm();
    } catch (error) {
      handleFormErrors(error?.errors, this.querySelector('form'));
      console.error('An error occurred:', error);
    }
  }

  handleCancel() {
    this.removeCommentForm(this.data);
  }

  async removeCommentForm() {
    // Destroy self
    this.remove();
  }
}

customElements.define('anspress-comment-form', AnsPressCommentForm);
