import { clearFormErrors, handleFormErrors, loadForm, removeForm } from "../common/AnsPressCommon";
import { BaseCustomElement } from "../common/BaseCustomElement";

class AnsPressAnswerForm extends BaseCustomElement {
  addEventListeners() {
    this.querySelector('[data-anspressel="load-form"]')?.addEventListener('click', this.loadAnswerForm.bind(this));

    this.querySelector('form')?.addEventListener('submit', this.handleSubmit.bind(this));

    this.querySelector('[data-anspressel="cancel-button"]')?.addEventListener('click', this.handleCancel.bind(this));

  }

  loadAnswerForm() {
    loadForm(this.data);
  }

  async handleSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    clearFormErrors(this.querySelector('form'));

    try {
      await this.fetch({
        path: this.data.form_action,
        method: 'POST',
        body: formData,
      });

      this.removeAnswerForm();
    } catch (error) {
      handleFormErrors(error?.errors, this.querySelector('form'));
      console.error('An error occurred:', error);
    }
  }

  async removeAnswerForm() {
    removeForm(this.data);
  }
}

customElements.define('anspress-answer-form', AnsPressAnswerForm);
