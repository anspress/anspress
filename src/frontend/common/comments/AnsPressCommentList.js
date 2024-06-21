import { BaseCustomElement } from "../BaseCustomElement";

class AnsPressCommentList extends BaseCustomElement {
  connectedCallback() {
    this.addEventListeners();
  }

  addEventListeners() {
    this.addEventListener('comment-deleted', (event) => {
      console.log('comment-deleted event:', event.detail);
      this.removeComment(event.detail.comment_id);
    });

    this.addEventListener('comment-added', (event) => {
      this.addComment(event.detail.comment);
    });

    this.querySelector('[data-anspressel="comments-load-more"]')?.addEventListener('click', this.loadMoreComments.bind(this));
  }

  disconnectedCallback() {
    this.removeEventListener('comment-deleted');
    this.removeEventListener('comment-added');
    this.querySelector('[data-anspressel="comments-load-more"]')?.removeEventListener('click', this.loadMoreComments.bind(this));
  }

  updateComponent() {
    console.log('Triggered updateComponent method')
    this.checkLoadMoreButton();
    this.updateTotalCount();
  }

  async loadMoreComments(e) {
    e.preventDefault();
    try {
      const response = await this.fetch({
        path: `/anspress/v1/post/${this.data.postId}/comments?offset=${(this.data.offset + this.data.showing)}`,
        method: 'GET'
      });
      console.log(response);
      if (response.html) {
        this.addComments(response.html);
      }
    } catch (error) {
      console.error('An error occurred while loading more comments:', error);
    }
  }

  checkLoadMoreButton() {
    const { hasMore } = this.data;
    const loadMoreButton = this.querySelector('[data-anspressel="comments-load-more"]');
    if (loadMoreButton) {
      if (hasMore) {
        loadMoreButton.style.display = 'block';
      } else {
        loadMoreButton.style.display = 'none';
      }
    }
  }

  updateTotalCount() {
    const totalCountElement = this.querySelector('[data-anspressel="comments-total-count"]');
    console.log(totalCountElement)
    if (totalCountElement) {
      totalCountElement.textContent = this.data.totalComments;
    }
  }

  addComments(html) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const comments = tempDiv.querySelectorAll('anspress-comment-item');
    comments.forEach(comment => {
      this.querySelector('[data-anspressel="comments-items"]').appendChild(comment);
    });
  }

  removeComment(commentId) {
    const commentElement = this.querySelector(`[data-id="${commentId}"]`);
    if (commentElement) {
      commentElement.remove();
      this.setDataValue('totalComments', this.data.totalComments - 1);
    }
  }
}

customElements.define('anspress-comment-list', AnsPressCommentList);
