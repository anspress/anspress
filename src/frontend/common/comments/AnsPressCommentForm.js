import { clearFormErrors, handleFormErrors, loadForm, removeForm } from "../AnsPressCommon";
import { BaseCustomElement } from "../BaseCustomElement";


class AnsPressCommentForm extends BaseCustomElement {
  addEventListeners() {
    this.querySelector('[data-anspressel="load-form"]')?.addEventListener('click', this.loadCommentForm.bind(this));

    this.querySelector('form')?.addEventListener('submit', this.handleSubmit.bind(this));

    this.querySelector('[data-anspressel="cancel-button"]')?.addEventListener('click', this.handleCancel.bind(this));

  }

  loadCommentForm() {
    loadForm(this.data.load_form_path,);
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
    removeForm(this.data.load_form_path);
  }

  async removeCommentForm() {
    removeForm(this.data.load_form_path);
  }
}

customElements.define('anspress-comment-form', AnsPressCommentForm);
