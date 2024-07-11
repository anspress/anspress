import { clearFormErrors, handleFormErrors, initTynimce, loadForm, removeTinymce } from "../../common/AnsPressCommon";
import { BaseCustomElement } from "../../common/BaseCustomElement";

class AnsPressQuestionForm extends BaseCustomElement {
  addEventListeners() {
    initTynimce(`question_content`);


    this.querySelector('form')?.addEventListener('submit', this.handleSubmit.bind(this));
  }

  removeEventListeners() {
    removeTinymce(`question_content`);

    this.querySelector('form')?.removeEventListener('submit', this.handleSubmit.bind(this));
  }

  loadAnswerForm() {
    loadForm(this.data, {
      templateId: this.getTemplateId(),
      blockName: this.getBlockName(),
    });
  }

  async handleSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    let data = Object.fromEntries(formData);

    clearFormErrors(this.querySelector('form'));

    // Append tags to form data
    const tags = this.querySelector('[data-anspress-id="question_tags"]')?.value;
    if (tags && tags.length > 0) {
      data.question_tags = tags.map((tag, index) => tag.key || tag);
    }

    // Append categories to form data
    const category = this.querySelector('[data-anspress-id="question_category"]')?.value;

    if (category && category.length > 0) {
      data.question_category = category.map((cat, index) => cat.key || cat);
    }

    try {
      await this.fetch({
        path: this.data.form_action,
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json',
        },
      });
    } catch (error) {
      handleFormErrors(error?.errors, this.querySelector('form'));
      console.error('An error occurred:', error);
    }
  }
}

customElements.define('anspress-question-form', AnsPressQuestionForm);
