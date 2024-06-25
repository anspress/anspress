import { loadForm } from "../AnsPressCommon";
import { BaseCustomElement } from "../BaseCustomElement";

export class CommentItem extends BaseCustomElement {

  addEventListeners() {
    this.querySelector('[data-anspressel="edit-button"]').addEventListener('click', this.editComment.bind(this));
    this.querySelector('[data-anspressel="delete-button"]').addEventListener('click', this.deleteComment.bind(this));
  }

  disconnectedCallback() {
    this.querySelector('[data-anspressel="edit-button"]').removeEventListener('click', this.editComment.bind(this));
    this.querySelector('[data-anspressel="delete-button"]').removeEventListener('click', this.deleteComment.bind(this));
  }

  editComment(e) {
    e.preventDefault();
    console.log(this.getElData(e.target, 'anspress'))
    // trigger edit form.
    loadForm(this.getElData(e.target, 'anspress'));
  }

  async deleteComment(e) {
    e.preventDefault();

    const data = this.getElData(e.target, 'anspress');

    try {
      await this.fetch({
        path: data.path,
        method: 'DELETE'
      });
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }
}

customElements.define('anspress-comment-item', CommentItem);
