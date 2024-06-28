import { BaseCustomElement } from "../BaseCustomElement";

class AnsPressCommentList extends BaseCustomElement {
  connectedCallback() {
    this.addEventListeners();
  }

  addEventListeners() {
    document.addEventListener(`anspress-comments-${this.data.postId}-added`, this.addCommentsHandler.bind(this));
    document.addEventListener(`anspress-comments-${this.data.postId}-deleted`, this.deleteCommentHandler.bind(this));
  }

  disconnectedCallback() {
    document.removeEventListener(`anspress-comments-${this.data.postId}-added`, this.addCommentsHandler);
    document.removeEventListener(`anspress-comments-${this.data.postId}-deleted`, this.deleteCommentHandler);
  }

  addCommentsHandler(event) {
    this.addComments(event.detail.html);
  }

  deleteCommentHandler(event) {
    this.removeComment(event.detail.commentId);
  }

  updateComponent() {
    this.checkLoadMoreButton();
    this.updateTotalCount();
  }

  checkLoadMoreButton() {
    const { hasMore } = this.data;
    const loadMoreButton = this.querySelector('[data-anspressel="comments-load-more"]');
    if (loadMoreButton) {
      if (hasMore) {
        loadMoreButton.style.display = 'inline-block';
      } else {
        loadMoreButton.style.display = 'none';
      }
    }
  }

  updateTotalCount() {
    const totalCountElement = this.querySelector('[data-anspressel="comments-total-count"]');

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
    }
  }
}

customElements.define('anspress-comment-list', AnsPressCommentList);
