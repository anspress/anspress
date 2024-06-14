import apiFetch from '@wordpress/api-fetch';

export class CommentForm {
  constructor(button, containerClass) {
    this.button = button;
    this.containerClass = containerClass;
    this.postId = button.dataset.postId;
    this.type = button.dataset.type; // Either 'reply' or 'add-comment'

    // Validate post ID.
    if (!this.postId) {
      console.error('Post ID not found.');
      return;
    }

    this.form = this.createForm();

    button.addEventListener('click', (e) => this.toggleForm(e));
  }

  createForm() {
    const form = document.createElement('form');
    form.setAttribute('class', 'anspress-comments-form')
    form.innerHTML = `
      <textarea name="comment" placeholder="Write your comment..."></textarea>
      <div class="anspress-comments-form-buttons">
        <button class="anspress-comments-form-cancel">Cancel</button>
        <button class="anspress-comments-form-submit" type="submit">Submit</button>
      </div>
    `;
    form.style.display = 'none';

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      this.submitForm(event);
    });

    // Cancel button event listener.
    form.querySelector('.anspress-comments-form-cancel').addEventListener('click', (e) => this.toggleForm(e));

    const container = this.button.closest(`.${this.containerClass}`);
    if (container) {
      container.appendChild(form);
    } else {
      console.warn(`Container with class "${this.containerClass}" not found.`);
      document.body.appendChild(form); // Fallback
    }
    return form;
  }

  toggleForm(e) {
    e.preventDefault();
    if (this.form.style.display === 'none') {
      this.form.style.display = 'block';
    } else {
      this.form.style.display = 'none';
    }
  }

  async submitForm(event) {
    const comment = event.target.querySelector('textarea').value;
    try {
      const response = await apiFetch({
        path: `/anspress/v1/post/${this.postId}/comments`,
        method: 'POST',
        data: { comment }
      });

      if (response) {
        alert('Comment submitted successfully');
        this.form.style.display = 'none';
      } else {
        alert('Failed to submit comment');
      }
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }
}
