import { BaseCustomElement } from "../BaseCustomElement";
import { addQueryArgs } from '@wordpress/url';

class AnsPressCommentForm extends BaseCustomElement {
  addEventListeners() {
    this.querySelector('[data-anspressel="load-form"]').addEventListener('click', this.loadForm.bind(this));
    this.querySelector('form')?.addEventListener('submit', this.handleSubmit.bind(this));
    const cancelButton = this.querySelector('.cancel-button');
    if (cancelButton) {
      cancelButton.addEventListener('click', this.handleCancel.bind(this));
    }
  }

  loadForm() {
    this.fetch({
      path: addQueryArgs(this.data.load_form_path, { form_loaded: true }),
      method: 'GET',
    })
      .then(res => {
        if (res?.load_easymde) {
          tinymce.init({
            selector: '#' + res?.load_easymde,
            toolbar: 'bold italic | underline strikethrough | bullist numlist | link unlink blockquote hr| image removeformat',
            menubar: false,
            statusbar: false,
            plugins: 'lists link hr wpautoresize image',
            min_height: 200,
            wp_autoresize_on: true,
            images_file_types: 'jpg,svg,webp',
            file_picker_types: 'file image media',
            automatic_uploads: true,
            images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            }),
            setup: function (editor) {
              editor.on('change', function () {
                editor.save();
              });
            }
          });
        }
      });
  }

  async handleSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const content = formData.get('content');

    let response;
    try {
      if (this.data && this.data.id) {
        response = await this.fetch(`/anspress/v1/comments/${this.data.id}`, {
          method: 'PUT',
          body: JSON.stringify({ content }),
          headers: { 'Content-Type': 'application/json' }
        });
      } else {
        response = await this.fetch('/anspress/v1/comments', {
          method: 'POST',
          body: JSON.stringify({ content }),
          headers: { 'Content-Type': 'application/json' }
        });
      }

      const updatedComment = await response.json();
      this.dispatchEvent(new CustomEvent('comment-added', { detail: { comment: updatedComment }, bubbles: true }));
      this.removeForm();
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }

  handleCancel() {
    this.removeForm();
  }

  removeForm() {
    if (this.data && this.data.id) {
      const commentItem = document.createElement('comment-item');
      commentItem.setAttribute('data-anspress', JSON.stringify(this.data));
      this.replaceWith(commentItem);
    } else {
      this.remove();
    }
  }
}

customElements.define('anspress-comment-form', AnsPressCommentForm);
