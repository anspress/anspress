import { Spinner } from '@wordpress/components';
import PropTypes from 'prop-types';

function CategoryList({ hasResolved, terms, columns, showDescription, descriptionCount, showCount, showImage }) {
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
        {terms.map((term) => {

          const bgColorStyle = {
            backgroundColor: term.meta?.ap_category?.color || '#eee',
            backgroundImage: term.meta?.ap_category?.image?.url ? `url(${term.meta.ap_category.image.url})` : 'none',
          }

          return <div key={term.id} className='wp-block-anspress-question-answer-categories-citem'>
            {showImage && <div className='wp-block-anspress-question-answer-categories-cimage' style={bgColorStyle}>
            </div>}

            <div className='wp-block-anspress-question-answer-categories-chead'>
              <div className='wp-block-anspress-question-answer-categories-cicon'></div>
              <a class='wp-block-anspress-question-answer-categories-ctitle' href={term.link}>{term.name}</a>
              {showCount && <div>Questions: {term.count}</div>}
            </div>
            {showDescription && term.description && (
              <p className='wp-block-anspress-question-answer-categories-cdesc'>{term.description.substring(0, descriptionCount)}...</p>
            )}

          </div>
        })
        }
      </div >
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
  showImage: PropTypes.bool
};

export default CategoryList;
