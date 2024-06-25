import { BaseCustomElement } from "../BaseCustomElement";

class AnsPressCommentList extends BaseCustomElement {
  connectedCallback() {
    this.addEventListeners();
  }

  addEventListeners() {
    this.querySelector('[data-anspressel="comments-load-more"]')?.addEventListener('click', this.loadMoreComments.bind(this));

    document.addEventListener(`anspress-comments-${this.data.postId}-added`, (event) => {
      this.addComments(event.detail.html);
    });

    document.addEventListener(`anspress-comments-${this.data.postId}-deleted`, (event) => {
      this.removeComment(event.detail.commentId);
    })
  }

  disconnectedCallback() {
    this.querySelector('[data-anspressel="comments-load-more"]')?.removeEventListener('click', this.loadMoreComments.bind(this));

    document.removeEventListener(`anspress-comments-${this.data.postId}-added`);
    document.removeEventListener(`anspress-comments-${this.data.postId}-deleted`);
  }

  updateComponent() {
    this.checkLoadMoreButton();
    this.updateTotalCount();
  }

  async loadMoreComments(e) {
    e.preventDefault();
    try {
      await this.fetch({
        path: `/anspress/v1/post/${this.data.postId}/comments?offset=${(this.data.offset + this.data.showing)}`,
        method: 'GET'
      });
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
    console.log(comments, tempDiv)
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
