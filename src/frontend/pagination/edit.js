import { useBlockProps } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

const Edit = () => {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <nav aria-label="Pagination">
        <div className="wp-block-anspress-pagination-ul">
          <div className="wp-block-anspress-pagination-item">
            <Button className="wp-block-anspress-pagination-link" href="#">Previous</Button>
          </div>
          <div className="wp-block-anspress-pagination-item">
            <Button className="wp-block-anspress-pagination-link" href="#">1</Button>
          </div>
          <div className="wp-block-anspress-pagination-item">
            <Button className="wp-block-anspress-pagination-link" href="#">2</Button>
          </div>
          <div className="wp-block-anspress-pagination-item">
            <Button className="wp-block-anspress-pagination-link" href="#">3</Button>
          </div>
          <div className="wp-block-anspress-pagination-item">
            <Button className="wp-block-anspress-pagination-link" href="#">Next</Button>
          </div>
        </div>
      </nav>
    </div>
  );
};

export default Edit;
