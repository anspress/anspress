import { createRoot } from 'react-dom';
import { useState } from '@wordpress/element';
import icons from './icons';
import { Icon } from '@wordpress/components';

const Counter = ({ attributes }) => {
  const [counter, setCounter] = useState(attributes.initial);
  const increment = () => setCounter(counter + attributes.increment);
  const decrement = () => setCounter(counter - attributes.increment);
  return (
    <>
      <button onClick={decrement}>
        <Icon icon={icons.voteUp} />
      </button>
      <div>{counter}</div>
      <button onClick={increment}>
        <Icon icon={icons.voteDown} />
      </button>
    </>
  );
};

window.addEventListener(
  'load',
  function () {
    document
      .querySelectorAll(
        '.wp-block-anspress-vote-button .counter-contaner'
      )
      .forEach((blockDomElement) => {

        const attributes = JSON.parse(
          blockDomElement.dataset.gutenbergAttributes
        );

        const root = createRoot(blockDomElement);
        root.render(<Counter attributes={attributes} />);
      });
  },
  false
);
