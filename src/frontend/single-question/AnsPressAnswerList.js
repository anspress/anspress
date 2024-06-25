import { BaseCustomElement } from "../common/BaseCustomElement";
import { addQueryArgs } from '@wordpress/url';

class AnsPressAnswerList extends BaseCustomElement {
  connectedCallback() {
    this.addEventListeners();
  }

  addEventListeners() {
    console.log(this)
    document.addEventListener(`anspress:answer:added:${this.data.question_id}`, (event) => {
      this.addAnswers(event.detail.html);
      // this.addComments(event.detail.html);
    });

    document.addEventListener(`anspress:answer:deleted:${this.data.question_id}`, (event) => {
      // this.removeComment(event.detail.commentId);
    })

    this.querySelector('[data-anspressel="load-more-answers"]')?.addEventListener('click', this.loadMore.bind(this));
  }

  disconnectedCallback() {
    document.removeEventListener(`anspress:answer:added:${this.data.question_id}`);
    document.removeEventListener(`anspress:answer:deleted:${this.data.question_id}`);
    this.querySelector('[data-anspressel="load-more-answers"]')?.removeEventListener('click', this.loadMore.bind(this));
  }

  updateComponent() {
    if (this.data.current_page >= this.data.total_pages) {
      this.querySelector('[data-anspressel="load-more-answers"]').style.display = 'none';
    }

    // update answers-count-*
    document.querySelectorAll(`[data-anspress-id="answers-count-${this.data.question_id}"]`).forEach((element) => {
      element.textContent = this.data.remaining_items;
    });
  }

  addAnswers(html) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const answers = tempDiv.querySelectorAll('anspress-item');

    answers?.forEach(answer => {
      document.querySelector(`[data-anspress-id="answers-${this.data.question_id}"] [data-anspressel="answers-items"]`).appendChild(answer);
    });
  }

  async loadMore() {
    const button = this.querySelector('[data-anspressel="load-form"]');
    button.disabled = true;

    const response = await this.fetch({
      path: addQueryArgs(this.data.load_more_path, { page: parseInt(this.data.current_page) + 1 }),
      method: 'GET',
    });
  }
}

customElements.define('anspress-answer-list', AnsPressAnswerList);
