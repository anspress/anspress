import { BaseCustomElement } from "../../common/BaseCustomElement";
import { addQueryArgs } from '@wordpress/url';

class AnsPressAnswerList extends BaseCustomElement {
    connectedCallback() {
        this.addEventListeners();
    }

    addEventListeners() {
        document.addEventListener(`anspress:answer:added:${this.data.question_id}`, (event) => {
            this.addAnswers(event.detail.html);
            // this.addComments(event.detail.html);
        });

        document.addEventListener(`anspress:answer:deleted:${this.data.question_id}`, this.removeAnswer.bind(this));
    }

    disconnectedCallback() {
        document.removeEventListener(`anspress:answer:added:${this.data.question_id}`);
        document.removeEventListener(`anspress:answer:deleted:${this.data.question_id}`);
    }

    updateComponent() {
        if (this.data.current_page >= this.data.total_pages) {
            const loadMoreButton = this.querySelector('[data-anspressel="load-more-answers"]');

            if (loadMoreButton) {
                loadMoreButton.style.display = 'none';
            }
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

    removeAnswer(event) {
        const answerId = event.detail.answer_id;
        const answer = document.querySelector(`[data-anspress-id="answer:${answerId}"]`);
        answer.remove();
    }
}

customElements.define('anspress-answer-list', AnsPressAnswerList);
