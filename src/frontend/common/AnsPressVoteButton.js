import { BaseCustomElement } from "./BaseCustomElement";

export class VoteButton extends BaseCustomElement {

  updateComponent() {
    const voteData = this.data;

    const upVoteButton = this.querySelector('[data-anspressel="vote-up"]');
    const downVoteButton = this.querySelector('[data-anspressel="vote-down"]');
    const voteCountSpan = this.querySelector('[data-anspressel="votes-net-count"]');

    if (upVoteButton) {
      if (voteData.currentUserVoted === 'voteup') {
        upVoteButton.classList.add('highlight');
        upVoteButton.disabled = false; // Ensure it's enabled to allow undo
      } else {
        upVoteButton.classList.remove('highlight');
        upVoteButton.disabled = voteData.currentUserVoted === 'votedown'; // Disable if downvoted
      }
    }

    if (downVoteButton) {
      if (voteData.currentUserVoted === 'votedown') {
        downVoteButton.classList.add('highlight');
        downVoteButton.disabled = false; // Ensure it's enabled to allow undo
      } else {
        downVoteButton.classList.remove('highlight');
        downVoteButton.disabled = voteData.currentUserVoted === 'voteup'; // Disable if upvoted
      }
    }

    if (voteCountSpan) {
      voteCountSpan.textContent = voteData.votesNet;
    }
  }

  addEventListeners() {
    this.querySelector('[data-anspressel="vote-up"]').addEventListener('click', this.voteUp.bind(this));
    this.querySelector('[data-anspressel="vote-down"]').addEventListener('click', this.voteDown.bind(this));
  }

  async send(action) {
    try {
      const path = !this.data.currentUserVoted
        ? `/anspress/v1/post/${this.data.postId}/actions/vote/${action}`
        : `/anspress/v1/post/${this.data.postId}/actions/undo-vote`;

      const response = await this.fetch({
        path,
        method: 'POST'
      });
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }

  async voteUp(event) {
    event.preventDefault();
    const voteData = this.data;

    await this.send('voteup').then(() => {
      if (voteData.currentUserVoted === 'voteup') {
        voteData.votesNet--;
        voteData.currentUserVoted = '';
        this.dispatchEvent(new CustomEvent('vote-undo', { detail: { vote: 'up' } }));
      } else {
        if (voteData.currentUserVoted === 'votedown') {
          voteData.votesNet++;
        }
        voteData.votesNet++;
        voteData.currentUserVoted = 'voteup';
        this.dispatchEvent(new CustomEvent('vote-up', { detail: { vote: 'up' } }));
      }
      this.data = voteData; // Use setter to update attribute and cache
    });
  }

  async voteDown(event) {
    event.preventDefault();
    const voteData = this.data;

    await this.send('votedown').then(() => {
      if (voteData.currentUserVoted === 'votedown') {
        voteData.votesNet++;
        voteData.currentUserVoted = '';
        this.dispatchEvent(new CustomEvent('vote-undo', { detail: { vote: 'down' } }));
      } else {
        if (voteData.currentUserVoted === 'voteup') {
          voteData.votesNet--;
        }
        voteData.votesNet--;
        voteData.currentUserVoted = 'votedown';
        this.dispatchEvent(new CustomEvent('vote-down', { detail: { vote: 'down' } }));
      }
      this.data = voteData; // Use setter to update attribute and cache
    })
  }
}

customElements.define('anspress-vote-button', VoteButton);
