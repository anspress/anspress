import { BaseCustomElement } from "../BaseCustomElement";

export class CommentItem extends BaseCustomElement {

  addEventListeners() {
    this.querySelector('[data-anspressel="edit-button"]').addEventListener('click', this.editComment.bind(this));
    this.querySelector('[data-anspressel="delete-button"]').addEventListener('click', this.deleteComment.bind(this));
  }

  editComment(e) {
    e.preventDefault();

    const commentForm = document.createElement('comment-form');
    commentForm.setAttribute('data-anspress', JSON.stringify(this.data));
    this.replaceWith(commentForm);
  }

  async deleteComment(e) {
    e.preventDefault();

    const data = this.getElData(e.target, 'anspress');

    try {
      await this.fetch({
        path: data.path,
        method: 'DELETE'
      });
      this.dispatchEvent(new CustomEvent('comment-deleted', { detail: data, bubbles: true }));
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }
}

customElements.define('anspress-comment-item', CommentItem);
