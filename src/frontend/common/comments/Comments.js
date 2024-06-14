import apiFetch from '@wordpress/api-fetch';
import { EventManager } from '../EventManager';

export class Comments extends EventManager {
  eventMappings() {
    return [
      { selector: '.anspress-comments-add-comment-button', eventType: 'click', handler: this.toggleForm, cancel: true },
      { selector: '.anspress-comments-loadmore-button', eventType: 'click', handler: this.loadMoreComments, cancel: true },
      { selector: '.anspress-comments-form-cancel', eventType: 'click', handler: this.toggleForm, cancel: true },
      { selector: 'form.anspress-comments-form', eventType: 'submit', handler: this.submitForm, cancel: true },
      { selector: '.anspress-comments-delete', eventType: 'click', handler: this.deleteComment, cancel: true }
    ];
  }
  init() {
    // Validate post ID.
    if (!this.data?.postId) {
      console.error('Post ID not found.');
      return;
    }

    this.form = this.createForm();
    this.loadMoreButton = this.el('.anspress-comments-loadmore-button');
    this.commentsCountNode = this.el('.anspress-comments-count');

    this.replyButtons = this.el('.anspress-comments-add-comment-button');

    super.init();
  }

  createForm() {
    const form = document.createElement('form');
    form.setAttribute('class', 'anspress-comments-form');
    form.innerHTML = `
      <textarea name="comment" placeholder="Write your comment..."></textarea>
      <div class="anspress-comments-form-buttons">
        <button class="anspress-comments-form-cancel" type="button">Cancel</button>
        <button class="anspress-comments-form-submit" type="submit">Submit</button>
      </div>
    `;
    form.style.display = 'none';
    this.container.appendChild(form);
    return form;
  }

  toggleForm(button) {
    if (this.form.style.display === 'none') {
      this.form.style.display = 'block';
    } else {
      this.form.style.display = 'none';
    }
  }

  async submitForm(e, form) {
    const comment = form.querySelector('textarea').value;
    try {
      const response = await apiFetch({
        path: `/anspress/v1/post/${this.data.postId}/comments`,
        method: 'POST',
        data: { comment }
      });

      if (response) {
        this.form.style.display = 'none';
        this.form.reset();
        this.el('.anspress-comments-items').insertAdjacentHTML('beforebegin', response.html);
      } else {
        alert('Failed to submit comment');
      }
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }

  async loadMoreComments() {
    console.log(this)
    try {
      const response = await apiFetch({
        path: `/anspress/v1/post/${this.commentsData.postId}/comments?offset=${this.itemsShowing}&limit=5`,
        method: 'GET'
      });

      if (response.comments && response.comments.length) {
        response.comments.forEach(comment => this.appendComment(comment));
      } else {
        this.loadMoreButton.style.display = 'none';
      }
    } catch (error) {
      console.error('An error occurred while loading more comments:', error);
    }
  }

  async deleteComment(e, element) {
    const commentElement = element.closest('.anspress-comments-item');
    const commentId = element.dataset.commentId;

    try {
      const response = await apiFetch({
        path: `/anspress/v1/post/${this.data.postId}/comments/${commentId}`,
        method: 'DELETE'
      });

      if (response) {
        commentElement.remove();
      } else {
        alert('Failed to delete comment');
      }
    } catch (error) {
      console.error('An error occurred while deleting the comment:', error);
    }
  }

  updateCommentsCount() {
    if (this.commentsCountNode) {
      this.commentsCountNode.textContent = `Showing ${this.itemsShowing} of ${this.totalItems} items`;
    }
  }

  updateLoadMoreButton() {
    if (this.itemsShowing >= this.totalItems) {
      this.loadMoreButton.style.display = 'none';
    } else {
      this.loadMoreButton.style.display = 'block';
    }
  }
}

