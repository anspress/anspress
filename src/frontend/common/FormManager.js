import { EventManager } from './EventManager';

export class FormManager extends EventManager {

  getFormFields(form) {
    return form.querySelectorAll('[data-anspress-field]');
  }

  appendError(field, errors) {
    let errorElement = field.parentElement.querySelector('.anspress-form-error');
    if (!errorElement) {
      errorElement = document.createElement('div');
      errorElement.classList.add('anspress-form-error');
      field.appendChild(errorElement);
    }
    errorElement.innerText = errors;
  }

  clearErrors(form) {
    form.querySelectorAll('.anspress-form-error').forEach(errorElement => {
      errorElement.remove();
    });
  }

  async submitForm(e, form) {
    this.clearErrors(form);

    // Search for the form fields and trigger easymed.toTextArea() to update the textarea value.
    const formFields = this.getFormFields(form);
    console.log(formFields)
    formFields?.forEach((field) => {
      console.log(field.querySelector('textarea')?.easymde)
      field.querySelector('textarea').value = field.querySelector('textarea')?.easymde?.value();
    });

    const formData = new FormData(form);

    const data = Object.fromEntries(formData.entries());

    try {
      const response = await this.fetch({
        path: this.data.form_action,
        method: 'POST',
        data: data
      });

      this.handleSuccess(response, form);
    } catch (error) {
      console.error('Form submission error:', error);
      this.handleErrors(error.errors, form);
    }
  }

  handleSuccess(response, form) {
    form.reset();
  }

  handleErrors(errors, form) {
    Object.keys(errors).forEach((field) => {
      const fieldErrors = errors[field] ?? [];
      const fieldEl = form.querySelector(`[data-anspress-field="${field}"]`);
      if (fieldEl) {
        this.appendError(fieldEl, fieldErrors);
      } else {
        console.error(`Field not found for error: ${field}`);
      }
    });
  }

  isFormLoaded() {
    return this.data?.formLoaded;
  }

  loadForm() {
    this.fetch({
      path: this.data.load_form_path,
      method: 'GET',
    })
      .then(res => {
        if (res?.load_easymde) {
          const easymde = new EasyMDE({
            element: document.getElementById(res?.load_easymde),
            toolbar: [
              'bold', 'italic', 'strikethrough', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'image', '|', 'preview', 'guide'
            ]
          })

          document.getElementById(res?.load_easymde).easymde = easymde;
        }
      });
  }
}
