import { clearFormErrors, handleFormErrors, initTynimce, loadForm, removeForm, removeTinymce } from "../common/AnsPressCommon";
import { BaseCustomElement } from "../common/BaseCustomElement";

class AnsPressAnswerForm extends BaseCustomElement {
  connectedCallback() {
    super.connectedCallback();
    if (this.data.load_tinymce) {
      initTynimce(this.data.load_tinymce);
    }

    // Scroll to the form.
    this.scrollIntoView();
  }

  disconnectedCallback() {
    if (this.data.load_tinymce) {
      removeTinymce(this.data.load_tinymce);
    }
  }

  addEventListeners() {
    this.querySelector('[data-anspressel="load-form"]')?.addEventListener('click', this.loadAnswerForm.bind(this));

    this.querySelector('form')?.addEventListener('submit', this.handleSubmit.bind(this));

    this.querySelector('[data-anspressel="cancel-button"]')?.addEventListener('click', this.removeAnswerForm.bind(this));

  }

  loadAnswerForm() {
    console.log(this)
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

  async removeAnswerForm(e) {
    e?.preventDefault();
    removeForm(this.data);
  }
}

customElements.define('anspress-answer-form', AnsPressAnswerForm);
