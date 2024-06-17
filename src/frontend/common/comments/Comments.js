import { EventManager } from '../EventManager';

export class Comments extends EventManager {
  eventMappings() {
    return [
      { selector: 'form.anspress-comments-form', eventType: 'submit', handler: this.submitForm, cancel: true },
    ];
  }
  init() {
    // Validate post ID.
    if (!this.data?.postId) {
      console.error('Post ID not found.');
      return;
    }

    this.form = this.createForm();

    this.commentsCountNode = this.el('.anspress-comments-count');
    this.replyButton = this.el('.anspress-comments-add-comment-button');

    super.init();

    if (!this.data?.canComment) {
      this.elements['comments-toggle-form'].style.display = 'none';
    }

    this.updateLoadMoreButton();
  }

  updateElements() {
    return {
      'comments-total-count': 'totalComments'
    };
  }

  createForm() {
    const form = document.createElement('form');
    form.setAttribute('class', 'anspress-comments-form');
    form.innerHTML = `
      <textarea name="anspress-comment-content" placeholder="Write your comment..."></textarea>
      <div class="anspress-comments-form-buttons">
        <button data-anspressel @click.prevent="toggleCommentForm" class="anspress-comments-form-cancel" type="button">Cancel</button>
        <button class="anspress-comments-form-submit" type="submit">Submit</button>
      </div>
    `;
    form.style.display = 'none';
    this.container.appendChild(form);
    return form;
  }

  toggleCommentForm() {
    if (this.form.style.display === 'none') {
      this.replyButton.style.display = 'none';
      this.form.style.display = 'block';
    } else {
      this.replyButton.style.display = 'inline-block';
      this.form.style.display = 'none';
    }
  }

  async submitForm(e, form) {
    e.preventDefault(); // Prevent default form submission behavior
    const comment = form.querySelector('textarea').value;

    // Remove all validation error messages.
    form.querySelectorAll('.anspress-validation-error').forEach(errorElement => {
      errorElement.remove();
    });

    await this.fetch({
      path: `/anspress/v1/post/${this.data.postId}/comments`,
      method: 'POST',
      data: { comment }
    }).then(response => {

      if (response) {
        this.form.style.display = 'none';
        this.form.reset();

        // Create a temporary container to extract the newly added HTML element
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = response.html;
        const newComment = tempContainer.firstElementChild;

        // Insert the new comment into the DOM
        const commentsContainer = this.el('.anspress-comments-items');
        commentsContainer.insertAdjacentElement('afterend', newComment);

        // Scroll to the new comment
        newComment.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Apply fade-in animation using CSS class
        newComment.classList.add('fade-in');

        this.replyButton.style.display = 'inline-block';
      }
    }).catch(error => {
      console.error('An error occurred while submitting the comment:', error);

      if (!error?.errors?.comment) {
        return;
      }

      // Add validation error message to the form element by name anspress-comment-content.
      const errorElement = document.createElement('div');
      errorElement.setAttribute('class', 'anspress-comment-error anspress-validation-error');
      errorElement.textContent = error.errors.comment;
      form.querySelector('[name=anspress-comment-content]').insertAdjacentElement('afterend', errorElement);
    });
  }

  async loadMoreComments() {
    try {
      const response = await this.fetch({
        path: `/anspress/v1/post/${this.data.postId}/comments?offset=${(this.data.offset + this.data.showing)}`,
        method: 'GET'
      });

      if (response.html) {
        this.elements['comments-items'].insertAdjacentHTML('beforeend', response.html);
      } else {
        this.elements['comments-load-more'].style.display = 'none';
      }

      if (this.data.hasMore) {
        this.elements['comments-load-more'].style.display = 'inline-block';
      } else {
        this.elements['comments-load-more'].style.display = 'none';
      }
    } catch (error) {
      console.error('An error occurred while loading more comments:', error);
    }
  }

  async deleteComment(e, element) {
    const commentElement = element.closest('.anspress-comments-item');
    const commentId = element.dataset.commentId;


    const response = await this.fetch({
      path: `/anspress/v1/post/${this.data.postId}/comments/${commentId}`,
      method: 'DELETE'
    });

    if (response) {
      commentElement.remove();
    } else {
      alert('Failed to delete comment');
    }

  }

  updateLoadMoreButton() {
    if (!this.data.hasMore) {
      this.elements['comments-load-more'].style.display = 'none';
    } else {
      this.elements['comments-load-more'].style.display = 'inline-block';
    }
  }

  updateCommentsCount() {
    if (this.elements['comments-total-count']) {
      this.elements['comments-total-count'].textContent = this.data.totalComments;
    }

    if (this.elements['comments-showing-count']) {
      this.elements['comments-showing-count'].textContent = this.data.showing;
    }
  }

  editComment(e, element) {
    console.error('Edit comment not implemented yet');
  }
}

