import { Spinner } from '@wordpress/components';
import PropTypes from 'prop-types';

function CategoryList({ hasResolved, terms, columns, showDescription, descriptionCount, showCount }) {
  if (!hasResolved) {
    return <Spinner />;
  }

  if (terms && terms.length > 0) {
    const columnStyle = {
      gridTemplateColumns: `repeat(${columns}, 1fr)`,
      columnGap: '20px', // Adjust as needed
    };

    return (
      <div className='wp-block-anspress-question-answer-categories-ccon' style={columnStyle}>
        {terms.map((term) => (
          <div key={term.id}>
            <div className='wp-block-anspress-question-answer-categories-ctitle'>{term.name}</div>
            {showDescription && term.description && (
              <p className='wp-block-anspress-question-answer-categories-cdesc'>{term.description.substring(0, descriptionCount)}...</p>
            )}
            {showCount && <p>Questions: {term.count}</p>}
          </div>
        ))}
      </div>
    );
  }

  return <p>No question categories found.</p>;
}

CategoryList.propTypes = {
  hasResolved: PropTypes.bool.isRequired,
  terms: PropTypes.array,
  columns: PropTypes.number,
  showDescription: PropTypes.bool,
  descriptionCount: PropTypes.number,
  showCount: PropTypes.bool,
};

export default CategoryList;
